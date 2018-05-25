<?php

namespace xepan\communication;


class Model_Config_SubType extends \xepan\base\Model_ConfigJsonModel{
	public $fields =[
								'sub_type'=>'text',
								'calling_status'=>'text',
					];
	public $config_key = 'COMMUNICATION_SUB_TYPE';
	public $application='Communication';

	function init(){
		parent::init();

		// $this->getField('default_login_page')->defaultValue('login');
		// $this->getField('system_contact_types')->defaultValue('Contact,Customer,Supplier,Employee');
	}

}