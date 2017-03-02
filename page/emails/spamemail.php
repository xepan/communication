<?php


namespace xepan\communication;

class page_emails_spamemail extends \xepan\base\Page {

	function init(){
		parent::init();


		$mark_spam_emails = $_POST['mark_spam_emails']; 
		
		if($mark_spam_emails){
			foreach ($mark_spam_emails as $spam_email) {
				$spam_m = $this->add('xepan\communication\Model_Communication_Abstract_Email')
					->load($spam_email);
				$spam_m['status'] = "Junk";
				$spam_m->save();
				
			}
		}
		
	}
}