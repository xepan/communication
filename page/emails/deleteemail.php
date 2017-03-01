<?php


namespace xepan\communication;

class page_emails_deleteemail extends \xepan\base\Page {

	function init(){
		parent::init();

		$my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
		$my_email->addExpression('post_email')->set(function($m,$q){
			return $q->getField('email_username');
		});

		$do_delete_emails = $_POST['delete_emails']; 
		if($do_delete_emails){
			foreach ($do_delete_emails as $delete_email) {
				$delete_model = $this->add('xepan\communication\Model_Communication_Abstract_Email');
				$delete_model->load($delete_email);
				$delete_model->delete();
			}
		}
		
	}
}