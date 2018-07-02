<?php

namespace xepan\communication;

class Model_Communication_SMSSetting extends \xepan\base\Model_Table{
	public $table="communication_sms_setting";
	public $acl_type="Communication_SMSSetting";
	function init(){
		parent::init();
		$this->hasOne('xepan\base\Contact','created_by_id')->set(@$this->app->employee->id);
		$this->addField('name');
		$this->addField('gateway_url')->caption('GateWay Url');
		$this->addField('sms_username')->caption('Gateway User Name');
		$this->addField('sms_password')->type('password')->caption('Gateway Password');
		
		$this->addField('sms_user_name_qs_param')->caption('Gateway User Name Query String Variable');
		$this->addField('sms_password_qs_param')->caption('Gateway Password Query String Variable');
		$this->addField('sms_number_qs_param')->caption('Number Query String Variable');
		$this->addField('sm_message_qs_param')->caption('Messesge Query String Variable');
		$this->addField('sms_prefix')->caption('Message Prefix');
		$this->addField('sms_postfix')->caption('Message Postfix');
	}
}