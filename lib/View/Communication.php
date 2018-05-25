<?php

namespace xepan\communication;



class View_Communication extends \View {
	
	public $allowed_channels = ['email','call','call_sent','call_received','meeting','personal','comment'];

	public $channel_email=true;
	public $channel_sms=true;
	public $channel_call_sent=true;
	public $channel_call_received=true;
	public $channel_meeting=true;
	public $channel_comment=true;

	public $showCommunicationHistory = true;
	public $showAddCommunications = true;
	public $showFilter = true;


	public $contact=null;

	public $is_editing = false;

	public $acl_controller = null;

	public $historyLister;

	function init(){
		parent::init();
		$this->template->loadTemplateFromString($this->myTemplate());

		$this->app->stickyGET('edit_communication_id');
		$this->edit_vp = $this->add('VirtualPage')
			->set(function($page){
				$id = $_GET['edit_communication_id'];
				
				$form_fields = [
							'communication_sub_type'=>'Edit Communication~c1~4',
							'calling_status~Status'=>'c2~4',
							'created_at'=>'c3~4',
							'from_number'=>'c1~4',
							'to_number'=>'c2~4',
							'employee'=>'c3~4',
							'description'=>'c4~12'
						];

				$m = $this->add('xepan\communication\Model_Communication');
				$m->load($id);
				if($m['status'] == "Called"){
					$comm_model = $this->add('xepan\communication\Model_Communication_Call');
					$comm_model->addCondition('status','Called');
					$comm_model->addCondition('id',$id);
					$comm_model->tryLoadAny();

					$form_fields = [
							'communication_sub_type'=>'Edit Called Communication~c1~4',
							'calling_status'=>'c2~4',
							'created_at'=>'c3~4',
							'from_number'=>'c1~4',
							'to_number'=>'c2~4',
							'employee'=>'c3~4',
							'description'=>'c4~12'
						];

				}elseif($m['status'] == "Received"){
					$comm_model = $this->add('xepan\communication\Model_Communication_Call');
					$comm_model->addCondition('status','Received');
					$comm_model->addCondition('id',$id);
					$comm_model->tryLoadAny();

					$form_fields = [
							'communication_sub_type'=>'Edit Call Received Communication~c1~4',
							'calling_status'=>'c2~4',
							'created_at'=>'c3~4',
							'from_number'=>'c1~4',
							'to_number'=>'c2~4',
							'employee'=>'c3~4',
							'description'=>'c4~12'
						];
				}elseif($m['status'] == "Personal"){
					$comm_model = $this->add('xepan\communication\Model_Communication_Personal');
					$comm_model->addCondition('status','Personal');
					$comm_model->addCondition('id',$id);
					$comm_model->tryLoadAny();

					$form_fields = [
							'communication_sub_type'=>'Edit Personal Communication~c1~3',
							'calling_status~Status'=>'c2~3',
							'created_at'=>'c3~3',
							'employee'=>'c4~3',
							'description'=>'c5~12'
						];
				}elseif($m['status'] == "Commented"){
					$comm_model = $this->add('xepan\communication\Model_Communication_Comment');
					$comm_model->addCondition('status','Commented');
					$comm_model->addCondition('id',$id);
					$comm_model->tryLoadAny();

					$form_fields = [
							'communication_sub_type'=>'Edit Comment Communication~c1~3',
							'calling_status~Status'=>'c2~3',
							'created_at'=>'c3~3',
							'employee'=>'c4~3',
							'description'=>'c5~12'
						];
				}

				$contact = $this->add('xepan\base\Model_Contact');
				$contact->load($comm_model['to_id']);
				
				$form = $page->add('Form');
				$form->add('xepan\base\Controller_FLC')
					->makePanelCollepsible()
					->closeOtherPanels()
					->addContentSpot()
					->layout($form_fields);

				$config_m = $this->add('xepan\communication\Model_Config_SubType');
				$config_m->tryLoadAny();

				
				$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
							
				$company_m->tryLoadAny();

				$company_number = explode(",", $company_m['mobile_no']);
				$company_number = array_combine($company_number, $company_number);

				$sub_type_array = explode(",",$config_m['sub_type']);
				$sub_type_field = $form->addField('xepan\base\DropDown','communication_sub_type')->setEmptyText("Please Select");
				$sub_type_field->setValueList(array_combine($sub_type_array,$sub_type_array));
				$sub_type_field->set($comm_model['sub_type']);

				$status_array = explode(",",$config_m['calling_status']);
				$status_field = $form->addField('xepan\base\DropDown','calling_status')->setEmptyText('Please Select');
				$status_field->setValueList(array_combine($status_array,$status_array));
				$status_field->set($comm_model['calling_status']);

				$form->addField('DateTimePicker','created_at')->validate('required')->set($comm_model['created_at']);
				
				$employee_field = $form->addField('xepan\hr\Employee','employee')->set($comm_model['from_id']);

				$form->addField('xepan\base\RichText','description')
						->set($comm_model['description']);

				if(isset($form_fields['from_number'])){
					$from_number_field = $form->addField('xepan\base\DropDown','from_number');
					$emp_phones = $this->app->employee->getPhones();
					$emp_phones = array_combine($emp_phones, $emp_phones);
					$from_number_field->setValueList(array_merge(array_filter($company_number),array_filter($emp_phones)));
					$from_number_field->select_menu_options = ['tags'=>true];
					$from_number_field->validate_values = false;

					$from_raw = json_decode($comm_model['from_raw'],true);
					$from_number_field->set($from_raw['number']);
				}

				if(isset($form_fields['to_number'])){

					$phones = [];
					$phones = $contact->getPhones();
					$to_number_field = $form->addField('xepan\base\DropDown','to_number');
					$to_number_field->setValueList(array_combine($phones,$phones));
					$to_number_field->select_menu_options = ['tags'=>true];
					$to_number_field->validate_values = false;

					$to_raw = json_decode($comm_model['to_raw'],true);
					$to_number_field->set($to_raw[0]['number']);
				}

				$form->addSubmit('Update Communication')->addClass('btn btn-primary');

				if($form->isSubmitted()){

					foreach ($form_fields as $key => $value) {
						$comm_model[$key] = $form[$key];
					}
					// $comm_model['created_at'] = $form['created_at'];

					if(isset($form_fields['to_number'])){
						$comm_model->addTo($form['to_number'],$contact['name']);
					}

					if(isset($form_fields['from_number'])){
						$emp = $this->add('xepan\hr\Model_Employee')->load($form['employee']);
						// $comm_model->setFrom($form['from_number'],$emp['name']);
						$to=['name'=>$emp['name'],'number'=>$form['from_number']];
						$comm_model->set('from_raw',$to);
					}
					$comm_model['from_id'] = $form['employee'];
					$comm_model->save();

					$form->js(null,$form->js()->reload())->univ()->successMessage('communication updated')->execute();
				}

			});
	
		$this->acl_controller = $this->add('xepan\hr\Controller_ACL',['based_on_model'=>'xepan\communication\Model_Communication']);

	}

	function filter(){

		if($start_date = $this->app->stickyGET('start_date')){
			$this->model->addCondition('created_at','>=',$start_date);
		}

		if($end_date = $this->app->stickyGET('end_date')){
			$this->model->addCondition('created_at','<',$this->app->nextDate($end_date));
		}

		if($related_contact_id = $this->app->stickyGET('related_contact_id')){
			$this->model->addCondition([
								['from_id',$related_contact_id],
								['to_id',$related_contact_id]
							]);
		}

		if($comm_type = $this->app->stickyGET('communication_type')){
			$this->model->addCondition('communication_type',explode(",", $comm_type));
		}

		if($direction = $this->app->stickyGET('direction')){
			$this->model->addCondition('direction',$direction);
		}

		if($search = $this->app->stickyGET('search_string')){
			$this->model->addExpression('Relevance')
					->set('MATCH(title,description,communication_type) AGAINST ("'.$search.'")');
			$this->model->addCondition('Relevance','>',0);
 			$this->model->setOrder('Relevance','Desc');
		}
	}

