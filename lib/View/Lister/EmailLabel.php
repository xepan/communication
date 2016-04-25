<?php
namespace xepan\communication;

class View_Lister_EmailLabel extends \CompleteLister{

	function formatRow(){
		$email=$this->add('xepan\communication\Model_Communication_Email_Received');
		$email->addCondition('direction','In');

		$email->addCondition('mailbox',$this->model['email_username'].'#INBOX');
		
		$email_count=$email->count()->getOne();

		$this->current_row['email_count']=$email_count ;
		return parent::formatRow();
	}

	function getJSID(){
		return 'email-nav-labels-wrapper';
	}
	function defaultTemplate(){
		return ['view/emails/label'];
	}
}