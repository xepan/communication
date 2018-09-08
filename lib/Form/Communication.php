<?php


namespace xepan\communication;

class Form_Communication extends \Form {

	public $contact = null;
	public $edit_communication_id=null;

	function init(){
		parent::init();
		$edit_model = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		if($this->edit_communication_id){
			$edit_model->load($this->edit_communication_id);
		}

		$this->addClass('form-communication');
		$this->setLayout('view\communicationform');
		$type_field = $this->addField('dropdown','type');
		$type_field->setValueList(['Email'=>'Email','Call'=>'Call','TeleMarketing'=>'TeleMarketing','Personal'=>'Personal','Comment'=>'Comment','SMS'=>'SMS']);
		$type_field->set($edit_model['communication_type']);

		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->tryLoadAny();
		$sub_type_array = explode(",",$config_m['sub_type']);
		
		$sub_type_field = $this->addField('dropdown','sub_type',$config_m['sub_type_1_label_name'])->set($edit_model['sub_type'])->setEmptyText('Please Select');
		$sub_type_field->setValueList(array_combine($sub_type_array,$sub_type_array));

		$calling_status_array = explode(",",$config_m['calling_status']);
		$calling_status_field = $this->addField('dropdown','calling_status',$config_m['sub_type_2_label_name'])->set($edit_model['calling_status'])->setEmptyText('Please Select');
		$calling_status_field->setValueList(array_combine($calling_status_array,$calling_status_array));
		
		$sub_type_3_array = explode(",",$config_m['sub_type_3']);
		$sub_type_field = $this->addField('dropdown','sub_type_3',$config_m['sub_type_3_label_name'])->set($edit_model['sub_type_3'])->setEmptyText('Please Select');
		$sub_type_field->setValueList(array_combine($sub_type_3_array,$sub_type_3_array));

		$this->layout->template->set('sub_type_1_label_name',$config_m['sub_type_1_label_name']);
		$this->layout->template->set('sub_type_2_label_name',$config_m['sub_type_2_label_name']);
		$this->layout->template->set('sub_type_3_label_name',$config_m['sub_type_3_label_name']);

		if($this->app->auth->model->isSuperUser()){
			$date_field = $this->addField('DateTimePicker','date')->set($edit_model['created_at']);
		}		
		
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
		$called_to_field = $this->addField('xepan\base\DropDown','called_to')->set($edit_model['to_raw'][0]['number']);
		$called_to_field->select_menu_options=['tags'=>true];
		$called_to_field->validate_values=false;
		// $called_to_field->setAttr(['multiple'=>'multiple']);
		$this->addField('line','from_number');
		$this->addField('line','sms_to');

		// SCORE BUTTONS START
		$score_field = $this->addField('hidden','score')->set('0');
		
		
		/**********************************
			FOLLOWUP BEGIN
		************************************/
		if($edit_model->loaded()){
			$this->layout->template->del('score_button_wrapper');
			$this->layout->template->del('followup_form_wrapper');
		}

		if(!$edit_model->loaded()){
			$follow_up_field = $this->addField('checkbox','follow_up','Add Followup');
			// $task_title_field = $this->addField('task_title');
			$starting_date_field = $this->addField('DateTimePicker','starting_at');
			$starting_date_field->js(true)->val('');
			$assign_to_field = $this->addField('DropDown','assign_to');
			$assign_to_field->setModel('xepan\hr\Model_Employee')->addCondition('status','Active');
			$assign_to_field->set($this->app->employee->id);
			$description_field = $this->addField('text','description');

			$set_reminder_field = $this->addField('checkbox','set_reminder');
			$remind_via_field = $this->addField('DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
			$notify_to_field = $this->addField('DropDown','notify_to')->setAttr(['multiple'=>'multiple'])->setEmptyText('Please select a value');
			$notify_to_field->setModel('xepan\hr\Model_Employee')->addCondition('status','Active');
			$reminder_time  = $this->addField('DateTimePicker','reminder_time');
			$reminder_time->js(true)->val('');

			$force_remind_field = $this->addField('checkbox','force_remind','Enable Snoozing [Repetitive Reminder]');
			$snooze_field = $this->addField('snooze_duration');
			$remind_unit_field = $this->addField('DropDown','remind_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');
			
			$follow_up_field->js(true)->univ()->bindConditionalShow([
				true=>['follow_up_type','task_title','starting_at','assign_to','description','set_reminder','existing_schedule']
			],'div.atk-form-row');

			$set_reminder_field->js(true)->univ()->bindConditionalShow([
				true=>['remind_via','notify_to','reminder_time','force_remind']
			],'div.atk-form-row');

			$force_remind_field->js(true)->univ()->bindConditionalShow([
				true=>['snooze_duration','remind_unit']
			],'div.atk-form-row');
		}

		$this->layout->add('xepan\projects\View_EmployeeFollowupSchedule',['employee_field'=>$assign_to_field,'date_field'=>$starting_date_field],'existing_schedule');

		/**********************************
			FOLLOWUP END
		************************************/

		$type_field->js(true)->univ()->bindConditionalShow([
			'Email'=>['from_email','email_to','cc_mails','bcc_mails'],
			'Call'=>['from_email','from_phone','from_person','called_to','notify_email','notify_email_to','status','calling_status'],
			'TeleMarketing'=>['from_phone','called_to'],
			'Personal'=>['from_person'],
			'Comment'=>['from_person'],
			'SMS'=>['from_number','sms_to']
		],'div.atk-form-row');

		$this->addHook('validate',[$this,'validateFields']);

	}

	function validateFields(){
        $commtype = $this['type'];
		$communication = $this->add('xepan\communication\Model_Communication_'.$commtype);
       	
       	/********************************
			FOLLOWUP VALIDATION
       	**********************************/
       	if(!$this->edit_communication_id){
	       	if($this['follow_up']){
				// if($this['task_title'] == ''){
				// 	$this->displayError('task_title','Task title field is required');
				// }

				if($this['starting_at'] == ''){
					$this->displayError('starting_at','Starting date title field is required');
				}

				// if($this['task_title'] == ''){
				// 	$this->displayError('task_title','Task title field is required');
				// }

				if($this['set_reminder']){
					if($this['remind_via'] == null){
						$this->displayError('remind_via','Remind mediu is required');
					}

					if($this['notify_to'] == null){
						$this->displayError('notify_to','Notify to field is required');
					}

					if($this['reminder_time'] == ''){
						$this->displayError('reminder_time','Reminder time field is required');
					}

					if($this['force_remind']){					
						if($this['snooze_duration'] == ''){
							$this->displayError('snooze_duration','Snooze duration field is required');
						}

						if($this['remind_unit'] == ''){
							$this->displayError('remind_unit','Snooze unit field is required');
						}
					}
				}
	       	}
       	}
       	
    	/*********************************
			FOLLOWUP VALIDATION
       	**********************************/

        switch ($commtype) {
			case 'Email':
				foreach (explode(',', $this['email_to']) as $value) {
					if( ! filter_var(trim($value), FILTER_VALIDATE_EMAIL))
						$this->displayError('email_to',$value.' is not a valid email');
				}
				$_to_field='email_to';

				if(isset($this->contact->id)){
					if(!$communication->verifyTo($this[$_to_field], $this->contact->id)){
						throw new \Exception($commtype." of customer not present");	
					}
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
				if(!$this['calling_status'])
					$this->displayError('calling_status','Status is required');
				
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
		$communication['sub_type']=$this['sub_type'];
		$communication['calling_status']=$this['calling_status'];
		$communication['sub_type_3']=$this['sub_type_3'];
		$communication['score']=$this['score'];

		switch ($commtype) {
			case 'Email':
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($this['from_email']?:-1);
				$_from = $send_settings['from_email'];
				$_from_name = $send_settings['from_name'];
				$_to_field='email_to';
				$communication->unload();
				$communication['from_id']=$this->app->employee->id;
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
				$communication['from_id'] = $this->app->employee->id;
				$communication['description'] = $this['body'];
				$_from = $this->app->employee->id;
				$_from_name = $this->app->employee['name'];
				$_to_field='sms_to';
				foreach (explode(",", $this[$_to_field]) as $nos) {
					$communication->addTo($nos,$this->contact['name']);
					
				}
				$communication->setFrom($_from,$_from_name);
				$communication['direction']='Out';
				$communication['communication_channel_id'] = $this['from_sms'];
				$communication['title'] = 'SMS: '.substr(strip_tags($this['body']),0,35)." ...";
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

		if($this->hasElement('date')){
			$communication['created_at'] = $this['date'];
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

		// INSERTING SCORE
		if($this['score']){
			$model_point_system = $this->add('xepan\base\Model_PointSystem');
			$model_point_system['contact_id'] = $this->contact->id;
			$model_point_system['score'] = $this['score'];
			$model_point_system->save();
		}

		/*************************************
			INSERTING FOLLOWUP BEGIN
		*************************************/
		if(!$this->edit_communication_id){
			if($this['follow_up']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = 'Followup '. $this->contact['name_with_type'];
				$model_task['created_by_id'] = $this->app->employee->id;
				$model_task['starting_date'] = $this['starting_at'];
				$model_task['assign_to_id'] = $this['assign_to'];
				$model_task['description'] = $this['description'];
				$model_task['related_id'] = $this->contact->id;
				
				if($this['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $this['reminder_time'];
					$model_task['remind_via'] = $this['remind_via'];
					$model_task['notify_to'] = $this['notify_to'];
					
					if($this['force_remind']){
						$model_task['snooze_duration'] = $this['snooze_duration'];
						$model_task['remind_unit'] = $this['remind_unit'];

					}
				}
			$model_task->save();
		}

		/*************************************
			INSERTING FOLLOWUP END
		*************************************/	
		}

		return $communication;
    }
}