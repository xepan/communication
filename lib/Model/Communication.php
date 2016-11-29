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
	public $acl=false;
	function init(){
		parent::init();

		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);
		$config_m->tryLoadAny();
		
		$this->hasOne('xepan\base\Contact','from_id');
		$this->hasOne('xepan\base\Contact','to_id');
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

		$this->addField('sub_type')->enum(explode(',', $config_m['sub_type']));
		
		$this->addField('direction')->enum(['In','Out']);
		$this->addField('communication_type');

		$this->addField('related_id'); // Can be used anywhere as per requirement
		
		$this->addField('sent_on')->type('date'); // Can be used anywhere as per requirement
		
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('status');
		$this->addField('mailbox');
		$this->addField('communication_channel_id'); // Cna be used for email_settings or sms settings etc.
		$this->addField('is_starred')->type('boolean')->defaultValue(false);

		$this->addField('detailed')->type('boolean')->defaultValue(false);
		$this->addField('extra_info')->defaultValue(['seen_by'=>[]]);
		
		$this->addField('type');
 
		$this->hasMany('xepan\communication\Communication_Attachment','communication_id',null,'EmailAttachments');
		$this->hasMany('xepan\crm\Ticket_Comments','communication_id',null,'Comments');
		$this->hasMany('xepan\crm\SupportTicket','communication_id',null,'SupportTicket');
		
		$this->addExpression('image')->set($this->refSQL('from_id')->fieldQuery('image'));
		$this->addExpression('attachment_count')->set($this->refSQL('EmailAttachments')->addCondition('type','attach')->count());

		$this->addHook('afterInsert',[$this,'throwHookNotification']);
		$this->addHook('beforeDelete',[$this,'deleteAttachments']);
		
		$this->addhook('beforeSave',function($m){
			$m['extra_info'] = json_encode($m['extra_info']);
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

	function getAttachments($urls=true){
		$attach_arry = array();
		if($this->loaded()){
			foreach ($this->ref('EmailAttachments') as $attach) {
				$attach_arry[] = $urls?$attach['file']:$attach['id'];
			}

		}
		
		return $attach_arry;
	}

	function deleteAttachments(){
		$this->ref('EmailAttachments')->each(function($o){
			$o->delete();
		});
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

	function set_old_communication_info($app,$contact_info){	
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