	function setCommunicationsWith($contact){
		$this->contact = $contact;
		$communication = $this->add('xepan\communication\Model_Communication');
		$communication->addCondition([['from_id',$contact->id],['to_id',$contact->id]]);
		$communication->setOrder('created_at','desc');

		return $this->setModel($communication);
	}

	function setCommunicationsRelatedToDocument($document){

	}


	function setModel($model){
		if($model->loaded()) $this->is_editing = true;
		return parent::setModel($model);
	}

	function showCommunicationHistory($show){
		$this->showCommunicationHistory= $show;
	}

	function showAddCommunications($show){
		$this->showAddCommunications = $show;
	}

	function addChannels($channels){
		if(is_array($channels)){
			foreach ($channels as $ch) {
				$this->addChannels($ch);
			}
			return;
		}

		if(!in_array(strtolower($channels), $this->channels)) 
			throw $this->exception('Unknown channel')->addMoreInfo('Available Channels ',implode(", ", $this->channels))->addMoreInfo('Provided Channel',$channels);
			
		switch (strtolower($channels)) {
			case 'email':
				$this->channel_email = true;
				break;
			case 'sms':
				$this->channel_sms = true;
				break;
			case 'call':
				$this->channel_call_sent = true;
				$this->channel_call_received = true;
				break;
			case 'call_sent':
				$this->channel_call_sent = true;
				break;
			case 'call_received':
				$this->channel_call_received = true;
				break;
			case 'meeting':
			case 'personal':
				$this->channel_meeting = true;
				break;
			case 'comment':
				$this->channel_comment = true;
				break;
		}
	}

	function addTopBar(){

		if(!$this->acl_controller->canAdd()) return;

		if($this->channel_email) {
			$html = '<button type="button" class="btn btn-primary"><i class="fa fa-envelope"></i><br/>Email</button>';
			$icon = $this->add('View',null,'icons')->addClass('btn-group')->setAttr('role','group')->setAttr('title','Create Email Communication')->setHtml($html);
			$this->manageEmail($icon);
		}

		if($this->channel_sms) {
			$html = '<button type="button" class="btn btn-primary"><i class="fa fa-envelope"></i><br/>SMS</button>';
			$icon = $this->add('View',null,'icons')->addClass('btn-group')->setAttr('role','group')->setAttr('title','Send SMS')->setHtml($html);
			$this->manageSms($icon);
		}

		if($this->channel_call_sent){
			$html = '<button type="button" class="btn btn-primary"><i class="fa fa-upload"></i><br/>Called</button>';
			$icon = $this->add('View',null,'icons')->addClass('btn-group ')->setAttr('role','group')->setAttr('title','Create Phone Called Communication')->setHtml($html);
			$this->manageCalled($icon);
		}

		if($this->channel_call_received){
			$html = '<button type="button" class="btn btn-primary"><i class="fa fa-download "></i><br/>Received</button>';
			$icon = $this->add('View',null,'icons')->addClass('btn-group')->setAttr('role','group')->setAttr('title','Create Phone Received Communication')->setHtml($html);
			$this->manageCallReceived($icon);
		}

		if($this->channel_meeting){
			$html = '<button type="button" class="btn btn-primary"><i class="fa fa-users"></i><br/>Meeting</button>';
			$icon = $this->add('View',null,'icons')->addClass('btn-group')->setAttr('role','group')->setAttr('title','Create Personal/Meeting Communication')->setHtml($html);
			$this->manageMeeting($icon);
		}

		if($this->channel_comment){
			$html = '<button type="button" class="btn btn-primary"><i class="fa fa-comments "></i><br/>Comment</button>';
			$icon = $this->add('View',null,'icons')->addClass('btn-group')->setAttr('role','group')->setAttr('title','Create Comment Communication')->setHtml($html);
			$this->manageComment($icon);
		}

		if($this->showFilter){
			$this->addFilter();
		}
	}

	function addCommunicationHistory(){
		$communication = $this->model;

		$this->historyLister = $lister=$this->add('xepan\communication\View_Lister_NewCommunication',['contact_id'=>$this->contact->id],null,null);
		if($this->app->stickyGET('communication_filter')){
			$this->filter();
		}

		$lister->setModel($communication)->setOrder(['created_at desc','id desc']);
		$p = $lister->add('xepan\base\Paginator',null,'Paginator');
		$p->setRowsPerPage($this->ipp = 10);

		$lister->js('click',$this->js()->univ()->frameURL('Edit Communication',[$this->app->url($this->edit_vp->getURL()),'edit_communication_id'=>$this->js()->_selectorThis()->data('id')]))
			->_selector('.do-view-edit-communication');
	}

	function manageEmail($email_icon, $edit_communication= null){
		$email_popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$email_popup->setTitle('Send New Email');
		$default_to_ids=implode(",",$this->contact->getEmails());
		$form = $email_popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->layout([
				'subject'=>'Email Content|danger~c1~8',
				'from_email_id'=>'c3~4',

				'body'=>'c4~12',
				
				'follow_up'=>'c5~6',
				'score_buttons~Score'=>'c7~6',
				'score~'=>'c7~',
				
				'assigned_to'=>'c10~6',
				'followup_on'=>'c9~6',

				'followup_detail'=>'c11~12',
				'set_reminder~'=>'c12~12',
				'reminder_at'=>'c13~2',
				'remind_via'=>'c14~2',
				'notify_to'=>'c15~4',
				'snooze_duration'=>'c16~2',
				'snooze_unit'=>'c17~2',
				'to'=>'Send To (' .$default_to_ids.  ')~c1~8~closed',
				'cc'=>'c3~5',
				'cc_me'=>'c35~1',
				'bcc'=>'c4~5',
				'bcc_me'=>'c5~1',

			]);
		$subject = $form->addField('subject')->validate('required');
		$form->addField('xepan\base\RichText','body');
		$form->addField('to')->set($default_to_ids);
		
		$cc = $form->addField('cc');
		$cc_me = $form->addField('Checkbox','cc_me');
		$cc_me->js('click',$cc->js()->val($this->app->employee->getEmails()));
		
		$bcc = $form->addField('bcc');
		$bcc_me = $form->addField('Checkbox','bcc_me');
		$bcc_me->js('click',$bcc->js()->val($this->app->employee->getEmails()));
		
		$allwed_emails = $form->addField('xepan\hr\EmployeeAllowedEmail','from_email_id')->validate('required');
		
		$follow_up = $form->addField('Checkbox','follow_up');
		$followup_on = $form->addField('DateTimePicker','followup_on');
		$followup_on->js(true)->val('');
		$assigned_to = $form->addField('xepan\hr\Employee','assigned_to')->setCurrent();
		$form->addField('Text','followup_detail');

		$score = $form->addField('Hidden','score')->set(0);
		$set = $form->layout->add('ButtonSet',null,'score_buttons');
		$up_btn = $set->add('Button')->set('+10')->addClass('btn');
		$down_btn = $set->add('Button')->set('-10')->addClass('btn');
		

		$reminder = $form->addField('CheckBox','set_reminder');
		$reminder_at = $form->addField('DateTimePicker','reminder_at');
		$reminder_at->js(true)->val('');
		$remind_via = $form->addField('xepan\base\DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
		$notify_to = $form->addField('xepan\hr\Employee','notify_to')->setAttr(['multiple'=>'multiple'])->setCurrent();
		$form->addField('snooze_duration');
		$snooz_unit= $form->addField('xepan\base\DropDown','snooze_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');

		$reminder->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['reminder_at','remind_via','notify_to','snooze_duration','snooze_unit']
		],'div.col-md-2,div.col-md-4');

