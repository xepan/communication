<?php


namespace xepan\communication;

class page_emails_unspamemail extends \xepan\base\Page {

	function init(){
		parent::init();


		$mark_not_spam_emails = $_POST['mark_not_spam_emails']; 
		
		if($mark_not_spam_emails){
			foreach ($mark_not_spam_emails as $unspam_email) {
				$unspam_m = $this->add('xepan\communication\Model_Communication_Abstract_Email')
					->load($unspam_email);
				$unspam_m['status'] = "Received";
				$unspam_m->save();
				
			}
		}
		
	}
}