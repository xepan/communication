<?php
namespace xepan\communication;

class page_generalsetting extends \Page{
	public $title="General Settings";
	function init(){
		parent::init();

		$setiingview=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_communication_general_email'],'general_setting',['view/setting/email-setting-grid']);
		$setiingview->setModel('xepan\communication\Communication_EmailSetting');

		$sms_view=$this->add('xepan\hr\CRUD',null,'sms_setting',['view/setting/sms-setting-grid']);
		if($sms_view->isEditing()){
			$form=$sms_view->form;
			$form->setLayout('view/setting/form/sms-setting');
			$form->js(true)->find('button')->addClass('btn btn-primary');
		}	
		$sms_view->setModel('xepan\communication\Model_Communication_SMSSetting');

		/*Reset Password Email Content*/
		$resetpass_config = $this->app->epan->config;
		$reset_subject = $resetpass_config->getConfig('RESET_PASSWORD_SUBJECT');
		$reset_body = $resetpass_config->getConfig('RESET_PASSWORD_BODY');
		$form=$this->add('Form',null,'reset_email');
		$form->addField('line','subject')->set($reset_subject);
		$form->addField('xepan\base\RichText','subject')->set($reset_body)->setFieldHint('{$name},{$email_id},{$password},{$click_here_to_activate}');
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$resetpass_config->setConfig('RESET_PASSWORD_SUBJECT',$form['subject'],'base');

			$registration_config->setConfig('RESET_PASSWORD_BODY',$form['Body'],'base');
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
		

		$misc_config = $this->app->epan->config;
		$misc_time_zone = $update_config->getConfig('TIME_ZONE');
		$form = $this->add('Form_Stacked',null,'misc_view');
		$time_zone_field=$form->addField('DropDown','time_zone')->set($misc_time_zone);
		$time_zone_field->setValueList(array_combine(timezone_identifiers_list(),timezone_identifiers_list()));
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$misc_config->setConfig('TIME_ZONE',$form['time_zone'],'base');
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

	}
	
	function defaultTemplate(){
		return ['page/general-setting'];
	}
}