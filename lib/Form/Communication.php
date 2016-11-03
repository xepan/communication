<?php


namespace xepan\communication;

class Form_Communication extends \Form {

	public $contact = null;
	public $edit_communication_id=null;

	function init(){
		parent::init();
		$edit_model = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$edit_task_model = $this->add('xepan\projects\Model_Task');
		if($this->edit_communication_id){
			$edit_model->load($this->edit_communication_id);
			$edit_task_model->tryLoadBy('related_id',$edit_model->id);
		}

		$this->addClass('form-communication');
		$this->setLayout('view\communicationform');
		$type_field = $this->addField('dropdown','type');
		$type_field->setValueList(['Email'=>'Email','Call'=>'Call','TeleMarketing'=>'TeleMarketing','Personal'=>'Personal','Comment'=>'Comment','SMS'=>'SMS']);
		$type_field->set($edit_model['communication_type']);
		
		$status_field = $this->addField('dropdown','status')->set($edit_model['status']);
		$status_field->setValueList(['Called'=>'Called','Received'=>'Received'])->setEmptyText('Please Select');

		$this->addField('title')->validate('required')->set($edit_model['title']);
		$this->addField('xepan\base\RichText','body')->validate('required')->set($edit_model['description']);
		$from_email=$this->addField('dropdown','from_email')->setEmptyText('Please Select From Email');
		$my_email = $this->add('xepan\hr\Model_Post_Email_MyEmails');
		$from_email->setModel($my_email);
		$email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');
		if($edit_model['from_raw']){
			$email_setting->tryLoadBy('email_username',$edit_model['from_raw']['email']);
			$from_email->set($email_setting->id);
		}

		// $email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');
		if($_GET['from_email'])
			$email_setting->tryLoad($_GET['from_email']);
		$view=$this->layout->add('View',null,'signature')->setHTML($email_setting['signature']);
		$from_email->js('change',$view->js()->reload(['from_email'=>$from_email->js()->val()]));

		$notify_email = $this->addField('Checkbox','notify_email','');
		$notify_email_to = $this->addField('line','notify_email_to');
		$email_to_field = $this->addField('line','email_to');
		$cc_email_field = $this->addField('line','cc_mails');
		$bcc_email_field = $this->addField('line','bcc_mails');

		if($edit_model['cc_raw']){
			$edit_emails_cc =[];		
			foreach ($edit_model['cc_raw'] as $flipped) {
				$edit_emails_cc [] = $flipped['email'];
			}
			$cc_email_field->set(implode(", ", $edit_emails_cc));
		}
		
		
		if($edit_model['bcc_raw']){
			$edit_emails_bcc =[];		
			foreach ($edit_model['bcc_raw'] as $flipped) {
				$edit_emails_bcc [] = $flipped['email'];
			}
			$bcc_email_field->set(implode(", ", $edit_emails_bcc));
		}
		
		$this->addField('line','from_phone')->set($edit_model['from_raw']['number']);

		$emp_field = $this->addField('DropDown','from_person');
		$emp_model = $this->add('xepan\hr\Model_Employee');
		if($edit_model['from_id']){
			$emp_model->load($edit_model['from_id']);
			$emp_field->set($emp_model->id);
		}else{
			$emp_field->set($this->app->employee->id);
		}
		$emp_field->setModel($emp_model);
		$this->addField('line','called_to')->set($edit_model['to_raw'][0]['number']);
		$this->addField('line','from_number');//->set($edit_model['from_raw']['number']);
		$this->addField('line','sms_to');

		$follow_up_field = $this->addField('DropDown','follow_up')->setValueList(['Yes'=>'Yes','No'=>'No'])->setEmptyText('Want to follow-up ?');
		$follow_up_by_field = $this->addField('DropDown','follow_up_type')->setValueList(['Task'=>'Task','Reminder'=>'Reminder']);
		$task_title_field = $this->addField('task_title');
		$starting_date_field = $this->addField('DateTimePicker','starting_at')->set($this->app->now);
		$assign_to_field = $this->addField('DropDown','assign_to');
		$assign_to_field->setModel('xepan\hr\Model_Employee');
		$assign_to_field->set($this->app->employee->id);

		$description_field = $this->addField('text','description');
		$remind_via_field = $this->addField('DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple']);
		$notify_to_field = $this->addField('DropDown','notify_to')->setAttr(['multiple'=>'multiple']);
		$notify_to_field->setModel('xepan\hr\Model_Employee');
		$remind_value_field = $this->addField('remind_value')->set(0);
		$remind_unit_field = $this->addField('DropDown','remind_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days','Weeks'=>'Weeks','months'=>'Months']);
		
		if($edit_task_model->loaded()){
			$task_title_field->set($edit_task_model['task_name']);
			$starting_date_field->set($edit_task_model['starting_date']);
			$assign_to_field->set($edit_task_model['assign_to_id']);
			$description_field->set($edit_task_model['description']);
			$remind_via_field->set($edit_task_model['task_name']);
			$notify_to_field->set($edit_task_model['task_name']);
			$remind_value_field->set($edit_task_model['remind_value']);
			$remind_unit_field->set($edit_task_model['remind_unit']);
						
			$temp = [];
			$temp = explode(',', $edit_task_model['notify_to']);

			$temp1 = [];
			$temp1 = explode(',', $edit_task_model['remind_via']);

			$notify_to_field->set($temp)->js(true)->trigger('changed');
			$remind_via_field->set($temp1)->js(true)->trigger('changed');
		}

		$type_field->js(true)->univ()->bindConditionalShow([
			'Email'=>['from_email','email_to','cc_mails','bcc_mails'],
			'Call'=>['from_email','from_phone','from_person','called_to','notify_email','notify_email_to','status'],
			'TeleMarketing'=>['from_phone','called_to'],
			'Personal'=>['from_person'],
			'Comment'=>['from_person'],
			'SMS'=>['from_number','sms_to']
		],'div.atk-form-row');

		$follow_up_field->js(true)->univ()->bindConditionalShow([
			'Yes'=>['follow_up_type','task_title','starting_at','assign_to','description'],
		],'div.atk-form-row');

		$follow_up_by_field->js(true)->univ()->bindConditionalShow([
			'Reminder'=>['remind_via','notify_to','remind_value','remind_unit'],
		],'div.atk-form-row');

		// $notify_email->js(true)->univ()->bindConditionalShow([
		// 	'Phone'=>['notify_email_to'],
		// ],'div.atk-form-row');

		$this->addHook('validate',[$this,'validateFields']);

	}

	function validateFields(){
        $commtype = $this['type'];
		$communication = $this->add('xepan\communication\Model_Communication_'.$commtype);
  //      	if($this['follow_up'] == 'Yes' && $this['task_title'] == '')
		// 	$this->displayError('task_field','This field is required');
		// if($this['follow_up_type'] == 'Reminder' && ($this['remind_via']=='' || $this['notify_to']==''))
		// 	$this->displayError('remind_via','All fields are required');
        
        switch ($commtype) {
			case 'Email':
				foreach (explode(',', $this['email_to']) as $value) {
					if( ! filter_var(trim($value), FILTER_VALIDATE_EMAIL))
						$this->displayError('email_to',$value.' is not a valid email');
				}
				$_to_field='email_to';

				if(!$communication->verifyTo($this[$_to_field], $this->contact->id)){
					throw new \Exception($commtype." of customer not present");	
				}
				$communication['direction']='Out';
				break;
			case 'TeleMarketing':
				$this['status'] = 'Called';	
			case 'Call':
				if(!$this['from_phone'])
					$this->displayError('from_phone','from_phone is required');
				if(!$this['called_to'])
					$this->displayError('called_to','called_to is required');
				if(!$this['status'])
					$this->displayError('status','Status is required');
				
				if($this['notify_email']){
					if(!$this['notify_email_to'])
						$this->displayError('notify_email_to','Notify Email is required');
					if(!$this['from_email'])
						$this->displayError('from_email','From  Email is required to send Email');
					
					foreach (explode(',', $this['notify_email_to']) as $value) {
						if( ! filter_var(trim($value), FILTER_VALIDATE_EMAIL))
							$this->displayError('notify_email_to',$value.' is not a valid email');
					}
					$_to_field='notify_email_to';	
				}

				$_to_field='called_to';
				break;
			case 'SMS':
				if(!$this['from_number'])
					$this->displayError('from_number','from_number is required');
				if(!$this['sms_to'])
					$this->displayError('sms_to','sms_to is required');
				$_to_field='sms_to';
				break;
			case 'Personal':
				$_to_field=null;
				break;
			case 'Comment':
				$_to_field=null;
				break;	
			default:
				break;
		}
    }

    function setContact($contact){
    	$this->contact = $contact;
    }

    function process(){

    	if(!$this->contact || ! $this->contact instanceof \xepan\base\Model_Contact)
    		throw $this->exception('Please setContact() on this form first');

    	$commtype = $this['type'];
					
		$communication = $this->add('xepan\communication\Model_Communication_'.$commtype);
		if($this->edit_communication_id){
			$communication->load($this->edit_communication_id);
		}
		$communication['from_id']=$this['from_person'];
		$communication['to_id']=$this->contact->id;

		switch ($commtype) {
			case 'Email':
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($this['from_email']?:-1);
				$_from = $send_settings['from_email'];
				$_from_name = $send_settings['from_name'];
				$_to_field='email_to';
				$communication->unload();
				$communication->setFrom($_from,$_from_name);
				$communication['direction']='Out';
				break;
			case 'TeleMarketing':
				$this['status'] = 'Called';	
			case 'Call':
				$send_settings = $this['from_phone'];
				if($this['status']=='Received'){
					$communication['from_id']=$this->contact->id;
					$communication['to_id']=$this['from_person']; // actually this is to person this time
					$communication['direction']='In';
					$communication->setFrom($this['from_phone'],$this->contact['name']);
				}else{					
					$communication['from_id']=$this['from_person']; // actually this is to person this time
					$communication['to_id']=$this->contact->id;
					$communication['direction']='Out';
					$employee_name=$this->add('xepan\hr\Model_Employee')->load($this['from_person'])->get('name');
					$communication->setFrom($this['from_phone'],$employee_name);
				}

				if($this['notify_email']){
					if(!$this['notify_email_to'])
						$this->displayError('notify_email_to','Notify Email is required');
					
					$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
					$send_settings->tryLoad($this['from_email']?:-1);
				}

				$communication['status']=$this['status'];
				$_to_field='called_to';

				break;

			case 'SMS':
				if(!$this['from_number'])
					$this->displayError('from_number','from_number is required');
				if(!$this['sms_to'])
					$this->displayError('sms_to','sms_to is required');
				$send_settings = $this->add('xepan\communication\Model_Epan_SMSSetting');
				$send_settings->load($this['from_sms']);
				$_from = $email_settings['from_number'];
				$_from_name = $email_settings['from_sms_code'];
				$_to_field='sms_to';
				$communication->setFrom($_from,$_from_name);
				$communication['direction']='Out';
				break;
			case 'Personal':
				$_from = $this->app->employee->id;
				$_from_name = $this->app->employee['name'];
				$_to = $this->contact->id;
				$_to_name = $this->contact['name'];
				$_to_field=null;
				$communication->addTo($_to, $_to_name);
				$communication->setFrom($_from,$_from_name);
				break;
			case 'Comment':
				$_from = $this->app->employee->id;
				$_from_name = $this->app->employee['name'];
				$_to = $this->contact->id;
				$_to_name = $this->contact['name'];
				$_to_field=null;
				$communication->addTo($_to, $_to_name);
				$communication->setFrom($_from,$_from_name);
				break;	
			default:
				break;
		}
		
		$communication->setSubject($this['title']);
		$communication->setBody($this['body']);

		if($_to_field){				
			foreach (explode(',',$this[$_to_field]) as $to) {				
				$communication->addTo(trim($to));
			}			
		}
		
		if($this['bcc_mails']){
			foreach (explode(',',$this['bcc_mails']) as $bcc) {
					if( ! filter_var(trim($bcc), FILTER_VALIDATE_EMAIL))
						$this->displayError('bcc_mails',$bcc.' is not a valid email');
				$communication->addBcc($bcc);
			}
		}

		if($this['cc_mails']){
			foreach (explode(',',$this['cc_mails']) as $cc) {
					if( ! filter_var(trim($cc), FILTER_VALIDATE_EMAIL))
						$this->displayError('cc_mails',$cc.' is not a valid email');
				$communication->addCc($cc);
			}
		}

		if(isset($send_settings)){			
			$communication->send(
					$send_settings,
					$this['notify_email']?$this['notify_email_to']:''
					);			
		}else{
			$communication['direction']='Out';
			$communication->save();
		}

		if($this['follow_up'] == 'Yes'){
			$model_task = $this->add('xepan\projects\Model_Task');
			$model_task['type'] = 'Followup';
			$model_task['task_name'] = $this['task_title'];
			$model_task['created_by_id'] = $this->app->employee->id;
			$model_task['starting_date'] = $this['starting_at'];
			$model_task['assign_to_id'] = $this['assign_to'];
			$model_task['description'] = $this['description'];
			$model_task['remind_value'] = $this['remind_value'];
			$model_task['remind_unit'] = $this['remind_unit'];
			$model_task['remind_via'] = $this['remind_via'];
			$model_task['notify_to'] = $this['notify_to'];
			$model_task['related_id'] = $communication->id;
			if($this['follow_up_type'] == 'Reminder')
				$model_task['set_reminder'] = true;
			$model_task->save();
		}

		return $communication;
    }
}