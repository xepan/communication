<?php

namespace xepan\communication;

class Model_EmployeeCommunication extends \xepan\hr\Model_Employee{

	public $from_date;
	public $to_date;

	function init(){
		parent::init();

		$this->addCondition('status','Active');

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
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						->addCondition('communication_type','Call')
						->addCondition('status','Called')
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		$this->addExpression('received_call')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						->addCondition('communication_type','Call')
						->addCondition('status','Received')
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		$this->addExpression('total_email')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_Communication_Email',['table_alias'=>'employee_commni_all_emails']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						// ->addCondition('status','Received')
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		$this->addExpression('total_comment')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_Communication_Comment',['table_alias'=>'employee_commni_all_comment']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		$this->addExpression('total_meeting')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_Communication_Personal',['table_alias'=>'employee_commni_all_personal']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		$this->addExpression('attended_others_meeting')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_CommunicationRelatedEmployee',['table_alias'=>'employee_commni_other_meeting']);
			return 	$all_call->addCondition('employee_id',$q->getField('id'))
						->addCondition('comm_created_at','>=',$this->from_date)
						->addCondition('comm_created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});		

		$this->addExpression('total_sms')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_Communication_SMS',['table_alias'=>'employee_commni_all_sms']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		$this->addExpression('total_telemarketing')->set(function($m,$q){
			$all_call = $this->add('xepan\communication\Model_Communication_TeleMarketing',['table_alias'=>'employee_commni_all_telemarketing']);
			return 	$all_call->addCondition('created_by_id',$q->getField('id'))
						->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->count();
		});

		
		$this->addExpression('unique_leads_from')->set(function($m,$q){
			$all = $this->add('xepan\communication\Model_Communication',['table_alias'=>'employeecommniallfrom']);
			return $all->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->addCondition([['to_id',$q->getField('id')],['created_by_id',$q->getField('id')]])
						->_dsql()->del('fields')
						->field('GROUP_CONCAT(DISTINCT(from_id))')
						;
		});

		$this->addExpression('unique_leads_to')->set(function($m,$q){
			$all = $this->add('xepan\communication\Model_Communication',['table_alias'=>'employeecommniallto']);
			return $all->addCondition('created_at','>=',$this->from_date)
						->addCondition('created_at','<',$this->api->nextDate($this->to_date))
						->addCondition([['from_id',$q->getField('id')],['created_by_id',$q->getField('id')]])
						// ->addCondition('created_by_id',$q->getField('id'))
						->_dsql()->del('fields')
						->field('GROUP_CONCAT(DISTINCT(to_id))')
						;
		});

		// $this->addExpression('created_lead')->set(function($m,$q){
		// 	$all = $this->add('xepan\base\Model_Contact',['table_alias'=>'commcontact']);
		// 	return $all->addCondition('created_by_id',$q->getField('id'))
		// 				->addCondition('created_at','>=',$this->from_date)
		// 				->addCondition('created_at','<',$this->api->nextDate($this->to_date))
		// 				->count();
			
		// });

	}
}