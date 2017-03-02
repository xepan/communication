<?php


namespace xepan\communication;

class page_emails_unreademail extends \xepan\base\Page {

	function init(){
		parent::init();


		$unmark_read_emails = $_POST['mark_unread_emails']; 
		
		if($unmark_read_emails){
			foreach ($unmark_read_emails as $unread_email) {
				$unread_model = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
				$unread_model->addCondition('communication_id',$unread_email);
				$unread_model->addCondition('contact_id',$this->app->employee->id);
				$unread_model->tryLoadAny();
				if($unread_model->loaded()){
					$unread_model['is_read'] =  false;
					$unread_model->save();
					
				}
			}
		}
		
	}
}