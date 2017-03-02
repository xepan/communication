<?php

namespace xepan\communication;

/**
* 
*/
class page_test extends \xepan\base\Page{
	

	function page_setemailsettingid(){
		$email_set = $this->add('xepan\communication\Model_Communication_EmailSetting')
							->addCondition('imap_email_username','<>',null)
							->addCondition('imap_email_username','<>','')
							;
		
		foreach ($email_set as $s) {
			$this->app->db->dsql()->table('communication')
						->set('emailsetting_id',$s->id)
						->where('mailbox','like',$s['imap_email_username'].'%')
						->update();
		}
	}

	function page_markmsgread(){

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