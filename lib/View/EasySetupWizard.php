<?php


namespace xepan\communication;

class View_EasySetupWizard extends \View{
	function init(){
		parent::init();

		if($_GET[$this->name.'_set_emailsetting']){
			$this->js(true)->univ()->frameURL("Mail Config",$this->app->url('xepan_communication_general_email&action=add'));
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

		// 	$support_view = $this->add('xepan\base\View_Wizard_Step');
		// 	$support_view->setAddOn('Application - Communication');
		// 	$support_view->setTitle('For Support Email System');
		// 	$support_view->setMessage('For Support Services System You have to select the given option "is support system" in Email Settings ');
		// 	$support_view->setHelpURL('#');
		// 	$support_view->setAction('Click Here',$support_view->js()->redirect('xepan_communication_generalsetting'));
	}
}