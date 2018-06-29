<?php

/**
* description: ATK Model
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;

class Model_Communication extends \xepan\base\Model_Table{
	public $table="communication";
	// public $acl=false;
	public $acl_type = 'Communication';
	public $actions = ['All'=>['view','edit','delete']];
	function init(){
		parent::init();

		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						'calling_status'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);
		$config_m->tryLoadAny();


		
		$this->hasOne('xepan\base\Contact','from_id');
		$this->hasOne('xepan\base\Contact','to_id');
		$this->hasOne('xepan\base\Contact','related_contact_id');
		$this->hasOne('xepan\base\Document','related_document_id');
		$this->hasOne('xepan\base\Contact','created_by_id')->defaultValue(@$this->app->employee->id);
		
		$this->addField('uid');
		$this->addField('uuid');
		$this->addField('reply_to');
		
		$this->addField('from_raw')->defaultValue([]);
		$this->addField('to_raw')->defaultValue([]);
		$this->addField('flags');
		$this->addField('cc_raw')->defaultValue([]);
		$this->addField('bcc_raw')->defaultValue([]);

		$this->addField('title');
		$this->addField('description')->type('text');

		$this->addField('tags');

		$this->addField('sub_type')->enum(explode(',', $config_m['sub_type']))->caption($config_m['sub_type_1_label_name']?:"Product/ Service/ Related To");
		$this->addField('calling_status')->enum(explode(',', $config_m['calling_status']))->caption($config_m['sub_type_2_label_name']?:"Communication Result");
		$this->addField('sub_type_3')->enum(explode(',', $config_m['sub_type_3']))->caption($config_m['sub_type_3_label_name']?:"Communication Remark");
		
		$this->addField('direction')->enum(['In','Out']);
		$this->addField('communication_type');

		$this->addField('related_id'); // Can be used anywhere as per requirement
		
		$this->addField('sent_on')->type('date'); // Can be used anywhere as per requirement
		
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('status');
		$this->addField('mailbox');
		$this->addField('communication_channel_id'); // Cna be used for email_settings or sms settings etc.
		$this->addField('score')->type('int')->defaultValue(0); 
		$this->addField('is_starred')->type('boolean')->defaultValue(false);

		$this->addField('detailed')->type('boolean')->defaultValue(false);
		$this->addField('extra_info')->defaultValue(['seen_by'=>[]]);
		
		$this->addField('type');
 	
		$this->hasMany('xepan\communication\Communication_Attachment','communication_id',null,'EmailAttachments');
		$this->hasMany('xepan\crm\Ticket_Comments','communication_id',null,'Comments');
		$this->hasMany('xepan\crm\SupportTicket','communication_id',null,'SupportTicket');
		$this->hasMany('xepan\base\Contact_CommunicationReadEmail','communication_id',null,'UnreadEmails');
		$this->hasMany('xepan\communication\Model_CommunicationRelatedEmployee','communication_id',null,'CommunicationRelatedEmployee');

		$this->addExpression('image')->set($this->refSQL('from_id')->fieldQuery('image'));
		$this->addExpression('attachment_count')->set($this->refSQL('EmailAttachments')->addCondition('type','attach')->count());

		$this->addExpression('is_read')->set(function($m,$q){
			return $unread_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail')
								->addCondition('is_read',true)
								->addCondition('communication_id',$q->getField('id'))
								->addCondition('contact_id',$this->app->employee->id)
								->count();

		})->type('boolean');

		$this->addExpression('to_contact_str')->set($this->refSQL('to_id')->fieldQuery('contacts_comma_seperated'));
		$this->addHook('afterInsert',[$this,'throwHookNotification']);
		$this->addHook('beforeDelete',[$this,'deleteAttachments']);
		
		$this->addhook('beforeSave',function($m){
			$m['extra_info'] = json_encode($m['extra_info']);
			if($m['to_id'] && ($m['direction'] == 'Out' || $m['communication_type'] == "Comment")) $this->ref('to_id')->set('last_communication_before_days','0')->save();
		});
		
		$this->addhook('afterLoad',function($m){
			$m['extra_info'] = json_decode($m['extra_info'],true);
		});

		$this->is([
				'direction|required'
			]);
		
	}

	function throwHookNotification($model,$new_id){		
		$communication = $this->add('xepan\communication\Model_Communication');
		$communication->load($new_id);

		$this->app->hook('communication_created',[$communication]);
	}

	function addAttachment($attach_id,$type=null){
		if(!$attach_id) return;
		$attach = $this->add('xepan\communication\Model_Communication_Attachment');
		$attach['file_id'] = $attach_id;
		$attach['communication_id'] = $this->id;
		$attach['type'] = $type;	
		$attach->save();

		return $attach;
	}

	// $required = employee_id, employee_name,id
	function getCommunicationRelatedEmployee($required = "employee_id"){
		$comm = $this->add('xepan\communication\Model_CommunicationRelatedEmployee');
		$comm->addCondition('communication_id',$this->id);

		if($required == "employee_name")
			return array_column($comm->getRows(), 'employee');

		return array_column($comm->getRows(), 'employee_id');
	}

	function getAttachments($urls=true){
		$attach_arry = array();
		if($this->loaded()){
			$attach_m = $this->add('xepan\communication\Model_Communication_Attachment');
			$attach_m->addCondition('communication_id',$this->id);
			foreach ($attach_m as $attach) {
				$attach_arry[] = $urls?$attach['file']:$attach['id'];
			}

		}
		
		return $attach_arry;
	}

	function deleteAttachments(){
		$attach = $this->add('xepan\communication\Model_Communication_Attachment');
		$attach->addCondition('communication_id',$this->id);
		foreach ($attach as  $m) {
			$m->delete();
		}

		$unread_count_entry = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
		$unread_count_entry->addCondition('communication_id',$this->id);
		foreach ($unread_count_entry as $email) {
			$email->delete();
		}
	}

	function activityReport($app,$report_view,$emp,$start_date,$end_date){				
		$employee = $this->add('xepan\hr\Model_Employee')->load($emp);
							  					  
		$message = $this->add('xepan\communication\Model_Communication_AbstractMessage');
		$message->addCondition('created_at','>=',$start_date);
		$message->addCondition('created_at','<=',$this->app->nextDate($end_date));
		$message->addCondition('created_by_id',$emp);
		$message_count = $message->count()->getOne();
		
		$result_array[] = [
 					'assign_to'=>$employee['name'],
 					'from_date'=>$start_date,
 					'to_date'=>$end_date,
 					'type'=> 'Message',
 					'count'=>$message_count,
 				];	

		$call = $this->add('xepan\communication\Model_Communication_Call');
		$call->addCondition('created_at','>=',$start_date);
		$call->addCondition('created_at','<=',$this->app->nextDate($end_date));
		$call->addCondition('created_by_id',$emp);
		$call_count = $call->count()->getOne();
		
		$result_array[] = [
 					'assign_to'=>$employee['name'],
 					'from_date'=>$start_date,
 					'to_date'=>$end_date,
 					'type'=> 'Call',
 					'count'=>$call_count,
 				];	

		$comment = $this->add('xepan\communication\Model_Communication_Comment');
		$comment->addCondition('created_at','>=',$start_date);
		$comment->addCondition('created_at','<=',$this->app->nextDate($end_date));
		$comment->addCondition('created_by_id',$emp);
		$comment_count = $comment->count()->getOne();
		
		$result_array[] = [
 					'assign_to'=>$employee['name'],
 					'from_date'=>$start_date,
 					'to_date'=>$end_date,
 					'type'=> 'Comment',
 					'count'=>$comment_count,
 				];	

		$email = $this->add('xepan\communication\Model_Communication_Email');
		$email->addCondition('created_at','>=',$start_date);
		$email->addCondition('created_at','<=',$this->app->nextDate($end_date));
		$email->addCondition('created_by_id',$emp);
		$email_count = $email->count()->getOne();
		
		$result_array[] = [
 					'assign_to'=>$employee['name'],
 					'from_date'=>$start_date,
 					'to_date'=>$end_date,
 					'type'=> 'Email',
 					'count'=>$email_count,
 				];	


		$sms = $this->add('xepan\communication\Model_Communication_SMS');
		$sms->addCondition('created_at','>=',$start_date);
		$sms->addCondition('created_at','<=',$this->app->nextDate($end_date));
		$sms->addCondition('created_by_id',$emp);
		$sms_count = $sms->count()->getOne();
		
		$result_array[] = [
 					'assign_to'=>$employee['name'],
 					'from_date'=>$start_date,
 					'to_date'=>$end_date,
 					'type'=> 'SMS',
 					'count'=>$sms_count,
 				];

		$personal = $this->add('xepan\communication\Model_Communication_Personal');
		$personal->addCondition('created_at','>=',$start_date);
		$personal->addCondition('created_at','<=',$this->app->nextDate($end_date));
		$personal->addCondition('created_by_id',$emp);
		$personal_count = $personal->count()->getOne();
		
		$result_array[] = [
 					'assign_to'=>$employee['name'],
 					'from_date'=>$start_date,
 					'to_date'=>$end_date,
 					'type'=> 'Personal',
 					'count'=>$personal_count,
 				];	

		$cl = $report_view->add('CompleteLister',null,null,['view\communicationactivityreport']);
		$cl->setSource($result_array);		
	}

	function quickSearch($app,$search_string,&$result_array,$relevency_mode){
		$this->addExpression('Relevance')->set('MATCH(title, description, communication_type) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$this->addCondition('Relevance','>',0.5);
 		$this->setOrder('Relevance','Desc');
 		
 		if($this->count()->getOne()){
 			foreach ($this->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['title'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_communication_emaildetail',['email_id'=>$data['id']])->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 					'quick_info'=>substr(strip_tags($data['description']),0,200),
 				];
 			}
		}
	}	

	function set_old_communication_info($app,$contact_info,$bypass_true){	
		if($bypass_true)
			return;		

		$communication1 = $this->add('xepan\communication\Model_Communication'); 
		$communication1->addCondition('direction','In');
		$communication1->addCondition('from_id',null);

		$contact_email = $contact_info['value'];
        
        if(! filter_var($contact_email, FILTER_VALIDATE_EMAIL)){
            return;
        }

	    $from_communications = $communication1->addCondition('from_raw', 'like', '%'.$contact_email.'%');
		
		foreach ($from_communications as $previous_communication) {
			$previous_communication['from_id']  = $contact_info['contact_id'];
			$previous_communication->saveAndUnload(); 
		}

		$communication2 = $this->add('xepan\communication\Model_Communication');
		$communication2->addCondition('direction','Out');
		$communication2->addCondition('to_id',null);

       	$to_communications = $communication2->addCondition(
            $this->dsql()->orExpr()
               ->where('to_raw', 'like', '%'.$contact_email.'%')
               ->where('cc_raw', 'like', '%'.$contact_email.'%')
               ->where('bcc_raw', 'like', '%'.$contact_email.'%')
       	);
            
	   	foreach ($to_communications as $previous_communication) {
			   	$previous_communication['to_id']  = $contact_info['contact_id'];
			   	$previous_communication->saveAndUnload(); 
	   	}
	}

	function setRelatedDocument($document){
		$this['related_id']=$document->id;
	}
	
}
