<?php
namespace xepan\communication;

class View_Lister_EmailLabel extends \CompleteLister{

	function formatRow(){
		$email=$this->add('xepan\communication\Model_Communication_Email_Received');
		$email->addCondition('direction','In');
		$email->addCondition('mailbox',$this->model['email_username'].'#INBOX');
		// $email->addCondition('is_read',false);
		$email_count=$email->count()->getOne();
		
		$unreademail=$this->add('xepan\communication\Model_Communication_Email_Received');
		$unreademail->addCondition('direction','In');
		$unreademail->addCondition('mailbox',$this->model['email_username'].'#INBOX');
		// $unreademail->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
		$unreademail->addCondition('is_read',false);
		$unread_email = $unreademail->count()->getOne();
		
		$this->current_row['email_count']=$email_count ;
		$this->current_row['unread_email']=$unread_email ;
		return parent::formatRow();
	}

	function getJSID(){
		return 'email-nav-labels-wrapper';
	}
	function defaultTemplate(){
		return ['view/emails/label'];
	}
}