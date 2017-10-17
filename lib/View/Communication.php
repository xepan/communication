<?php

namespace xepan\communication;



class View_Communication extends \View {
	
	public $allowed_channels = ['email','call','call_sent','call_received','meeting','personal','comment'];

	public $channel_email=true;
	public $channel_call_sent=true;
	public $channel_call_received=true;
	public $channel_meeting=true;
	public $channel_comment=true;

	public $showCommunicationHistory = true;
	public $showAddCommunications = true;

	public $contact=null;

	public $is_editing = false;

	function init(){
		parent::init();
		$this->template->loadTemplateFromString($this->myTemplate());
	}

	function filter($from_ids=null,$to_ids=null,$related_document_ids=null,$created_by_ids=null){

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
		if($this->channel_email) {
			$email_icon = $this->add('Icon',null,'icons')->set('fa fa-envelope fa-3x')->addStyle('cursor:hand');
			$this->manageEmail($email_icon);
		}

		if($this->channel_call_sent){
			$called_icon = $this->add('Icon',null,'icons')->set('fa fa-upload fa-3x');
			$this->manageCalled($called_icon);
		}
		if($this->channel_call_received) $this->add('Icon',null,'icons')->set('fa fa-download fa-3x');
		if($this->channel_meeting) $this->add('Icon',null,'icons')->set('fa fa-users');
		if($this->channel_comment) $this->add('Icon',null,'icons')->set('fa fa-users');
	}

	function addCommunicationHistory(){
		$communication = $this->model;
		$lister=$this->add('xepan\communication\View_Lister_NewCommunication',['contact_id'=>$this->contact->id],null,null);
		if($_GET['comm_type']){
			$communication->addCondition('communication_type',explode(",", $_GET['comm_type']));
		}

		if($search = $this->app->stickyGET('search')){
			$communication->addExpression('Relevance')->set('MATCH(title,description,communication_type) AGAINST ("'.$search.'")');
			$communication->addCondition('Relevance','>',0);
 			$communication->setOrder('Relevance','Desc');
		}

		$lister->setModel($communication)->setOrder(['created_at desc','id desc']);
		$p = $lister->add('Paginator',null,'Paginator');
		$p->setRowsPerPage(10);

		// $grid = $this->add('xepan\base\Grid');
		// $grid->setModel($this->model);
		// $grid->addPaginator(100);
	}

