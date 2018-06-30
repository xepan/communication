<?php

namespace xepan\communication;


class Model_Config_SubType extends \xepan\base\Model_ConfigJsonModel{
	public $fields =[
						'sub_type_1_label_name'=>'line',
						'sub_type'=>'text',
						'sub_type_2_label_name'=>'line',
						'calling_status'=>'text',
						'sub_type_3_label_name'=>'line',		
						'sub_type_3'=>'text',
					];
	public $config_key = 'COMMUNICATION_SUB_TYPE';
	public $application='Communication';

	function init(){
		parent::init();

		$this->getElement('sub_type')->caption('Sub Type 1');
		$this->getElement('calling_status')->caption('Sub Type 2');
		$this->getElement('sub_type_1_label_name')->defaultValue('Product/ Service/ Related To');
		$this->getElement('sub_type_2_label_name')->defaultValue('Communication Result');
		$this->getElement('sub_type_3_label_name')->defaultValue('Communication Remark');
		// $this->getField('default_login_page')->defaultValue('login');
		// $this->getField('system_contact_types')->defaultValue('Contact,Customer,Supplier,Employee');
	}

}