<?php

namespace xepan\communication;

class Model_CommunicationRelatedEmployee extends \xepan\base\Model_Table{
	public $table = "communication_related_employee";

	function init(){
		parent::init();
		
		$this->hasOne('xepan\communication\Model_Communication','communication_id');
		$this->hasOne('xepan\hr\Model_Employee','employee_id');
				
		$this->is(
			[
				'communication_id|required',
				'employee_id|required',
			]);	
	}
}