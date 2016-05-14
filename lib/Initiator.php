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
        $truncate_model = ['Communication_Attachment','Communication','Communication_EmailSetting'];
        foreach ($truncate_model as $t) {
            $m=$this->add('xepan\communication\Model_'.$t);
            foreach ($m as $mt) {
                $mt->delete();
            }
        }
        
        $this->app->epan=$this->app->new_epan;

	}
}
