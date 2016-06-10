<?php


namespace xepan\communication;

class View_EasySetupWizard extends \View{
	function init(){
		parent::init();


		if($_GET[$this->name.'_config_user_settings']){

			$frontend_config = $this->app->epan->config;
			$reg_type=$frontend_config->getConfig('REGISTRATION_TYPE');

			$registration_config = $this->app->epan->config;
			$reg_subject = $registration_config->getConfig('REGISTRATION_SUBJECT','base');
			$reg_body = $registration_config->getConfig('REGISTRATION_BODY','base');

			$file_reg_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_subject.html'));
			$file_reg_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_body.html'));
		
			$resetpass_config = $this->app->epan->config;
			$reset_subject = $resetpass_config->getConfig('RESET_PASSWORD_SUBJECT');
			$reset_body = $resetpass_config->getConfig('RESET_PASSWORD_BODY');

			$file_reset_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_subject.html'));
			$file_reset_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_body.html'));
		
			$verify_config = $this->app->epan->config;
			$verify_subject = $verify_config->getConfig('VERIFICATIONE_MAIL_SUBJECT');
			$verify_body = $verify_config->getConfig('VERIFICATIONE_MAIL_BODY');
		
			$file_verification_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_subject.html'));
			$file_verification_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_body.html'));
			
			$update_config = $this->app->epan->config;
			$update_subject = $update_config->getConfig('UPDATE_PASSWORD_SUBJECT');
			$update_body = $update_config->getConfig('UPDATE_PASSWORD_BODY');
			
			$file_update_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_subject.html'));
			$file_update_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_body.html'));
			
			if(!$reg_type){
				$reg_type= $frontend_config->setConfig('REGISTRATION_TYPE',"admin_activated",'base');
			}

			if(!$reg_subject){
				$reg_subject = $registration_config->setConfig('REGISTRATION_SUBJECT',$file_reg_subject,'base');
			}
			if(!$reg_body){
				$reg_body = $registration_config->setConfig('REGISTRATION_BODY',$file_reg_body,'base');
			}

			if(!$reset_subject){
				$reset_subject = $resetpass_config->setConfig('RESET_PASSWORD_SUBJECT',$file_reset_subject,'base');
			}

			if(!$reset_body){
				$reset_body = $resetpass_config->setConfig('RESET_PASSWORD_BODY',$file_reset_body,'base');
			}

			if(!$verify_subject){
				$verify_subject = $verify_config->setConfig('VERIFICATIONE_MAIL_SUBJECT',$file_verification_subject,'base');
			}
			if(!$verify_body){
				$verify_body = $verify_config->setConfig('VERIFICATIONE_MAIL_BODY',$file_verification_body,'base');
			}

			if(!$update_subject){
				$update_subject = $update_config->setConfig('UPDATE_PASSWORD_SUBJECT',$file_update_subject,'base');
			}
			if(!$update_body){
				$update_body = $update_config->setConfig('UPDATE_PASSWORD_BODY',$file_update_body,'base');
			}
			
			// $this->js(true)->reload(['UPDATE_PASSWORD_BODY',$update_body]);
			$this->js(true)->univ()->frameURL("User Configuration For Activation/Deactivation",$this->app->url('xepan_communication_general_emailcontent_usertool'));

		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_config_user_settings'=>1]);

			$all = $this->app->epan->config;
			$r_type = $all->getConfig('REGISTRATION_TYPE');
			$reg_sub = $all->getConfig('REGISTRATION_SUBJECT');
			$reg_body = $all->getConfig('REGISTRATION_BODY');
			$reset_pwd_sub = $all->getConfig('RESET_PASSWORD_SUBJECT');
			$reset_pwd_body = $all->getConfig('RESET_PASSWORD_BODY');
			$verify_subject = $all->getConfig('VERIFICATIONE_MAIL_SUBJECT');
			$verify_body = $all->getConfig('VERIFICATIONE_MAIL_SUBJECT');
			$update_subject = $all->getConfig('UPDATE_PASSWORD_SUBJECT');
			$update_body = $all->getConfig('UPDATE_PASSWORD_BODY');

			if(!$r_type || !$reg_sub || !$reg_body || !$reset_pwd_sub || !$reset_pwd_body || !$verify_subject || !$verify_body || !$update_body || !$update_subject){
				$isDone = false;
			}else{	
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already config the user settings, visit page ? <a href="'. $this->app->url('xepan_communication_general_emailcontent_usertool')->getURL().'"> click here to go </a>');
			}

			$user_config_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - Communication')
				->setTitle('Configure Settings For New Users')
				->setMessage('Configuration setting for web user activation & deactivation mailing content')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

				
		if($_GET[$this->name.'_set_emailsetting']){
			$this->js(true)->univ()->frameURL("Mail Config",$this->app->url('xepan_communication_general_email&action=add'));
			if($this->add('xepan\communication\Model_Communication_EmailSetting')->count()->getOne() > 0)
				return 1;
		}

		$isDone = false;
		
			$action = $this->js()->reload([$this->name.'_set_emailsetting'=>1]);

			if($this->add('xepan\communication\Model_Communication_EmailSetting')->count()->getOne() > 0){
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already have emailsetting, visit page ? <a href="'. $this->app->url('xepan_communication_general_email')->getURL().'"> click here to go </a>');
			}

			$email_view = $this->add('xepan\base\View_Wizard_Step');

			$email_view->setAddOn('Application - Communication')
				->setTitle('Configure Email Setting To Communicate Via Email')
				->setMessage('Please configure Email Settings, for Communication with your clients via email')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_check_supportemail_options']){
			$this->js(true)->univ()->frameURL("Mail Config",$this->app->url('xepan_communication_general_email&action=add'));
		}

		$isDone = false;
		
			$action = $this->js()->reload([$this->name.'_check_supportemail_options'=>1]);

			$support_mail = $this->add('xepan\communication\Model_Communication_EmailSetting')->tryLoadAny();

			if($support_mail['is_support_email']){
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already have emailsetting, visit page ? <a href="'. $this->app->url('xepan_communication_general_email')->getURL().'"> click here to go </a>');
			}

			$support_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - Communication')
				->setTitle('Support Email System')
				->setMessage('For Support Services System You have to select option "is support system" in Email Settings')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

	}
}