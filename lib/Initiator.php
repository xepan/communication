<?php

namespace xepan\communication;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_communication';

	function init(){
		parent::init();
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('../vendor/xepan/communication/');

		$contact_email=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');

		$contact_email->addCondition('extra_info',null);
		$contact_count=$contact_email->count()->getOne();

		$all_email=$this->add('xepan\communication\Model_Communication_Email_Received');
		$all_email->addCondition('extra_info',null);
		$all_count=$all_email->count()->getOne();
		
		if($this->app->is_admin){
			$this->app->side_menu->addItem(['Emails','icon'=>' fa fa-envelope','badge'=>[$contact_count. " / " .$all_count ,'swatch'=>' label label-primary label-circle pull-right']],'xepan_communication_emails');
		}
		
	}

	function generateInstaller(){
	}
}
