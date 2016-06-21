<?php

namespace xepan\communication;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_communication';

	function setup_admin(){
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('../vendor/xepan/communication/');

		$contact_email=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');

		$contact_email->addCondition('extra_info',null);
		$contact_count=$contact_email->count()->getOne();

		$all_email=$this->add('xepan\communication\Model_Communication_Email_Received');
		$all_email->addCondition('extra_info',null);
		$all_count=$all_email->count()->getOne();
		
		$this->app->side_menu->addItem(['Emails','icon'=>' fa fa-envelope','badge'=>[$contact_count. " / " .$all_count ,'swatch'=>' label label-primary pull-right']],'xepan_communication_emails')->setAttr(['title'=>'Emails']);
		$search_communication = $this->add('xepan\communication\Model_Communication');
		$this->app->addHook('quick_searched',[$search_communication,'quickSearch']);
		$this->app->addHook('contact_info',[$search_communication,'set_old_communication_info']);
		return $this;
	}

	function setup_frontend(){
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('./vendor/xepan/communication/');
			return $this;
	}

	function resetDB(){
		if(!isset($this->app->old_epan)) $this->app->old_epan = $this->app->epan;
        if(!isset($this->app->new_epan)) $this->app->new_epan = $this->app->epan;
        
		$this->app->epan=$this->app->old_epan;
        $truncate_model = ['Communication_Attachment','Communication','Communication_EmailSetting','Communication_SMSSetting'];
        foreach ($truncate_model as $t) {
            $m=$this->add('xepan\communication\Model_'.$t);
            foreach ($m as $mt) {
                $mt->delete();
            }
        }
        
        $this->app->epan=$this->app->new_epan;

        //Set Config
        $frontend_config = $this->app->epan->config;
		$frontend_config->setConfig('REGISTRATION_TYPE',"self_activated",'communication');
		
		$registration_config = $this->app->epan->config;
		$file_reg_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_subject.html'));
		$file_reg_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_body.html'));
			
		$registration_config->setConfig('REGISTRATION_SUBJECT',$file_reg_subject,'communication');
		$registration_config->setConfig('REGISTRATION_BODY',$file_reg_body,'communication');
		
		$resetpass_config = $this->app->epan->config;
		$file_reset_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_subject.html'));
		$file_reset_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_body.html'));
		
		$resetpass_config->setConfig('RESET_PASSWORD_SUBJECT',$file_reset_subject,'communication');
		$resetpass_config->setConfig('RESET_PASSWORD_BODY',$file_reset_body,'communication');
		
		$verify_config = $this->app->epan->config;
		$file_verification_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_subject.html'));
		$file_verification_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_body.html'));
		
		$verify_config->setConfig('VERIFICATIONE_MAIL_SUBJECT',$file_verification_subject,'communication');
		$verify_config->setConfig('VERIFICATIONE_MAIL_BODY',$file_verification_body,'communication');
		
		$update_config = $this->app->epan->config;
		$file_update_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_subject.html'));
		$file_update_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_body.html'));
		
		$update_config->setConfig('UPDATE_PASSWORD_SUBJECT',$file_update_subject,'communication');
		$update_config->setConfig('UPDATE_PASSWORD_BODY',$file_update_body,'communication');
		

	}
}