		$follow_up->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['followup_on','assigned_to','followup_detail']
		],'div.col-md-12,div.col-md-6');



		if($form->isSubmitted()){

			// check validation
			if($form['follow_up'] && !$form['followup_on']){
				$form->error('followup_on','Followup on must not be empty');
			}elseif($form['followup_detail'] && (!$form['followup_on'])){
				$form->error('followup_on','Followup on must not be empty');
			}

			// reminder validation
			if($form['set_reminder']){
				if(!$form['follow_up']) $form->error('follow_up','Followup must be set to put on reminder');
				if(!$form['reminder_at']) $form->error('reminder_at','Reminder at must not be empty');
				if(!$form['remind_via']) $form->error('remind_via','Remind Via must not be empty');
				if(!$form['notify_to']) $form->error('notify_to','Notify To must not be empty');

				if($form['snooze_duration'] && !$form['snooze_unit'])
					$form->error('snooze_unit','must not be empty');

				if($form['snooze_unit'] && !$form['snooze_duration'])
					$form->error('snooze_duration','must not be empty');
			}

			$communication = $this->add('xepan\communication\Model_Communication_Email');
			$communication['from_id']=$this->app->employee->id;
			$communication['to_id']=$this->contact->id;
			$communication['score']=$form['score'];

			$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
			$send_settings->tryLoad($form['from_email_id']?:-1);
			$_from = $send_settings['from_email'];
			$_from_name = $send_settings['from_name'];
			
			$communication->setSubject($form['subject']);
			$communication->setBody($form['body']);
			$communication->setFrom($_from,$_from_name);

			$communication['direction']='Out';
			foreach (explode(',',$form['to']) as $to) {
					if( ! filter_var(trim($to), FILTER_VALIDATE_EMAIL))
						$form->displayError('to',$to.' is not a valid email');
				$communication->addTo($to);
			}

			if($form['cc']){
				foreach (explode(',',$form['cc']) as $cc) {
						if( ! filter_var(trim($cc), FILTER_VALIDATE_EMAIL))
							$form->displayError('cc',$cc.' is not a valid email');
					$communication->addCc($cc);
				}
			}

			if($form['bcc']){
				foreach (explode(',',$form['bcc']) as $bcc) {
						if( ! filter_var(trim($bcc), FILTER_VALIDATE_EMAIL))
							$form->displayError('bcc',$bcc.' is not a valid email');
					$communication->addBcc($bcc);
				}
			}

			if($form->hasElement('date')){
				$communication['created_at'] = $form['date'];
			}
			$communication->send($send_settings);

			// SCORE
			if($form['score']){
				$model_point_system = $this->add('xepan\base\Model_PointSystem');
				$model_point_system['contact_id'] = $this->contact->id;
				$model_point_system['score'] = $form['score'];
				$model_point_system['remarks'] = 'Comm: Email: '.$form['subject'];
				$model_point_system->save();
			}

			// FOLLOW UP
			if($form['follow_up']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = 'Followup '. $this->contact['name_with_type'];
				$model_task['created_by_id'] = $form->app->employee->id;
				$model_task['starting_date'] = $form['followup_on'];
				$model_task['assign_to_id'] = $form['assigned_to'];
				$model_task['description'] = $form['followup_detail'];
				$model_task['related_id'] = $this->contact->id;
				if($form['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $form['reminder_at'];
					$model_task['remind_via'] = $form['remind_via'];
					$model_task['notify_to'] = $form['notify_to'];
					
					if($form['snooze_duration']){
						$model_task['snooze_duration'] = $form['snooze_duration'];
						$model_task['remind_unit'] = $form['snooze_unit'];
					}
				}
				$model_task->save();
			}

			$form->js(null,[$email_popup->js()->modal('hide'),$this->historyLister->js()->reload()])->reload()->univ()->successMessage('Email Sent')->execute();

		}

		// JAVASCRIP SECTION
		// $subject->js('change',"\$('#$follow_title->name').val('".$this->contact['name']." : ' + \$('#$subject->name').val())");
		$up_btn->js('click',[$score->js()->val(10),$down_btn->js()->removeClass('btn-danger'),$this->js()->_selectorThis()->addClass('btn-success')]);
		$down_btn->js('click',[$score->js()->val(-10),$up_btn->js()->removeClass('btn-success'),$this->js()->_selectorThis()->addClass('btn-danger')]);
		$email_icon->js('click',[ // show event
			$email_popup->js()->modal('show',['backdrop'=>'static','keyboard'=>false]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();'),
			$followup_on->js()->val(''),
			$reminder_at->js()->val(''),
			$allwed_emails->js()->select2('val',''),
			$assigned_to->js()->select2('val',$this->app->employee->id),
			$remind_via->js()->select2('val',''),
			$notify_to->js()->select2('val',''),
			$snooz_unit->js()->select2('val','')
		]);
		
	}

	function manageCalled($called_icon){
		$called_popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$called_popup->setTitle('Phone Called - Log Communication of '.$this->contact['name']);
		
		$form = $called_popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->addContentSpot()
			->layout([
				'communication_sub_type'=>'Called Content~c1~4',
				'calling_status'=>'c2~4',
				'date'=>'c3~4',
				'from_number'=>'c1~4',
				'to_number'=>'c2~4',
				'call_by_employee'=>'c3~4',
				'description'=>'c4~12',

				'notify_via_email~'=>'c5~12',
				'notify_email_subject'=>'c6~4',
				'notify_from_email_id'=>'c7~4',
				'notify_to_email_ids'=>'c11~4',	

				'follow_up'=>'f1~8',
				'score_buttons~Score'=>'f2~2',
				'score~'=>'f23',
				'assigned_to'=>'f25~6',
				'followup_on'=>'f24~6',
				'followup_detail'=>'f26~12',
				'set_reminder'=>'f27~12',
				'reminder_at'=>'f28~2',
				'remind_via'=>'f29~2',
				'notify_to'=>'f30~4',
				'snooze_duration'=>'f31~2',
				'snooze_unit'=>'f32~2'
			]);

		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->tryLoadAny();
		
		$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
		$company_m->tryLoadAny();
		$company_number = explode(",", $company_m['mobile_no']);
		$company_number = array_combine($company_number, $company_number);

		$sub_type_array = explode(",",$config_m['sub_type']);

		$sub_type_field = $form->addField('xepan\base\DropDown','communication_sub_type')->setEmptyText("Please Select");
		$sub_type_field->setValueList(array_combine($sub_type_array,$sub_type_array));
		
		$calling_status_array = explode(",",$config_m['calling_status']);
		$calling_status_field = $form->addField('xepan\base\DropDown','calling_status')->setEmptyText('Please Select');
		$calling_status_field->setValueList(array_combine($calling_status_array,$calling_status_array));
		
		$form->addField('DateTimePicker','date')->validate('required')->set($this->app->now);

		$from_number_field = $form->addField('xepan\base\DropDown','from_number');
		$emp_phones = $this->app->employee->getPhones();
		$emp_phones = array_combine($emp_phones, $emp_phones);
		$from_number_field->setValueList(array_merge(array_filter($company_number),array_filter($emp_phones)));
		$from_number_field->select_menu_options = ['tags'=>true];
		$from_number_field->validate_values = false;

		$phones = $this->contact->getPhones();
		$to_number_field = $form->addField('xepan\base\DropDown','to_number');
		$to_number_field->setValueList(array_combine($phones,$phones));
		$to_number_field->select_menu_options = ['tags'=>true];
		$to_number_field->validate_values = false;

		$call_by_emp_field = $form->addField('xepan\hr\Employee','call_by_employee')->setCurrent();

		$form->addField('xepan\base\RichText','description');

		$notify_via_email_field = $form->addField('checkbox','notify_via_email');
		$form->addField('notify_email_subject');

		// Notify_from_email_id
		$notify_from_email_id_field = $form->addField('xepan\base\DropDown','notify_from_email_id');
		$my_email = $this->add('xepan\hr\Model_Post_Email_MyEmails');
		$notify_from_email_id_field->setModel($my_email);
		$email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');

		$contact_emails = $this->contact->getEmails();
		$form->addField('notify_to_email_ids')->set(implode(",", $contact_emails));

		$follow_up = $form->addField('Checkbox','follow_up');
		$score = $form->addField('Hidden','score')->set(0);
		$set = $form->layout->add('ButtonSet',null,'score_buttons');
		$up_btn = $set->add('Button')->set('+10')->addClass('btn');
		$down_btn = $set->add('Button')->set('-10')->addClass('btn');
		
		$followup_on = $form->addField('DateTimePicker','followup_on');
		$followup_on->js(true)->val('');
		$assigned_to = $form->addField('xepan\hr\Employee','assigned_to')->setCurrent();
		$form->addField('Text','followup_detail');

		$reminder = $form->addField('CheckBox','set_reminder');
		$reminder_at = $form->addField('DateTimePicker','reminder_at');
		$reminder_at->js(true)->val('');
		$remind_via = $form->addField('xepan\base\DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
		$notify_to = $form->addField('xepan\hr\Employee','notify_to')->setAttr(['multiple'=>'multiple'])->setCurrent();
		$form->addField('snooze_duration');
		$snooz_unit= $form->addField('xepan\base\DropDown','snooze_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');

		$notify_via_email_field->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['notify_email_subject','notify_from_email_id','notify_to_email_ids']
		],'div.col-md-2,div.col-md-4');

		$reminder->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['reminder_at','remind_via','notify_to','snooze_duration','snooze_unit']
		],'div.col-md-2,div.col-md-4');

		$follow_up->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['followup_on','assigned_to','followup_detail']
		],'div.col-md-12,div.col-md-6');
		
		if($form->isSubmitted()){

			// check validation
			if($form['follow_up'] && !$form['followup_on']){
				$form->error('followup_on','must not be empty');
			}elseif($form['followup_detail'] && (!$form['followup_on'])){
				$form->error('followup_title','must not be empty');
			}
			// reminder validation
			if($form['set_reminder']){
				if(!$form['follow_up']) $form->error('follow_up','Followup must be set to put on reminder');
				if(!$form['reminder_at']) $form->error('reminder_at','Reminder at must not be empty');
				if(!$form['remind_via']) $form->error('remind_via','Remind Via must not be empty');
				if(!$form['notify_to']) $form->error('notify_to','Notify To must not be empty');

				if($form['snooze_duration'] && !$form['snooze_unit'])
					$form->error('snooze_unit','must not be empty');

				if($form['snooze_unit'] && !$form['snooze_duration'])
					$form->error('snooze_duration','must not be empty');
			}

			if($form['notify_via_email']){
				if(!$form['notify_email_subject']) $form->error('notify_email_subject','must not be empty');
				if(!$form['notify_from_email_id']) $form->error('notify_from_email_id','must not be empty');
				if(!$form['notify_to_email_ids']) $form->error('notify_to_email_ids','must not be empty');
			}
			// end checking vaidation

			$communication = $this->add('xepan\communication\Model_Communication_Call');
			$communication->addCondition('status','Called');

			$communication['from_id'] = $form['call_by_employee'];
			$communication['to_id'] = $this->contact->id;
			$communication['sub_type'] = $form['communication_sub_type'];
			$communication['calling_status'] = $form['calling_status'];
			$communication['score'] = $form['score'];
			$communication['direction'] = 'Out';
			$communication['description'] = $form['description'];

			$communication->setSubject($form['title']);
			$communication->setBody($form['description']);
			$communication->addTo($form['to_number']);
			$employee_name = $this->add('xepan\hr\Model_Employee')
	                         ->load($form['call_by_employee'])
	                         ->get('name');
			$communication->setFrom($form['from_number'],$employee_name);
			
			if($form['notify_via_email']){
				$communication['title'] = $form['notify_email_subject'];
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($form['notify_from_email_id']?:-1);
				$communication->send(
					$send_settings,
					$form['notify_to_email_ids']
				);
			}elseif($form['description']){
				$communication['title'] = substr(strip_tags($form['description']),0,35)." ...";
			}else{
				$communication['title'] = "Called to ".$this->contact['name']." - type: ".$form['communication_sub_type']." - status: ".$form['calling_status'];
			}
			$communication->save();
			// SCORE
			if($form['score']){
				$model_point_system = $this->add('xepan\base\Model_PointSystem');
				$model_point_system['contact_id'] = $this->contact->id;
				$model_point_system['score'] = $form['score'];
				$model_point_system['remarks'] = 'Comm: Called: '.($form['communication_sub_type']?:'').' '.($form['calling_status']?:'').' '.substr(strip_tags($form['description']),0,35)." ...";
				$model_point_system->save();
			}

			// FOLLOW UP
			if($form['follow_up']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = 'Followup '. $this->contact['name_with_type'];
				$model_task['created_by_id'] = $this->app->employee->id;
				$model_task['starting_date'] = $form['followup_on'];
				$model_task['assign_to_id'] = $form['assigned_to'];
				$model_task['description'] = $form['followup_detail'];
				$model_task['related_id'] = $this->contact->id;
				if($form['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $form['reminder_at'];
					$model_task['remind_via'] = $form['remind_via'];
					$model_task['notify_to'] = $form['notify_to'];
					
					if($form['snooze_duration']){
						$model_task['snooze_duration'] = $form['snooze_duration'];
						$model_task['remind_unit'] = $form['snooze_unit'];
					}
				}
				$model_task->save();
			}

			$form->js(null,[$called_popup->js()->modal('hide'),$this->historyLister->js()->reload()])->reload()->univ()->successMessage('Communication added')->execute();
		}
			
		$up_btn->js('click',[$score->js()->val(10),$down_btn->js()->removeClass('btn-danger'),$this->js()->_selectorThis()->addClass('btn-success')]);
		$down_btn->js('click',[$score->js()->val(-10),$up_btn->js()->removeClass('btn-success'),$this->js()->_selectorThis()->addClass('btn-danger')]);
		$called_icon->js('click',[ // show event
			$called_popup->js()->modal(['backdrop'=>true,'keyboard'=>true]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();'),
			$followup_on->js()->val(''),
			$reminder_at->js()->val(''),
			$notify_from_email_id_field->js()->select2('val',''),
			$assigned_to->js()->select2('val',$this->app->employee->id),
			$remind_via->js()->select2('val',''),
			$notify_to->js()->select2('val',''),
			$snooz_unit->js()->select2('val','')
		]);		
		
	}

	function manageCallReceived($call_received_icon){

		$popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$popup->setTitle('Phone Received - Log Communication of '.$this->contact['name']);
		
		$form = $popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->addContentSpot()
			->layout([
				'communication_sub_type'=>'Call Received Content~c1~4',
				'calling_status'=>'c2~4',
				'date'=>'c3~4',
				'from_number'=>'c1~4',
				'to_number'=>'c2~4',
				'call_received_by_employee'=>'c3~4',
				'description'=>'c4~12',

				'notify_via_email~'=>'c5~12',
				'notify_email_subject'=>'c6~4',
				'notify_from_email_id'=>'c7~4',
				'notify_to_email_ids'=>'c11~4',	

				'follow_up'=>'f1~8',
				'score_buttons~Score'=>'f2~2',
				'score~'=>'f23',
				'assigned_to'=>'f25~6',
				'followup_on'=>'f24~6',
				'followup_detail'=>'f26~12',
				'set_reminder'=>'f27~12',
				'reminder_at'=>'f28~2',
				'remind_via'=>'f29~2',
				'notify_to'=>'f30~4',
				'snooze_duration'=>'f31~2',
				'snooze_unit'=>'f32~2'
			]);

		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->tryLoadAny();
		
		$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
		$company_m->tryLoadAny();
		
		$company_number = explode(",", $company_m['mobile_no']);
		$company_number = array_combine($company_number, $company_number);

		$sub_type_array = explode(",",$config_m['sub_type']);

		$emp_phones = $this->app->employee->getPhones();
		$emp_phones = array_combine($emp_phones, $emp_phones);
		$emp_phones = array_merge(array_filter($company_number),array_filter($emp_phones));

		$phones = $this->contact->getPhones();
		$contact_phones = array_combine($phones,$phones);

		// fields
		$sub_type_field = $form->addField('xepan\base\DropDown','communication_sub_type')->setEmptyText("Please Select");
		$sub_type_field->setValueList(array_combine($sub_type_array,$sub_type_array));
		
		$calling_status_array = explode(",",$config_m['calling_status']);
		$calling_status_field = $form->addField('xepan\base\DropDown','calling_status')->setEmptyText('Please Select');
		$calling_status_field->setValueList(array_combine($calling_status_array,$calling_status_array));
		
		$form->addField('DateTimePicker','date')->validate('required')->set($this->app->now);

		$from_number_field = $form->addField('xepan\base\DropDown','from_number');
		$from_number_field->setValueList($contact_phones);
		$from_number_field->select_menu_options = ['tags'=>true];
		$from_number_field->validate_values = false;

		$to_number_field = $form->addField('xepan\base\DropDown','to_number');
		$to_number_field->setValueList($emp_phones);
		$to_number_field->select_menu_options = ['tags'=>true];
		$to_number_field->validate_values = false;

		$form->addField('xepan\hr\Employee','call_received_by_employee')->setCurrent();

		$form->addField('xepan\base\RichText','description');

		$notify_via_email_field = $form->addField('checkbox','notify_via_email');
		$form->addField('notify_email_subject');

		// Notify_from_email_id
		$notify_from_email_id_field = $form->addField('xepan\base\DropDown','notify_from_email_id');
		$my_email = $this->add('xepan\hr\Model_Post_Email_MyEmails');
		$notify_from_email_id_field->setModel($my_email);
		$email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');

		$contact_emails = $this->contact->getEmails();
		$form->addField('notify_to_email_ids')->set(implode(",", $contact_emails));

		$follow_up = $form->addField('Checkbox','follow_up');
		$score = $form->addField('Hidden','score')->set(0);
		$set = $form->layout->add('ButtonSet',null,'score_buttons');
		$up_btn = $set->add('Button')->set('+10')->addClass('btn');
		$down_btn = $set->add('Button')->set('-10')->addClass('btn');
		
		$followup_on = $form->addField('DateTimePicker','followup_on');
		$followup_on->js(true)->val('');
		$assigned_to = $form->addField('xepan\hr\Employee','assigned_to')->setCurrent();
		$form->addField('Text','followup_detail');

		$reminder = $form->addField('CheckBox','set_reminder');
		$reminder_at = $form->addField('DateTimePicker','reminder_at');
		$reminder_at->js(true)->val('');
		$remind_via = $form->addField('xepan\base\DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
		$notify_to = $form->addField('xepan\hr\Employee','notify_to')->setAttr(['multiple'=>'multiple'])->setCurrent();
		$form->addField('snooze_duration');
		$snooz_unit= $form->addField('xepan\base\DropDown','snooze_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');

		$notify_via_email_field->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['notify_email_subject','notify_from_email_id','notify_to_email_ids']
		],'div.col-md-2,div.col-md-4');

		$reminder->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['reminder_at','remind_via','notify_to','snooze_duration','snooze_unit']
		],'div.col-md-2,div.col-md-4');

		$follow_up->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['followup_on','assigned_to','followup_detail']
		],'div.col-md-12,div.col-md-6');

		if($form->isSubmitted()){

			// check validation
			if($form['follow_up'] && !$form['followup_on']){
				$form->error('followup_on','must not be empty');
			}elseif($form['followup_detail'] && (!$form['followup_on'])){
				$form->error('followup_title','must not be empty');
			}
			// reminder validation
			if($form['set_reminder']){
				if(!$form['follow_up']) $form->error('follow_up','Followup must be set to put on reminder');
				if(!$form['reminder_at']) $form->error('reminder_at','Reminder at must not be empty');
				if(!$form['remind_via']) $form->error('remind_via','Remind Via must not be empty');
				if(!$form['notify_to']) $form->error('notify_to','Notify To must not be empty');

				if($form['snooze_duration'] && !$form['snooze_unit'])
					$form->error('snooze_unit','must not be empty');

				if($form['snooze_unit'] && !$form['snooze_duration'])
					$form->error('snooze_duration','must not be empty');
			}

			if($form['notify_via_email']){
				if(!$form['notify_email_subject']) $form->error('notify_email_subject','must not be empty');
				if(!$form['notify_from_email_id']) $form->error('notify_from_email_id','must not be empty');
				if(!$form['notify_to_email_ids']) $form->error('notify_to_email_ids','must not be empty');
			}
			// end checking vaidation

			$communication = $this->add('xepan\communication\Model_Communication_Call');
			$communication->addCondition('status','Received');

			$communication['from_id'] = $this->contact->id;
			$communication['to_id'] = $form['call_received_by_employee'];
			$communication['sub_type'] = $form['communication_sub_type'];
			$communication['calling_status'] = $form['calling_status'];
			$communication['score'] = $form['score'];
			$communication['direction'] = 'In';
			$communication['description'] = $form['description'];

			$communication->setSubject($form['title']);
			$communication->setBody($form['description']);
			$communication->addTo($form['from_number']);

			$employee_name = $this->add('xepan\hr\Model_Employee')
	                         ->load($form['call_received_by_employee'])
	                         ->get('name');
			$communication->setFrom($form['to_number'],$employee_name);
			
			if($form['notify_via_email']){
				$communication['title'] = $form['notify_email_subject'];
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($form['notify_from_email_id']?:-1);
				$communication->send(
					$send_settings,
					$form['notify_to_email_ids']
				);
			}elseif($form['description']){
				$communication['title'] = substr(strip_tags($form['description']),0,35)." ...";
			}else{
				$communication['title'] = "Called to ".$this->contact['name']." - type: ".$form['communication_sub_type']." - status: ".$form['calling_status'];
			}
			$communication->save();
			// SCORE
			if($form['score']){
				$model_point_system = $this->add('xepan\base\Model_PointSystem');
				$model_point_system['contact_id'] = $this->contact->id;
				$model_point_system['score'] = $form['score'];
				$model_point_system['remarks'] = 'Comm: Called: '.($form['communication_sub_type']?:'').' '.($form['calling_status']?:'').' '.substr(strip_tags($form['description']),0,35)." ...";
				$model_point_system->save();
			}

			// FOLLOW UP
			if($form['follow_up']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = 'Followup '. $this->contact['name_with_type'];
				$model_task['created_by_id'] = $this->app->employee->id;
				$model_task['starting_date'] = $form['followup_on'];
				$model_task['assign_to_id'] = $form['assigned_to'];
				$model_task['description'] = $form['followup_detail'];
				$model_task['related_id'] = $this->contact->id;
				if($form['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $form['reminder_at'];
					$model_task['remind_via'] = $form['remind_via'];
					$model_task['notify_to'] = $form['notify_to'];
					
					if($form['snooze_duration']){
						$model_task['snooze_duration'] = $form['snooze_duration'];
						$model_task['remind_unit'] = $form['snooze_unit'];
					}
				}
				$model_task->save();
			}

			$form->js(null,[$popup->js()->modal('hide'),$this->historyLister->js()->reload()])->reload()->univ()->successMessage('Communication added')->execute();
		}
			
		$up_btn->js('click',[$score->js()->val(10),$down_btn->js()->removeClass('btn-danger'),$this->js()->_selectorThis()->addClass('btn-success')]);
		$down_btn->js('click',[$score->js()->val(-10),$up_btn->js()->removeClass('btn-success'),$this->js()->_selectorThis()->addClass('btn-danger')]);
		$call_received_icon->js('click',[ // show event
			$popup->js()->modal(['backdrop'=>true,'keyboard'=>true]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();'),
			$followup_on->js()->val(''),
			$reminder_at->js()->val(''),
			$notify_from_email_id_field->js()->select2('val',''),
			$assigned_to->js()->select2('val',$this->app->employee->id),
			$remind_via->js()->select2('val',''),
			$notify_to->js()->select2('val',''),
			$snooz_unit->js()->select2('val','')
		]);		
		
	}

	function manageMeeting($meeting_icon){

		$popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$popup->setTitle('Meeting/Personal - Log Communication with '.$this->contact['name']);
		
		$form = $popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->addContentSpot()
			->layout([
				'communication_sub_type'=>'Meeting/Personal Content~c1~3',
				'calling_status~status'=>'c2~3',
				'date'=>'c3~3',
				'employee'=>'c4~3',
				'description'=>'c5~12',

				'notify_via_email~'=>'c6~12',
				'notify_email_subject'=>'c7~4',
				'notify_from_email_id'=>'c8~4',
				'notify_to_email_ids'=>'c11~4',	

				'follow_up'=>'f1~8',
				'score_buttons~Score'=>'f2~2',
				'score~'=>'f23',
				'assigned_to'=>'f25~6',
				'followup_on'=>'f24~6',
				'followup_detail'=>'f26~12',
				'set_reminder'=>'f27~12',
				'reminder_at'=>'f28~2',
				'remind_via'=>'f29~2',
				'notify_to'=>'f30~4',
				'snooze_duration'=>'f31~2',
				'snooze_unit'=>'f32~2'
			]);

		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->tryLoadAny();
		$sub_type_array = explode(",",$config_m['sub_type']);

		// fields
		$sub_type_field = $form->addField('xepan\base\DropDown','communication_sub_type')->setEmptyText("Please Select");
		$sub_type_field->setValueList(array_combine($sub_type_array,$sub_type_array));
		
		$calling_status_array = explode(",",$config_m['calling_status']);
		$calling_status_field = $form->addField('xepan\base\DropDown','calling_status')->setEmptyText('Please Select');
		$calling_status_field->setValueList(array_combine($calling_status_array,$calling_status_array));
		
		$form->addField('xepan\hr\Employee','employee')->setCurrent();

		$form->addField('DateTimePicker','date')->validate('required')->set($this->app->now);

		$form->addField('xepan\base\RichText','description');

		$notify_via_email_field = $form->addField('checkbox','notify_via_email');
		$form->addField('notify_email_subject');

		// Notify_from_email_id
		$notify_from_email_id_field = $form->addField('xepan\hr\EmployeeAllowedEmail','notify_from_email_id');
		$contact_emails = $this->contact->getEmails();
		$form->addField('notify_to_email_ids')->set(implode(",", $contact_emails));

		$follow_up = $form->addField('Checkbox','follow_up');
		$score = $form->addField('Hidden','score')->set(0);
		$set = $form->layout->add('ButtonSet',null,'score_buttons');
		$up_btn = $set->add('Button')->set('+10')->addClass('btn');
		$down_btn = $set->add('Button')->set('-10')->addClass('btn');
		
		$followup_on = $form->addField('DateTimePicker','followup_on');
		$followup_on->js(true)->val('');
		$assigned_to = $form->addField('xepan\hr\Employee','assigned_to')->setCurrent();
		$form->addField('Text','followup_detail');

		$reminder = $form->addField('CheckBox','set_reminder');
		$reminder_at = $form->addField('DateTimePicker','reminder_at');
		$reminder_at->js(true)->val('');
		$remind_via = $form->addField('xepan\base\DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
		$notify_to = $form->addField('xepan\hr\Employee','notify_to')->setAttr(['multiple'=>'multiple'])->setCurrent();
		$form->addField('snooze_duration');
		$snooz_unit= $form->addField('xepan\base\DropDown','snooze_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');

		$notify_via_email_field->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['notify_email_subject','notify_from_email_id','notify_to_email_ids']
		],'div.col-md-2,div.col-md-4');

		$reminder->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['reminder_at','remind_via','notify_to','snooze_duration','snooze_unit']
		],'div.col-md-2,div.col-md-4');

		$follow_up->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['followup_on','assigned_to','followup_detail']
		],'div.col-md-12,div.col-md-6');
		
		if($form->isSubmitted()){
			
			if($form['notify_via_email']){
				if(!$form['notify_email_subject']) $form->error('notify_email_subject','must not be empty');
				if(!$form['notify_from_email_id']) $form->error('notify_from_email_id','must not be empty');
				if(!$form['notify_to_email_ids']) $form->error('notify_to_email_ids','must not be empty');
			}

			// check validation
			if($form['follow_up'] && !$form['followup_on']){
				$form->error('followup_on','must not be empty');
			}elseif($form['followup_detail'] && (!$form['followup_on'])){
				$form->error('followup_title','must not be empty');
			}

			// reminder validation
			if($form['set_reminder']){
				if(!$form['follow_up']) $form->error('follow_up','Followup must be set to put on reminder');
				if(!$form['reminder_at']) $form->error('reminder_at','Reminder at must not be empty');
				if(!$form['remind_via']) $form->error('remind_via','Remind Via must not be empty');
				if(!$form['notify_to']) $form->error('notify_to','Notify To must not be empty');

				if($form['snooze_duration'] && !$form['snooze_unit'])
					$form->error('snooze_unit','must not be empty');

				if($form['snooze_unit'] && !$form['snooze_duration'])
					$form->error('snooze_duration','must not be empty');
			}

			// end checking vaidation

			$communication = $this->add('xepan\communication\Model_Communication_Personal');
			$communication->addCondition('status','Personal');

			$communication['to_id'] = $this->contact->id;
			$communication['from_id'] = $form['employee'];
			$communication['direction'] = "Out";

			$communication['sub_type'] = $form['communication_sub_type'];
			$communication['calling_status'] = $form['calling_status'];
			$communication['score'] = $form['score'];
			$communication['description'] = $form['description'];
			
			$communication->addTo($this->contact->id,$this->contact['name']);
			$employee_name = $this->add('xepan\hr\Model_Employee')
	                         ->load($form['employee'])
	                         ->get('name');
			$communication->setFrom($form['employee'],$employee_name);
			$communication->setBody($form['description']);

			if($form['notify_via_email']){
				$communication['title'] = $form['notify_email_subject'];

				$communication->setSubject($form['notify_email_subject']);
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($form['notify_from_email_id']?:-1);
				$communication->send(
					$send_settings,
					$form['notify_to_email_ids']
				);
			}elseif($form['description']){
				$communication['title'] = substr(strip_tags($form['description']),0,35)." ...";

			}else{
				$communication['title'] = "Called to ".$this->contact['name']." - type: ".$form['communication_sub_type']." - status: ".$form['calling_status'];
			}

			$communication->setSubject($communication['title']);
			$communication->save();

			// SCORE
			if($form['score']){
				$model_point_system = $this->add('xepan\base\Model_PointSystem');
				$model_point_system['contact_id'] = $this->contact->id;
				$model_point_system['score'] = $form['score'];
				$model_point_system['remarks'] = 'Comm: Called: '.($form['communication_sub_type']?:'').' '.($form['calling_status']?:'').' '.substr(strip_tags($form['description']),0,35)." ...";
				$model_point_system->save();
			}

			// FOLLOW UP
			if($form['follow_up']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = 'Followup '. $this->contact['name_with_type'];
				$model_task['created_by_id'] = $this->app->employee->id;
				$model_task['starting_date'] = $form['followup_on'];
				$model_task['assign_to_id'] = $form['assigned_to'];
				$model_task['description'] = $form['followup_detail'];
				$model_task['related_id'] = $this->contact->id;
				if($form['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $form['reminder_at'];
					$model_task['remind_via'] = $form['remind_via'];
					$model_task['notify_to'] = $form['notify_to'];
					
					if($form['snooze_duration']){
						$model_task['snooze_duration'] = $form['snooze_duration'];
						$model_task['remind_unit'] = $form['snooze_unit'];
					}
				}
				$model_task->save();
			}

			$form->js(null,[$popup->js()->modal('hide'),$this->historyLister->js()->reload()])->reload()->univ()->successMessage('Communication added')->execute();
		}
			
		$up_btn->js('click',[$score->js()->val(10),$down_btn->js()->removeClass('btn-danger'),$this->js()->_selectorThis()->addClass('btn-success')]);
		$down_btn->js('click',[$score->js()->val(-10),$up_btn->js()->removeClass('btn-success'),$this->js()->_selectorThis()->addClass('btn-danger')]);
		$meeting_icon->js('click',[ // show event
			$popup->js()->modal(['backdrop'=>true,'keyboard'=>true]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();'),
			$followup_on->js()->val(''),
			$reminder_at->js()->val(''),
			$notify_from_email_id_field->js()->select2('val',''),
			$assigned_to->js()->select2('val',$this->app->employee->id),
			$remind_via->js()->select2('val',''),
			$notify_to->js()->select2('val',''),
			$snooz_unit->js()->select2('val','')
		]);		
	}

	function manageComment($comment_icon){

		$popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$popup->setTitle('Comment - Log Communication of '.$this->contact['name']);
		
		$form = $popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->addContentSpot()
			->layout([
				'communication_sub_type'=>'Comment Content~c1~3',
				'calling_status'=>'c2~3',
				'date'=>'c3~3',
				'employee'=>'c4~3',
				'description'=>'c5~12',

				'notify_via_email~'=>'c6~12',
				'notify_email_subject'=>'c7~4',
				'notify_from_email_id'=>'c8~4',
				'notify_to_email_ids'=>'c11~4',	

				'follow_up'=>'f1~8',
				'score_buttons~Score'=>'f2~2',
				'score~'=>'f23',
				'assigned_to'=>'f25~6',
				'followup_on'=>'f24~6',
				'followup_detail'=>'f26~12',
				'set_reminder'=>'f27~12',
				'reminder_at'=>'f28~2',
				'remind_via'=>'f29~2',
				'notify_to'=>'f30~4',
				'snooze_duration'=>'f31~2',
				'snooze_unit'=>'f32~2'
			]);

		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->tryLoadAny();

		$sub_type_array = explode(",",$config_m['sub_type']);
		$sub_type_field = $form->addField('xepan\base\DropDown','communication_sub_type')->setEmptyText("Please Select");
		$sub_type_field->setValueList(array_combine($sub_type_array,$sub_type_array));
			
		$calling_status_array = explode(",",$config_m['calling_status']);
		$calling_status_field = $form->addField('xepan\base\DropDown','calling_status')->setEmptyText('Please Select');
		$calling_status_field->setValueList(array_combine($calling_status_array,$calling_status_array));
		
		$form->addField('DateTimePicker','date')->validate('required')->set($this->app->now);

		$emp_field = $form->addField('xepan\hr\Employee','employee')->setCurrent();

		$form->addField('xepan\base\RichText','description');

		$notify_via_email_field = $form->addField('checkbox','notify_via_email');
		$form->addField('notify_email_subject');

		// Notify_from_email_id

		// $notify_from_email_id_field = $form->addField('xepan\base\DropDown','notify_from_email_id');
		// $my_email = $this->add('xepan\hr\Model_Post_Email_MyEmails');
		// $notify_from_email_id_field->setModel($my_email);
		// $email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');
		
		$notify_from_email_id_field = $form->addField('xepan\hr\EmployeeAllowedEmail','notify_from_email_id');

		$contact_emails = $this->contact->getEmails();
		$form->addField('notify_to_email_ids')->set(implode(",", $contact_emails));

		$follow_up = $form->addField('Checkbox','follow_up');
		$score = $form->addField('Hidden','score')->set(0);
		$set = $form->layout->add('ButtonSet',null,'score_buttons');
		$up_btn = $set->add('Button')->set('+10')->addClass('btn');
		$down_btn = $set->add('Button')->set('-10')->addClass('btn');
		
		$followup_on = $form->addField('DateTimePicker','followup_on');
		$followup_on->js(true)->val('');
		$assigned_to = $form->addField('xepan\hr\Employee','assigned_to')->setCurrent();
		$form->addField('Text','followup_detail');

		$reminder = $form->addField('CheckBox','set_reminder');
		$reminder_at = $form->addField('DateTimePicker','reminder_at');
		$reminder_at->js(true)->val('');
		$remind_via = $form->addField('xepan\base\DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
		$notify_to = $form->addField('xepan\hr\Employee','notify_to')->setAttr(['multiple'=>'multiple'])->setCurrent();
		$form->addField('snooze_duration');
		$snooz_unit= $form->addField('xepan\base\DropDown','snooze_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');

		$notify_via_email_field->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['notify_email_subject','notify_from_email_id','notify_to_email_ids']
		],'div.col-md-2,div.col-md-4');

		$reminder->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['reminder_at','remind_via','notify_to','snooze_duration','snooze_unit']
		],'div.col-md-2,div.col-md-4');

		$follow_up->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['followup_on','assigned_to','followup_detail']
		],'div.col-md-12,div.col-md-6');
		
		if($form->isSubmitted()){

			// check validation
			if($form['follow_up'] && !$form['followup_on']){
				$form->error('followup_on','Followup Date must not be empty');
			}elseif($form['followup_detail'] && (!$form['followup_on'])){
				$form->error('followup_title','must not be empty');
			}

			// reminder validation
			if($form['set_reminder']){
				if(!$form['follow_up']) $form->error('follow_up','Followup must be set to put on reminder');
				if(!$form['reminder_at']) $form->error('reminder_at','Reminder at must not be empty');
				if(!$form['remind_via']) $form->error('remind_via','Remind Via must not be empty');
				if(!$form['notify_to']) $form->error('notify_to','Notify To must not be empty');

				if($form['snooze_duration'] && !$form['snooze_unit'])
					$form->error('snooze_unit','must not be empty');

				if($form['snooze_unit'] && !$form['snooze_duration'])
					$form->error('snooze_duration','must not be empty');
			}

			if($form['notify_via_email']){
				if(!$form['notify_email_subject']) $form->error('notify_email_subject','must not be empty');
				if(!$form['notify_from_email_id']) $form->error('notify_from_email_id','must not be empty');
				if(!$form['notify_to_email_ids']) $form->error('notify_to_email_ids','must not be empty');
			}
			// end checking vaidation

			$communication = $this->add('xepan\communication\Model_Communication_Comment');
			$communication->addCondition('status','Commented');

			$communication['from_id'] = $form['employee'];
			$communication['to_id'] = $this->contact->id;
			$communication['sub_type'] = $form['communication_sub_type'];
			$communication['calling_status'] = $form['calling_status'];
			$communication['score'] = $form['score'];
			$communication['direction'] = 'Out';
			$communication['description'] = $form['description'];


			$communication->setBody($form['description']);
			$communication->addTo($this->contact->id,$this->contact['name']);
			$employee_name = $this->add('xepan\hr\Model_Employee')
	                         ->load($form['employee'])
	                         ->get('name');
			$communication->setFrom($form['employee'],$employee_name);
			
			if($form['notify_via_email']){
				$communication['title'] = $form['notify_email_subject'];
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($form['notify_from_email_id']?:-1);
				$communication->send(
					$send_settings,
					$form['notify_to_email_ids']
				);
			}elseif($form['description']){
				$communication['title'] = substr(strip_tags($form['description']),0,35)." ...";
			}else{
				$communication['title'] = "Called to ".$this->contact['name']." - type: ".$form['communication_sub_type']." - status: ".$form['calling_status'];
			}

			$communication['created_at'] = $form['date'];
			$communication->setSubject($communication['title']);
			$communication->save();
			// SCORE
			if($form['score']){
				$model_point_system = $this->add('xepan\base\Model_PointSystem');
				$model_point_system['contact_id'] = $this->contact->id;
				$model_point_system['score'] = $form['score'];
				$model_point_system['remarks'] = 'Comm: Called: '.($form['communication_sub_type']?:'').' '.($form['calling_status']?:'').' '.substr(strip_tags($form['description']),0,35)." ...";
				$model_point_system->save();
			}

			// FOLLOW UP
			if($form['follow_up']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = 'Followup '. $this->contact['name_with_type'];
				$model_task['created_by_id'] = $this->app->employee->id;
				$model_task['starting_date'] = $form['followup_on'];
				$model_task['assign_to_id'] = $form['assigned_to'];
				$model_task['description'] = $form['followup_detail'];
				$model_task['related_id'] = $this->contact->id;
				if($form['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $form['reminder_at'];
					$model_task['remind_via'] = $form['remind_via'];
					$model_task['notify_to'] = $form['notify_to'];
					
					if($form['snooze_duration']){
						$model_task['snooze_duration'] = $form['snooze_duration'];
						$model_task['remind_unit'] = $form['snooze_unit'];
					}
				}
				$model_task->save();
			}

			$form->js(null,[$popup->js()->modal('hide'),$this->historyLister->js()->reload()])->reload()->univ()->successMessage('Communication added')->execute();
		}
			
		$up_btn->js('click',[$score->js()->val(10),$down_btn->js()->removeClass('btn-danger'),$this->js()->_selectorThis()->addClass('btn-success')]);
		$down_btn->js('click',[$score->js()->val(-10),$up_btn->js()->removeClass('btn-success'),$this->js()->_selectorThis()->addClass('btn-danger')]);
		$comment_icon->js('click',[ // show event
			$popup->js()->modal(['backdrop'=>true,'keyboard'=>true]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();'),
			$followup_on->js()->val(''),
			$reminder_at->js()->val(''),
			$notify_from_email_id_field->js()->select2('val',''),
			$assigned_to->js()->select2('val',$this->app->employee->id),
			$remind_via->js()->select2('val',''),
			$notify_to->js()->select2('val',''),
			$snooz_unit->js()->select2('val','')
		]);		
		
	}


	function manageSms($comment_icon){
		$popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$popup->setTitle('SMS - Log Communication of '.$this->contact['name']);
		
		$form = $popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->addContentSpot()
			->layout([
				'to_number'=>'c1~6',
				'sms_settings'=>'c2~6',
				'sms'=>'c4~12',
			]);

		$phones = $this->contact->getPhones();
		$to_number_field = $form->addField('xepan\base\DropDown','to_number');
		$to_number_field->setAttr('multiple','multiple');
		$to_number_field->setValueList(array_combine($phones,$phones));
		$to_number_field->select_menu_options = ['tags'=>true];
		$to_number_field->validate_values = false;

		$form->addField('DropDown','sms_settings')->validate('required')->setModel('xepan\communication\Model_Communication_SMSSetting');

		$form->addField('Text','sms');
		
		if($form->isSubmitted()){

			
			// end checking vaidation

			$communication = $this->add('xepan\communication\Model_Communication_SMS');
			$communication->addCondition('status','Commented');

			$communication['from_id'] = $this->app->employee->id;;
			$communication['to_id'] = $this->contact->id;
			$communication['direction'] = 'Out';
			$communication['description'] = $form['sms'];
			
			foreach (explode(",", $form['to_number']) as $nos) {
				$communication->addTo($nos,$this->contact['name']);
				
			}

			$communication->setFrom($this->app->employee->id,$this->app->employee['name']);
			
			$communication['title'] = 'SMS: '.substr(strip_tags($form['sms']),0,35)." ...";
			

			$communication['created_at'] = $this->app->now;
			$communication['communication_channel_id'] = $form['sms_settings'];
			// throw new \Exception(print_r($communication['to_raw'],true), 1);
			$reply = $communication->send($form['sms_settings']);			

			$form->js(null,[$popup->js()->modal('hide'),$this->historyLister->js()->reload()])->reload()->univ()->successMessage('SMS Gateway reply: '.implode("<br/>", $reply))->execute();
		}	

		$comment_icon->js('click',[ // show event
			$popup->js()->modal(['backdrop'=>true,'keyboard'=>true]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();')
		]);
		
	}

	function addFilter(){
		$form = $this->add('Form',null,'filter');

		$form->add('xepan\base\Controller_FLC')
			->makePanelCollepsible()
			->closeOtherPanels()
			->addContentSpot()
			->layout([
					'date_range'=>'Filter~c1~6~closed',
					'related_contact'=>'c2~6',
					'communication_type'=>'c3~6',
					'direction'=>'c4~2',
					'search'=>'c5~4',
					'FormButtons~<br/>'=>'c6~12'
				]);

	    $fld_date_range = $form->addField('DateRangePicker','date_range')
            // ->setStartDate('2016-04-07')
            // ->setEndDate('2016-04-30')
            ->showTimer(15)
            ->getBackDatesSet() // or set to false to remove
            // ->getFutureDatesSet() // or skip to not include
            ;
        $fld_contact = $form->addField('xepan\base\Contact','related_contact');
		$fld_contact->includeAll();

		$fld_type = $form->addField('xepan\base\DropDown','communication_type');
		$fld_type->setValueList(['Email'=>'Email','Called'=>'Called','Received'=>'Received','TeleMarketing'=>'TeleMarketing','Personal'=>'Personal','Comment'=>'Comment','SMS'=>'SMS','Newsletter'=>'Newsletter','Support'=>'Support']);
		$fld_type->setAttr(['multiple'=>'multiple']);

		$fld_direction = $form->addField('xepan\base\DropDown','direction');
		$fld_direction->setValueList(['In'=>'In','Out'=>'Out']);
		$fld_direction->setEmptyText('Please Select');

		$form->addField('search');
		$form->addSubmit('Filter')->addClass('btn btn-primary btn-block');
		
		if($form->isSubmitted()){
			$this->historyLister->js()->reload([
					'communication_filter'=>1,
					'start_date'=>$fld_date_range->getStartDate(),
					'end_date'=>$fld_date_range->getEndDate(),
					'related_contact_id'=>$form['related_contact'],
					'communication_type'=>$form['communication_type'],
					'direction'=>$form['direction'],
					'search_string'=>$form['search']
				])->execute();
		}		
	}

	function recursiveRender(){
		if($this->showCommunicationHistory) $this->addCommunicationHistory();
		if($this->showAddCommunications) $this->addTopBar();
		parent::recursiveRender();
	} 

	function myTemplate(){
		$template='
			<div id="{$_name}" class="{$class}">
				<div class="communication-top-bar">
					<div class="row main-box" style="padding-top:15px;">
						<div class="col-md-7 col-lg-7 col-sm-12 col-xs-12">
							{$filter}
						</div>
						<div class="col-md-5 col-lg-5 col-sm-12 col-xs-12">
							<div class="btn-group btn-group-justified" role="group" aria-label="Communication Action">
								{$icons}
							</div>
						</div>
					</div>
				</div>
				{$Content}
			</div>
		';
		return $template;
	}
}