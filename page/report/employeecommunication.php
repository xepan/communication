<?php

namespace xepan\communication;


/**
* 
*/
class page_report_employeecommunication extends \xepan\base\Page{

	public $title = "Employee Communication Reports";
	
	function init(){
		parent::init();
		$emp_id = $this->app->stickyGET('employee_id');
		// $from_date = $this->app->stickyGET('from_date');
		// $to_date = $this->app->stickyGET('to_date');
		$form = $this->add('Form',null,null,['form/empty']);
		// $date = $form->addField('DateRangePicker','date_range');
		// $set_date = $this->app->today." to ".$this->app->today;
		// if($from_date){
		// 	$set_date = $from_date." to ".$to_date;
		// 	$date->set($set_date);	
		// }
		$emp_field = $form->addField('xepan\base\Basic','employee');
		$emp_field->setModel('xepan\hr\Model_Employee')->addCondition('status','Active');
		
		$emp_model = $this->add('xepan\communication\Model_EmployeeCommunication'/*,['from_date'=>$from_date,'to_date'=>$to_date]*/);
		if($emp_id){
			$emp_model->addCondition('id',$emp_id);
		}
		// if($_GET['from_date']){
		// 	$emp_model->from_date = $_GET['from_date'];
		// }
		$form->addSubmit('Get Details')->addClass('btn btn-primary');
		$grid = $this->add('xepan\hr\Grid'/*,null,null,['view/report/emp-communication-grid-view']*/);


		/*Communication Sub Type Form */
		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						'calling_status'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);
		$config_m->tryLoadAny();
		$model_field_array = ['name','total_call','dial_call','received_call'];

		
		foreach (explode(",", $config_m['sub_type']) as $subtypes) {
			$grid->addColumn($this->app->normalizeName($subtypes));
			$model_field_array[] = $this->app->normalizeName($subtypes);
			
			
			$emp_model->addExpression($this->app->normalizeName($subtypes))->set(function($m,$q)use($subtypes){
				return $m->add('xepan\communication\Model_Communication')
							->addCondition('created_by_id',$q->getfield('id'))
							->addCondition('sub_type',$subtypes)
							->count();
			});


		}

		foreach (explode(",", $config_m['calling_status']) as $callingstatus) {
			$grid->addColumn($this->app->normalizeName($callingstatus));
			$model_field_array[] = $this->app->normalizeName($callingstatus);
			
			$emp_model->addExpression($this->app->normalizeName($callingstatus))->set(function($m,$q)use($callingstatus){
					return $m->add('xepan\communication\Model_Communication')
								->addCondition('created_by_id',$q->getfield('id'))
								->addCondition('calling_status',$callingstatus)
								->count();
				});

		}

		$grid->setModel($emp_model,$model_field_array);
		$order = $grid->addOrder();
		$grid->addpaginator(10);
		$order->move('name','first')->now();
		$order->move('total_call','after','name')->now();
		$order->move('dial_call','after','total_call')->now();
		$order->move('received_call','after','dial_call')->now();

		if($form->isSubmitted()){

			$grid->js()->reload(['employee_id'=>$form['employee']])->execute();
			
			// $form->js()->univ()->redirect($this->app->url(),[
			// 					'employee_id'=>$form['employee'],
			// 					'from_date'=>$date->getStartDate()?:0,
			// 					'to_date'=>$date->getEndDate()?:0
			// 				]
							
			// 			)->execute();
		}

	}
}