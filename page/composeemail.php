<?php
namespace xepan\communication;

class page_composeemail extends \xepan\base\Page{
	public $breadcrumb=['Home'=>'index','Inbox'=>'xepan_communication_emails'];
	public $title="Compose Email";
	// public $subject="";
	// public $message="";
	function init(){
		parent::init();
		
		$this->add('xepan\communication\View_ComposeEmailPopup');

		// $replay_email=$this->api->stickyGET('reply_email');
		// $replay_email_all=$this->api->stickyGET('reply_email_all');
		// $fwd_email=$this->api->stickyGET('fwd_email');
		// $communication_id=$this->api->stickyGET('communication_id');

		// $action= 'add';
		// $form = $this->add('Form');;
		// $form->setLayout(['view/composeemail']);

		// $mymail = $form->addField('Dropdown','email_username')->setEmptyText('Please Select From Email')->validate('required');
		// $mymail->setModel('xepan\hr\Model_Post_Email_MyEmails');		

		// $to_field = $form->addField('xepan\base\DropDown','email_to');
		// $to_field->validate_values = false;
		// $cc_field = $form->addField('xepan\base\Dropdown','email_cc');
		// $cc_field->validate_values = false;
		// $bcc_field = $form->addField('xepan\base\Dropdown','email_bcc');
		// $bcc_field->validate_values = false;

		// // Reply based on existing communication id
		// $replay_model=$this->add('xepan\communication\Model_Communication_Email');
		// if($_GET['communication_id'])
		// 	$replay_model->load($communication_id);
		
		// if($replay_email){
		// 	$emails_to=$replay_model->getReplyEmailFromTo()['to'][0];
		// 	$to_field->js(true)->append("<option value='".$emails_to['email']."'>".$emails_to['name']." &lt;".$emails_to['email']."&gt; </option>")->trigger('change');
		// 	$to_field->set($emails_to['email']);

		// 	$this->subject="Re: ".$replay_model['title'];
		// 	$this->message="<br/><br/><br/><br/><blockquote>".$replay_model['description']."<blockquote>";
		// }

		// if($replay_email_all){
		// 	$emails_to =[];		
		// 	foreach ($replay_model->getReplyEmailFromTo()['to'] as $to_field_emails) {
		// 		$emails_to [] = $to_field_emails['email'];
		// 		$to_field->js(true)->append("<option value='".$to_field_emails['email']."'>".$to_field_emails['name']." &lt;".$to_field_emails['email']."&gt;</option>")->trigger('change');
		// 	}
		// 	$to_field->set($emails_to);

		// 	$emails_cc =[];		
		// 	foreach ($replay_model->getReplyEmailFromTo()['cc'] as $cc_field_emails) {
		// 		$emails_cc [] = $cc_field_emails['email'];
		// 		$cc_field->js(true,$cc_field->js()->show()->_selector('#cc-field'))->append("<option value='".$cc_field_emails['email']."'>".$cc_field_emails['name']." &lt;".$cc_field_emails['email']."&gt;</option>")->trigger('change');
		// 	}
		// 	$cc_field->set($emails_cc);
			
		// 	$emails_bcc =[];		
		// 	foreach ($replay_model->getReplyEmailFromTo()['bcc'] as $bcc_field_emails) {
		// 		$emails_bcc [] = $bcc_field_emails['email'];
		// 		$bcc_field->js(true,$bcc_field->js()->show()->_selector('#bcc-field'))->append("<option value='".$bcc_field_emails['email']."'>".$bcc_field_emails['name']." &lt;".$bcc_field_emails['email']."&gt; </option>")->trigger('change');
		// 	}
		// 	$bcc_field->set($emails_bcc);

		// 	$this->subject="Re: ".$replay_model['title'];
		// 	$this->message="<br/><br/><br/><br/><blockquote>".$replay_model['description']."<blockquote>";
		// }

		// if($fwd_email){
		// 	$this->subject="Fwd: ".$replay_model['title'];
		// 	$this->message="<br/><br/><br/><br/><blockquote> ---------- Forwarded message ----------<br>".$replay_model['description']."<.blockquote>";
		// }

		// // Reply/Compose Based on contact
		// $contact_emails=$this->app->stickyGET('send_email_contact');
		// $contact_id=$this->app->stickyGET('contact_id');

		// if($contact_emails And $contact_id){
			
		// 	$contact_m=$this->add('xepan\base\Model_Contact')->load($contact_id);
		// 	$emails = array_reverse($contact_m->getEmails());
		// 	$email_to = array_pop($emails);
		// 	$to_field->js(true)->append("<option value='".$email_to."'>".$contact_m['name']." &lt;".$email_to."&gt; </option>")->trigger('change');
		// 	$to_field->set($email_to);
			
		// 	$emails_cc =[];		
		// 	foreach ($emails as $cc_field_emails) {
		// 		$emails_cc [] = $cc_field_emails;
		// 		$cc_field->js(true,$cc_field->js()->show()->_selector('#cc-field'))->append("<option value='".$cc_field_emails."'>".$contact_m['name']." &lt;".$cc_field_emails."&gt;</option>")->trigger('change');
		// 	}
		// 	$cc_field->set($emails_cc);
			
		// }

		// if($_GET[$this->name.'_src_email']){

		// 	$results = [];
		// 	$contact_info = $this->add('xepan\base\Model_Contact_Email');
		// 	$contact_info->addCondition(
		// 		$contact_info->dsql()->orExpr()
		// 		->where('value','like','%'.$_GET['q'].'%')
		// 		->where($contact_info->dsql()->expr('[0] like "%[1]%"',[$contact_info->refSQL('contact_id')->fieldQuery('name'),$_GET['q']]))
		// 		);
		// 	$contact_info->setLimit(20);
			
		// 	foreach ($contact_info as $cont) {
		// 		$results[] = ['id'=>$cont->id,'text'=>$cont['contact'].' <'.$cont['value'].'>'];
		// 	}

		// 	echo json_encode(
		// 		[
		// 			"results" => $results,
		// 			"more"=>false	
		// 		]
		// 		);
		// 	exit;
		// }

		// $to_field->select_menu_options = 
		// $cc_field->select_menu_options = 
		// $bcc_field->select_menu_options = 
		// 	[	
		// 		'width'=>'100%',
		// 		'tags'=>true,
		// 		'tokenSeparators'=>[',','\n\r'],
		// 		'ajax'=>[
		// 			'url' => $this->api->url(null,[$this->name.'_src_email'=>true])->getURL(),
		// 			'dataType'=>'json'
		// 		]
		// 	];

		// $to_field->setAttr('multiple','multiple');
		// $cc_field->setAttr('multiple','multiple');
		// $bcc_field->setAttr('multiple','multiple');

		// $email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');

		// $email_username_model=$this->add('xepan\communication\Model_Communication_EmailSetting');
		// if($_GET['email_username']){
		// 	$email_username_model->tryLoad($_GET['email_username']);
		// }

		// $form->addField('email_subject')->set($this->subject)->validate('required');
		// // $form->addField('Checkbox','save_as_draft');
		// $form->addField('xepan\base\RichText','email_body')->set($this->message);
		// $view=$form->layout->add('View',null,'signature')->setHTML($email_username_model['signature']);
		// $mymail->js('change',$view->js()->reload(['email_username'=>$mymail->js()->val()]));

		// $this->app->forget('subject');
		// $this->app->forget('message');

		// // $mymail->js('change',$form->js()->atk4_form('reloadField','email_signature',[$this->app->url(),'email_username'=>$mymail->js()->val()]));
		
		// $multi_upload_field = $form->addField('xepan\base\Form_Field_Upload','attachment',"")
		// 							->allowMultiple()->addClass('xepan-padding');
		// 							// ->display(['form'=>'xepan\base\Upload'])
		// 								// ->setFormatFilesTemplate('xepan\base\Upload');

		// // $multi_upload_field->setAttr('accept','.jpeg,.png,.jpg');
		// $filestore_image=$this->add('xepan\filestore\Model_File',['policy_add_new_type'=>true]);
		// $multi_upload_field->setModel($filestore_image);
		
		// $save_btn=$form->addSubmit('Save As Draft')->addClass('btn btn-primary');

		// $form->onSubmit(function($f)use($save_btn){
		// 	// throw new \Exception(print_r($this->app->employee->id), 1);
									
		// 	$email_settings = $this->add('xepan\communication\Model_Communication_EmailSetting')->load($f['email_username']);
		// 	$mail = $this->add('xepan\communication\Model_Communication_Email');
		// 	$mail['direction']='Out';
		// 	$mail->setfrom($email_settings['from_email'],$email_settings['from_name']);
			
		// 	foreach (explode(",",$f['email_to']) as $e2) {
		// 		if(is_numeric(trim($e2))){
		// 			$contact_info = $this->add('xepan\base\Model_Contact_Info');
		// 			$contact_info->tryLoad($e2);
		// 			if(!$contact_info->loaded())
		// 				return $f->error('email_to','Value '.$e2.' is not acceptable...');
		// 			$mail->addTo($contact_info['value'],$contact_info['contact']);
		// 		}else{
		// 			if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
		// 				return $f->error('email_to','Value '.$e2.' is not acceptable');
		// 			$mail->addTo($e2);
		// 		}
		// 	}

		// 	foreach (explode(",",$f['email_cc']) as $e2) {
		// 		if($f['email_cc']){
		// 			if(is_numeric(trim($e2))){
		// 				$contact_info = $this->add('xepan\base\Model_Contact_Info');
		// 				$contact_info->tryLoad($e2);
		// 					if($contact_info->id != $e2)
		// 						return $f->error('email_cc','Value '.$e2.' is not acceptable...');
		// 				$mail->addCC($contact_info['value'],$contact_info['contact']);
		// 			}else{
		// 				if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
		// 					return $f->error('email_cc','Value '.$e2.' is not acceptable');
		// 				$mail->addCC($e2);
		// 			}
		// 		}
		// 	}

		// 	foreach (explode(",",$f['email_bcc']) as $e2) {
		// 		if($f['email_bcc']){
		// 			if(is_numeric(trim($e2))){
		// 				$contact_info = $this->add('xepan\base\Model_Contact_Info');
		// 				$contact_info->tryLoad($e2);
		// 				if($contact_info->id != $e2)
		// 					return $f->error('email_bcc','Value '.$e2.' is not acceptable...');
		// 				$mail->addBcc($contact_info['value'],$contact_info['contact']);
		// 			}else{
		// 				if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
		// 					return $f->error('email_bcc','Value '.$e2.' is not acceptable');
		// 				$mail->addBcc($e2);
		// 			}
		// 		}
		// 	}



		// 	$upload_images_array = explode(",",$f['attachment']);
		// 	$mail->setSubject($f['email_subject']);
		// 	$mail->setBody($f['email_body']);
			
		// 	$mail->findContact('to');

		// 	$mail->save();

		// 	foreach ($upload_images_array as $file_id) {
		// 		$mail->addAttachment($file_id);
		// 	}
		// 	if($f->isClicked($save_btn)){
		// 		return $f->js(null,$f->js()->univ()->successMessage('EMAIL SENT'))->univ()->redirect($this->app->url('xepan_communication_emails'))->execute();
		// 		// return $f->js(null,$f->js()->univ()->successMessage('Save Email As Draft'))->reload();
		// 	}
		// 	$mail->send($email_settings);
		// 	return $f->js(null,$f->js()->univ()->successMessage('EMAIL SENT'))->univ()->redirect($this->app->url('xepan_communication_emails'))->execute();
		// });
	}

}