	function manageEmail($email_icon, $edit_communication= null){
		$email_popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$email_popup->setTitle('Send New Email');
		$default_to_ids=implode(",",$this->contact->getEmails());
		$form = $email_popup->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelsCoppalsible()
			->closeOtherPanels()
			->layout([
				'subject'=>'Email Content|danger~c1~8',
				'from_email_id'=>'c3~4',
				'body'=>'c4~12',
				'to'=>'Send To (' .$default_to_ids.  ')~c1~8~closed',
				'cc'=>'c3~5',
				'cc_me'=>'c35~1',
				'bcc'=>'c4~5',
				'bcc_me'=>'c5~1',
				'task_title'=>'Followup/Score~c1~8~closed',
				'score_buttons~Score'=>'c2~2',
				'score~'=>'c3',
				'followup_on'=>'c4~6',
				'assigned_to'=>'c5~6',
				'followup_detail'=>'c6~12',
				'set_reminder'=>'c7~12',
				'reminder_at'=>'c8~2',
				'remind_via'=>'c9~2',
				'notify_to'=>'c10~4',
				'snooze_duration'=>'c11~2',
				'snooze_unit~'=>'c12~2',

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
		
		$form->addField('xepan\hr\EmployeeAllowedEmail','from_email_id');
		$followup_on = $form->addField('DateTimePicker','followup_on');
		$form->addField('xepan\hr\Employee','assigned_to')->setCurrent();;
		$form->addField('Text','followup_detail');

		$follow_title = $form->addField('task_title');
		$score = $form->addField('Hidden','score')->set(0);
		$set = $form->layout->add('ButtonSet',null,'score_buttons');
		$up_btn = $set->add('Button')->set('+10')->addClass('btn');
		$down_btn = $set->add('Button')->set('-10')->addClass('btn');
		

		$reminder = $form->addField('CheckBox','set_reminder');
		$reminder_at = $form->addField('DateTimePicker','reminder_at');
		$form->addField('DropDown','remind_via')->setValueList(['Email'=>'Email','SMS'=>'SMS','Notification'=>'Notification'])->setAttr(['multiple'=>'multiple'])->setEmptyText('Please Select A Value');
		$form->addField('xepan\hr\Employee','notify_to')->setAttr(['multiple'=>'multiple'])->setCurrent();
		$form->addField('snooze_duration');
		$form->addField('DropDown','snooze_unit')->setValueList(['Minutes'=>'Minutes','hours'=>'Hours','day'=>'Days'])->setEmptyText('Please select a value');

		$reminder->js(true)->univ()->bindConditionalShow([
			''=>[],
			'*'=>['reminder_at','remind_via','notify_to','snooze_duration','snooze_unit']
		],'div.col-md-2,div.col-md-4');

		if($form->isSubmitted()){

			// check validation
			if($form['task_title'] && !$form['followup_on']){
				$form->error('followup_on','must not be empty');
			}elseif(!$form['task_title'] && $form['followup_on']){
				$form->error('task_title','must not be empty');
			}elseif($form['followup_detail'] && (!$form['followup_on'] OR !$form['task_title'])){
				$form->error('task_title','must not be empty');
			}
			// reminder validation
			if($form['set_reminder']){
				if(!$form['reminder_at']) $form->error('reminder_at','must not be empty');
				if(!$form['remind_via']) $form->error('remind_via','must not be empty');
				if(!$form['notify_to']) $form->error('notify_to','must not be empty');

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
				$model_point_system->save();
			}

			// FOLLOW UP
			if($form['task_title']){
				$model_task = $this->add('xepan\projects\Model_Task');
				$model_task['type'] = 'Followup';
				$model_task['task_name'] = $form['task_title'];
				$model_task['created_by_id'] = $form->app->employee->id;
				$model_task['starting_date'] = $form['followup_on'];
				$model_task['assign_to_id'] = $form['assigned_to'];
				$model_task['description'] = $form['followup_detail'];
				$model_task['related_id'] = $this->contact->id;
				if($form['set_reminder']){
					$model_task['set_reminder'] = true;
					$model_task['reminder_time'] = $form['reminder_at'];
					$model_task['remind_via'] = $this['remind_via'];
					$model_task['notify_to'] = $this['notify_to'];
					
					if($form['snooze_duration']){
						$model_task['snooze_duration'] = $form['snooze_duration'];
						$model_task['remind_unit'] = $this['snooze_unit'];
					}
				}
				$model_task->save();
			}

			$form->js()->reload()->univ()->successMessage('Email Sent')->execute();

		}

		// JAVASCRIP SECTION
		$subject->js('change',"\$('#$follow_title->name').val('".$this->contact['name']." : ' + \$('#$subject->name').val())");
		$up_btn->js('click',[$score->js()->val(10),$down_btn->js()->removeClass('btn-danger'),$this->js()->_selectorThis()->addClass('btn-success')]);
		$down_btn->js('click',[$score->js()->val(-10),$up_btn->js()->removeClass('btn-success'),$this->js()->_selectorThis()->addClass('btn-danger')]);
		$email_icon->js('click',[ // show event
			$email_popup->js()->modal(['backdrop'=>true,'keyboard'=>true]),
			$form->js(null,'$("#'.$form->name.'").find("form")[0].reset();'),
			$followup_on->js()->val(''),
			$reminder_at->js()->val(''),
		]);
	}

	function manageCalled($called_icon){
		$called_popup = $this->add('xepan\base\View_ModelPopup')->addClass('modal-full');
		$called_popup->setTitle('Phone Called - Log Communication');

		$called_icon->js('click',$called_popup->js()->modal(['backdrop'=>true,'keyboard'=>true]));
	}

	function recursiveRender(){
		if($this->showAddCommunications) $this->addTopBar();
		if($this->showCommunicationHistory) $this->addCommunicationHistory();
		parent::recursiveRender();
	} 

	function myTemplate(){
		$template='
			<div id="{$_name}" class="{$class}">
				<div class="top-bar">
					<div class="row">
						<div class="col-md-8">
						</div>
						<div class="col-md-4">
							{$icons}
						</div>
					</div>
				</div>
				{$Content}
			</div>
		';
		return $template;
	}
}