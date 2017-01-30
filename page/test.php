<?php

namespace xepan\communication;

/**
* 
*/
class page_test extends \xepan\base\Page{
	
	function init(){
		parent::init();

		$message_model = $this->add('xepan\communication\Model_Communication_AbstractMessage');
		$to_raw = [];
		foreach ($message_model as $msg) {
			foreach ($msg['to_raw'] as $to) {
				$read_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
				$read_email->addCondition('communication_id',$msg->id);
				$read_email->addCondition('type',"TO");
				$read_email->addCondition('is_read',true);
				$read_email->addCondition('contact_id',$to['id']);
				$read_email->tryLoadAny();
				$read_email->save();
			}

			foreach ($msg['cc_raw'] as $to) {
				$read_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
				$read_email->addCondition('communication_id',$msg->id);
				$read_email->addCondition('type',"CC");
				$read_email->addCondition('is_read',true);
				$read_email->addCondition('contact_id',$to['id']);
				$read_email->tryLoadAny();
				$read_email->save();
			}

		}
	}
}