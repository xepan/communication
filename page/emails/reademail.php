<?php


namespace xepan\communication;

class page_emails_reademail extends \xepan\base\Page {

	function init(){
		parent::init();


		$mark_read_emails = $_POST['mark_read_emails']; 
		
		if($mark_read_emails){
			foreach ($mark_read_emails as $mark_email) {
				$read_model = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
				$read_model->addCondition('communication_id',$mark_email);
				$read_model->addCondition('contact_id',$this->app->employee->id);
				$read_model->tryLoadAny();
				if($read_model->loaded()){
					$read_model['is_read'] =  true;
					$read_model->save();
					
				}
			}
		}
		
	}
}