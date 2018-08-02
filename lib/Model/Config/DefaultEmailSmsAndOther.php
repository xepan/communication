<?php

namespace xepan\communication;

class Model_Config_DefaultEmailSmsAndOther extends \xepan\base\Model_ConfigJsonModel{
	public $fields =[
						'default_email'=>'DropDown',
						'default_sms'=>'DropDown',
					];
	public $config_key = 'SYSTEM_DEFAULT_EMAIL_SMS_AND_OTHER';
	public $application='Communication';

	function init(){
		parent::init();

		$email_model = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$email_model->addCondition('is_active',true);
		$this->getElement('default_email')->setModel($email_model)->hint("used for sending email for reminder, task, followup and others.");

		$sms_model = $this->add('xepan\communication\Model_Communication_SMSSetting');
		$this->getElement('default_sms')->setModel($sms_model)->hint("used for sending sms for reminder, task, followup and others.");
	}
}