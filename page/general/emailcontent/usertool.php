<?php

namespace xepan\communication;

class page_general_emailcontent_usertool extends \xepan\communication\page_sidebar{
	public $title="User Panel Setting";
	function init(){
		parent::init();

		/*Frontend User Configuration*/

		$f=$this->add('Form',null,'frontend_user_config');
		$frontend_config = $this->app->epan->config;
		$reg_type= $frontend_config->getConfig('REGISTRATION_TYPE');
		$user_registration_type = $f->addField('DropDown','user_registration_type')->set($reg_type);
		$user_registration_type->setValueList(['self_activated'=>'Self Activation Via Email','admin_activated'=>'Admin Activated',"default_activated"=>'Default Activated'])->validate('required');
		$f->addSubmit('Update');
		
		if($f->isSubmitted()){
			$frontend_config->setConfig('REGISTRATION_TYPE',$f['user_registration_type'],'base');
			$f->js(null,$f->js()->reload())->univ()->successMessage('Update Information')->execute();
		}
		/*Reset Password Email Content*/
		$resetpass_config = $this->app->epan->config;
		$reset_subject = $resetpass_config->getConfig('RESET_PASSWORD_SUBJECT');
		$reset_body = $resetpass_config->getConfig('RESET_PASSWORD_BODY');
		$form=$this->add('Form',null,'reset_email');
		$form->addField('line','subject')->set($reset_subject);
		$form->addField('xepan\base\RichText','body')->set($reset_body)->setFieldHint('{$name},{$email_id},{$password},{$click_here_to_activate}');
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$resetpass_config->setConfig('RESET_PASSWORD_SUBJECT',$form['subject'],'base');

			$resetpass_config->setConfig('RESET_PASSWORD_BODY',$form['body'],'base');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		/*Registration Email Content*/
		$registration_config = $this->app->epan->config;
		$reg_subject = $registration_config->getConfig('REGISTRATION_SUBJECT','base');
		$reg_body = $registration_config->getConfig('REGISTRATION_BODY','base');
		
		$form=$this->add('Form',null,'registration_view');
		$form->addField('line','subject')->set($reg_subject);
		$form->addField('xepan\base\RichText','Body')->set($reg_body)->setFieldHint('{$name},{$email_id},{$password},{$click_here_to_activate}');
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$registration_config->setConfig('REGISTRATION_SUBJECT',$form['subject'],'base');

			$registration_config->setConfig('REGISTRATION_BODY',$form['Body'],'base');

			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}
		/*Verification Email Content*/
		$verify_config = $this->app->epan->config;
		$verify_subject = $verify_config->getConfig('VERIFICATIONE_MAIL_SUBJECT');
		$verify_body = $verify_config->getConfig('VERIFICATIONE_MAIL_BODY');
		$form=$this->add('Form',null,'verification_view');
		$form->addField('line','subject')->set($verify_subject);
		$form->addField('xepan\base\RichText','body')->set($verify_body)->setFieldHint('');
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$verify_config->setConfig('VERIFICATIONE_MAIL_SUBJECT',$form['subject'],'base');

			$verify_config->setConfig('VERIFICATIONE_MAIL_BODY',$form['body'],'base');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		/*Update Password Email Content*/
		$update_config = $this->app->epan->config;
		$update_subject = $update_config->getConfig('UPDATE_PASSWORD_SUBJECT');
		$update_body = $update_config->getConfig('UPDATE_PASSWORD_BODY');
		$form=$this->add('Form',null,'updatepassword_view');
		$form->addField('line','subject')->set($update_subject);
		$form->addField('xepan\base\RichText','body')->set($update_body)->setFieldHint('{$name},{$email_id},{$password}');
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$update_config->setConfig('UPDATE_PASSWORD_SUBJECT',$form['subject'],'base');

			$update_config->setConfig('UPDATE_PASSWORD_BODY',$form['body'],'base');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}
	}

	function defaultTemplate(){
		return ['page/usertool-email-content'];
	}
}