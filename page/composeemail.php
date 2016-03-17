<?php
namespace xepan\communication;

class page_composeemail extends \Page{
	function init(){
		parent::init();

		$action= 'add';
		$form = $this->add('Form');
		$form->setLayout(['view/composeemail']);

		$form->addField('Dropdown','email_from')->setModel('xepan\hr\Post_Email_MyEmails');
		$to_field = $form->addField('xepan\base\DropDown','email_to');
		$to_field->validate_values = false;
		$cc_field = $form->addField('xepan\base\Dropdown','email_cc');
		$cc_field->validate_values = false;
		$bcc_field = $form->addField('xepan\base\Dropdown','email_bcc');
		$bcc_field->validate_values = false;

		if($_GET[$this->name.'_src_email']){

			$results = [];
			$contact_info = $this->add('xepan\base\Model_Contact_Email');
			$contact_info->addCondition('value','like','%'.$_GET['q'].'%');
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
				'tokenSeparators'=>[','],
				'ajax'=>[
					'url' => $this->api->url(null,[$this->name.'_src_email'=>true])->getURL(),
					'dataType'=>'json'
				]
			];

		$to_field->setAttr('multiple','multiple');
		$cc_field->setAttr('multiple','multiple');
		$bcc_field->setAttr('multiple','multiple');


		$form->addField('email_subject');
		$form->addField('xepan\base\RichText','email_body');

		
		$form->onSubmit(function($f){

			$email_settings = $this->add('xepan\base\Model_Epan_EmailSetting')->tryLoadAny();
			$mail = $this->add('xepan\communication\Model_Communication_Email');
			$mail->setfrom($email_settings['from_email'],$email_settings['from_name']);
			
			foreach (explode(",",$f['email_to']) as $e2) {
				if(is_numeric(trim($e2))){
					$contact_info = $this->add('xepan\base\Model_Contact_Info');
					$contact_info->tryLoad($e2);
					if(!$contact_info->loaded())
						return $f->error('email_to','Value '.$e2.' is not acceptable...');
					$mail->addTo($contact_info['value'],$contact_info['contact']);
				}else{
					if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
						return $f->error('email_to','Value '.$e2.' is not acceptable');
					$mail->addTo($e2);
				}
			}

			foreach (explode(",",$f['email_cc']) as $e2) {
				if(is_numeric(trim($e2))){
					$contact_info = $this->add('xepan\base\Model_Contact_Info');
					$contact_info->tryLoad($e2);
					if($contact_info->id != $e2)
						return $f->error('email_cc','Value '.$e2.' is not acceptable...');
					$mail->addCC($contact_info['value'],$contact_info['contact']);
				}else{
					if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
						return $f->error('email_cc','Value '.$e2.' is not acceptable');
					$mail->addCC($e2);
				}
			}

			foreach (explode(",",$f['email_bcc']) as $e2) {
				if(is_numeric(trim($e2))){
					$contact_info = $this->add('xepan\base\Model_Contact_Info');
					$contact_info->tryLoad($e2);
					if($contact_info->id != $e2)
						return $f->error('email_bcc','Value '.$e2.' is not acceptable...');
					$mail->addBcc($contact_info['value'],$contact_info['contact']);
				}else{
					if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
						return $f->error('email_bcc','Value '.$e2.' is not acceptable');
					$mail->addBcc($e2);
				}
			}

			$mail->setSubject($f['email_subject']);
			$mail->setBody($f['email_body']);
			// $mail->send($email_settings);
			$mail->save();
			return $f->js()->univ()->successMessage('EMAIL SENDING DONE, , but actually code commented');
		});
	}

}