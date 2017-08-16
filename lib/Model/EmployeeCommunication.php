<?php

namespace xepan\communication;

/**
* 
*/
class Model_EmployeeCommunication extends \xepan\hr\Model_Employee{
	public $from_date;
	public $to_date;
	function init(){
		parent::init();
		$this->addCondition('status','Active');
		// throw new \Exception($this->from_date, 1);
		
		$this->addExpression('total_call')->set(function($m,$q){
			// return $q->getField('id');
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
				return 	$all_call->addCondition('created_by_id',$q->getField('id'))
							->addCondition('communication_type','Call')
							->addCondition('created_at','>=',$this->from_date)
							->addCondition('created_at','<',$this->api->nextDate($this->to_date))
							->count();
		});

		$this->addExpression('dial_call')->set(function($m,$q){
			// return $q->getField('id');
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
				return 	$all_call->addCondition('created_by_id',$q->getField('id'))
							->addCondition('communication_type','Call')
							->addCondition('status','Called')
							->addCondition('created_at','>=',$this->from_date)
							->addCondition('created_at','<',$this->api->nextDate($this->to_date))
							->count();
		});
		$this->addExpression('received_call')->set(function($m,$q){
					// return $q->getField('id');
					$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
						return 	$all_call->addCondition('created_by_id',$q->getField('id'))
									->addCondition('communication_type','Call')
									->addCondition('status','Received')
									->addCondition('created_at','>=',$this->from_date)
									->addCondition('created_at','<',$this->api->nextDate($this->to_date))
									->count();
				});


	}
}