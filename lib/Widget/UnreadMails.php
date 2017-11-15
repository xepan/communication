<?php

namespace xepan\communication;

class Widget_UnreadMails extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->view = $this->add('View',null,null,['view\unreadmails-widget']);
	}

	function recursiveRender(){
		$emp = $this->add('xepan\hr\Model_Employee');
		$emp->load($this->app->employee->id);
		$allowed_emails = $emp->getAllowEmails();

		$count = 0;
		foreach ($allowed_emails as $email){
			$email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');
			$email_setting->load($email);
			$allow_email= $this->add('xepan\communication\Model_Communication_Email_Received');
			$allow_email->addCondition('mailbox',$email_setting['email_username'].'#INBOX');
			$allow_email->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
			$count += $allow_email->count()->getOne();
		}

		$this->view->template->trySet('count',$count);
		$this->view->template->trySet('url',$this->app->url('xepan_communication_emails'));
		return parent::recursiveRender();
	}
}