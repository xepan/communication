<?php


namespace xepan\communication;

class View_EasySetupWizard extends \View{
	function init(){
		parent::init();

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
				->setTitle('Email Setting To Communicate via Email')
				->setMessage('Please Set Email Settings to create an Email for Communication with your clients')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		if($_GET[$this->name.'_check_supportemail_options']){
			$this->js(true)->univ()->frameURL("Mail Config",$this->app->url('xepan_communication_general_email&action=add'));
		}

		$isDone = false;
		
			$action = $this->js()->reload([$this->name.'_check_supportemail_options'=>1]);

			$support_mail = $this->add('xepan\communication\Model_Communication_EmailSetting');

			if($support_mail['is_support_email']){
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already have emailsetting, visit page ? <a href="'. $this->app->url('xepan_communication_general_email')->getURL().'"> click here to go </a>');
			}

			$support_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - Communication')
				->setTitle('For Support Email System')
				->setMessage('For Support Services System You have to select the given option "is support system" in Email Settings, At Least For One')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);
	}
}