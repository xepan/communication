<?php

namespace xepan\communication;

class View_ComposeEmailPopup extends \View{
	public $subject="";
	public $message="";
	public $communication_id;
	public $mode;
	function init(){
		parent::init();
		$this->addClass('compose-email-view-popup');	
		$this->app->stickyGET('communication_id');
		$this->app->stickyGET('mode');
		// if($_GET['communication_id'])
		// 	throw new \Exception($this->communication_id, 1);

		$my_emails = $this->add('xepan\hr\Model_Post_Email_MyEmails');

		$action= 'add';
		$replay_model=$this->add('xepan\communication\Model_Communication_Email');
		if($this->communication_id)
			$replay_model->load($this->communication_id);
		if($this->mode == 'DraftMessage')
			$my_emails->addCondition('post_email',$replay_model['from_raw']['email']);
		
		$form = $this->add('Form');;
		$form->setLayout(['view/composeemail-view']);

		


		$mymail = $form->addField('Dropdown','email_username')->setEmptyText('Please Select From Email')->validate('required');
		$mymail->setModel($my_emails);		

		$to_field = $form->addField('xepan\base\DropDown','email_to');
		$to_field->validate_values = false;
		$cc_field = $form->addField('xepan\base\Dropdown','email_cc');
		$cc_field->validate_values = false;
		$bcc_field = $form->addField('xepan\base\Dropdown','email_bcc');
		$bcc_field->validate_values = false;

		
		// Add Existing Attachment  existing Draft & fwd communication Message
		if($this->mode == "DraftMessage" OR $this->mode == "fwd_email"){
			$attach_m = $this->add('xepan\communication\Model_Communication_Attachment');
			$attach_m->addCondition('communication_id', $this->communication_id);
			$attach_m->addCondition('type','attach');
			$attach=$form->layout->add('xepan\communication\View_Lister_Attachment',null,'existing_attachments');
			$attach->setModel($attach_m);
			
		}
		
		//  Re . Compose  Emails existing communication Message
		if($this->mode == "DraftMessage"){
			
			$form->layout->template->trySet('compose_email_header','New Message');
			$emails_to =[];
			$draft_to_emails = $replay_model->getReplyEmailFromTo()['to'];
			unset($draft_to_emails[0]);
			foreach ($draft_to_emails as $to_field_emails) {
				$emails_to [] = $to_field_emails['email'];
				$to_field->js(true)->append("<option value='".$to_field_emails['email']."'>".$to_field_emails['name']." &lt;".$to_field_emails['email']."&gt;</option>")->trigger('change');
			}
			$to_field->set($emails_to);
			$this->subject = $replay_model['title'];
			$this->message = $replay_model['description'];

		}
		// Reply based on existing communication id

		if($this->mode == 'reply_email'){
			$form->layout->template->trySet('compose_email_header','Reply Email');
			$emails_to=$replay_model->getReplyEmailFromTo()['to'][0];
			$to_field->js(true)->append("<option value='".$emails_to['email']."'>".$emails_to['name']." &lt;".$emails_to['email']."&gt; </option>")->trigger('change');
			$to_field->set($emails_to['email']);

			$this->subject="Re: ".$replay_model['title'];
			$this->message="<br/><br/><br/><br/><blockquote>".$replay_model['description']."<blockquote>";
		}

		if($this->mode == 'reply_email_all'){
			$form->layout->template->trySet('compose_email_header','Reply Email (to all)');
			$emails_to =[];		
			foreach ($replay_model->getReplyEmailFromTo()['to'] as $to_field_emails) {
				$emails_to [] = $to_field_emails['email'];
				$to_field->js(true)->append("<option value='".$to_field_emails['email']."'>".$to_field_emails['name']." &lt;".$to_field_emails['email']."&gt;</option>")->trigger('change');
			}
			$to_field->set($emails_to);

			$emails_cc =[];		
			foreach ($replay_model->getReplyEmailFromTo()['cc'] as $cc_field_emails) {
				$emails_cc [] = $cc_field_emails['email'];
				$cc_field->js(true,$cc_field->js()->show()->_selector('#cc-field'))->append("<option value='".$cc_field_emails['email']."'>".$cc_field_emails['name']." &lt;".$cc_field_emails['email']."&gt;</option>")->trigger('change');
			}
			$cc_field->set($emails_cc);
			
			$emails_bcc =[];		
			foreach ($replay_model->getReplyEmailFromTo()['bcc'] as $bcc_field_emails) {
				$emails_bcc [] = $bcc_field_emails['email'];
				$bcc_field->js(true,$bcc_field->js()->show()->_selector('#bcc-field'))->append("<option value='".$bcc_field_emails['email']."'>".$bcc_field_emails['name']." &lt;".$bcc_field_emails['email']."&gt; </option>")->trigger('change');
			}
			$bcc_field->set($emails_bcc);

			$this->subject="Re: ".$replay_model['title'];
			$this->message="<br/><br/><br/><br/><blockquote>".$replay_model['description']."<blockquote>";
		}

		if($this->mode=='fwd_email'){
			$form->layout->template->trySet('compose_email_header','Forward Email');
			$this->subject="Fwd: ".$replay_model['title'];
			$this->message="<br/><br/><br/><br/><blockquote> ---------- Forwarded message ----------<br>".$replay_model['description']."<.blockquote>";
		}

		// Reply/Compose Based on contact
		$contact_emails=$this->app->stickyGET('send_email_contact');
		$contact_id=$this->app->stickyGET('contact_id');

		if($contact_emails And $contact_id){
			
			$contact_m=$this->add('xepan\base\Model_Contact')->load($contact_id);
			$emails = array_reverse($contact_m->getEmails());
			$email_to = array_pop($emails);
			$to_field->js(true)->append("<option value='".$email_to."'>".$contact_m['name']." &lt;".$email_to."&gt; </option>")->trigger('change');
			$to_field->set($email_to);
			
			$emails_cc =[];		
			foreach ($emails as $cc_field_emails) {
				$emails_cc [] = $cc_field_emails;
				$cc_field->js(true,$cc_field->js()->show()->_selector('#cc-field'))->append("<option value='".$cc_field_emails."'>".$contact_m['name']." &lt;".$cc_field_emails."&gt;</option>")->trigger('change');
			}
			$cc_field->set($emails_cc);
			
		}

		if($_GET[$this->name.'_src_email']){

			$results = [];
			$contact_info = $this->add('xepan\base\Model_Contact_Email');
			$contact_info->addCondition(
				$contact_info->dsql()->orExpr()
				->where('value','like','%'.$_GET['q'].'%')
				->where($contact_info->dsql()->expr('[0] like "%[1]%"',[$contact_info->refSQL('contact_id')->fieldQuery('name'),$_GET['q']]))
				);
			$contact_info->setLimit(20);
			
			foreach ($contact_info as $cont) {
				$results[] = ['id'=>$cont->id,'text'=>$cont['contact'].' <'.$cont['value'].'>'];
			}

			echo json_encode(
				[
					"results" => $results,
					"more"=>false	
				]
				);
			exit;
		}

		$to_field->select_menu_options = 
		$cc_field->select_menu_options = 
		$bcc_field->select_menu_options = 
			[	
				'width'=>'100%',
				'tags'=>true,
				'tokenSeparators'=>[',','\n\r'],
				'ajax'=>[
					'url' => $this->api->url(null,[$this->name.'_src_email'=>true])->getURL(),
					'dataType'=>'json'
				]
			];

		$to_field->setAttr('multiple','multiple');
		$cc_field->setAttr('multiple','multiple');
		$bcc_field->setAttr('multiple','multiple');

		$email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');

		$email_username_model=$this->add('xepan\communication\Model_Communication_EmailSetting');
		if($_GET['email_username']){
			$email_username_model->tryLoad($_GET['email_username']);
		}

		$subject_field = $form->addField('email_subject')->set($this->subject)->validate('required');
		// $form->addField('Checkbox','save_as_draft');
		$body_field = $form->addField('xepan\base\RichText','email_body')->set($this->message);
		
		$body_field->options = ['toolbar1'=>"styleselect | bold italic fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor",'menubar'=>false];	

		$view=$form->layout->add('View',null,'signature')->setHTML($email_username_model['signature']);
		$mymail->js('change',$view->js()->reload(['email_username'=>$mymail->js()->val()]));

		$this->app->forget('subject');
		$this->app->forget('message');

		// $mymail->js('change',$form->js()->atk4_form('reloadField','email_signature',[$this->app->url(),'email_username'=>$mymail->js()->val()]));
		
		$multi_upload_field = $form->addField('xepan\base\Form_Field_Upload','attachment',"")
									->allowMultiple()->addClass('xepan-padding');
									// ->display(['form'=>'xepan\base\Upload'])
										// ->setFormatFilesTemplate('xepan\base\Upload');

		// $multi_upload_field->setAttr('accept','.jpeg,.png,.jpg');
		$filestore_image=$this->add('xepan\filestore\Model_File',['policy_add_new_type'=>true]);
		$multi_upload_field->setModel($filestore_image);
		
		$save_btn=$form->addSubmit('Save As Draft')->addClass('btn btn-primary');
		if($form->isSubmitted()){
		// $form->onSubmit(function($f)use($save_btn){
			// throw new \Exception(print_r($this->app->employee->id), 1);
																											
			$email_settings = $this->add('xepan\communication\Model_Communication_EmailSetting')->load($form['email_username']);
			$mail = $this->add('xepan\communication\Model_Communication_Email');
			if($this->mode == "DraftMessage"){
				$mail ->load($this->communication_id);
			}
			$mail['direction']='Out';
			$mail->setfrom($email_settings['from_email'],$email_settings['from_name']);
			
			foreach (explode(",",$form['email_to']) as $e2) {
				if(is_numeric(trim($e2))){
					$contact_info = $this->add('xepan\base\Model_Contact_Info');
					$contact_info->tryLoad($e2);
					if(!$contact_info->loaded())
						return $form->error('email_to','Value '.$e2.' is not acceptable...');
					$mail->addTo($contact_info['value'],$contact_info['contact']);
				}else{
					if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
						return $form->error('email_to','Value '.$e2.' is not acceptable');
					$mail->addTo($e2);
				}
			}

			foreach (explode(",",$form['email_cc']) as $e2) {
				if($form['email_cc']){
					if(is_numeric(trim($e2))){
						$contact_info = $this->add('xepan\base\Model_Contact_Info');
						$contact_info->tryLoad($e2);
							if($contact_info->id != $e2)
								return $form->error('email_cc','Value '.$e2.' is not acceptable...');
						$mail->addCC($contact_info['value'],$contact_info['contact']);
					}else{
						if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
							return $form->error('email_cc','Value '.$e2.' is not acceptable');
						$mail->addCC($e2);
					}
				}
			}

			foreach (explode(",",$form['email_bcc']) as $e2) {
				if($form['email_bcc']){
					if(is_numeric(trim($e2))){
						$contact_info = $this->add('xepan\base\Model_Contact_Info');
						$contact_info->tryLoad($e2);
						if($contact_info->id != $e2)
							return $form->error('email_bcc','Value '.$e2.' is not acceptable...');
						$mail->addBcc($contact_info['value'],$contact_info['contact']);
					}else{
						if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
							return $form->error('email_bcc','Value '.$e2.' is not acceptable');
						$mail->addBcc($e2);
					}
				}
			}

			$upload_images_array = explode(",",$form['attachment']);

			$mail->setSubject($form['email_subject']);
			$mail->setBody($form['email_body']);
			
			$mail->findContact('to');
			$mail->save();
			
			if($this->mode == "fwd_email"){
				
				$attach_m = $this->add('xepan\communication\Model_Communication_Attachment');
				$attach_m->addCondition('communication_id', $this->communication_id);
				$attach_m->addCondition('type','attach');
				
				foreach ($attach_m as  $existing_attachment_model) {
						$upload_images_array[] = $existing_attachment_model['file_id'];
				}
			}			

			foreach ($upload_images_array as $file_id) {
				$mail->addAttachment($file_id,'attach');
			}
			if($form->isClicked($save_btn)){
				$js=[
					$form->js()->univ()->successMessage('Save Email As Draft'),
					$this->js()->hide(),
					$form->js()->_selector('.xepan-communication-email-list')->trigger('reload')
				];
				// return $form->js(null,$form->js()->univ()->successMessage('EMAIL SENT'))->univ()->redirect($this->app->url('xepan_communication_emails'))->execute();
				return $form->js(null,$js)->reload()->execute();
			}
			$mail->send($email_settings);
			
			if($replay_model->loaded()){
				$replay_model['related_id']=$mail->id;
				$replay_model->save();
			}
			
			$js=[
					$form->js()->univ()->successMessage('EMAIL SENT'),
					$this->js()->hide(),
					$form->js()->_selector('.xepan-communication-email-list')->trigger('reload')
				];

				return $form->js(null,$js)->reload()->execute();
			// return $f->js(null,$f->js()->univ()->successMessage('EMAIL SENT'))->execute();
		}

		$this->js('click',[$this->js()->show()->_selector('.compose-email-view-popup'),$this->js()->reload()])->_selector('.email-compose-btn');
		// $this->js('click',
		// 		[
		// 			$to_field->js()->val("")->trigger('change'),
		// 			$subject_field->js()->val(""),
		// 			$body_field->js()->html("")
		// 		]
		// )->_selector('.email-compose-btn');

		$this->js('click',$this->js()->hide()->_selector('.compose-email-view-popup'))->_selector('.close-compose-email-popup');
		$this->js('click',$this->js()->slideToggle()->_selector('.compose-email-inner'))->_selector('.minimize-compose-email-popup');
		$this->js('click',
			[
			$this->js()->toggleClass('fa-compress')->_selector('.fa-expand'),
			$this->js()->toggleClass('compose-email-resize-toggle')->_selector('.compose-email-view-popup'),
			]
			)->_selector('.fa-expand');
	}
}