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
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$department = $this->app->stickyGET('department');

		$form = $this->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelsCoppalsible(true)
			->layout([
				'date_range'=>'Filter~c1~3',
				'employee'=>'c2~3',
				'department'=>'c3~3',
				'FormButtons~&nbsp;'=>'c4~3'
			]);

		$date = $form->addField('DateRangePicker','date_range');
		$set_date = $this->app->today." to ".$this->app->today;
		if($from_date){
			$set_date = $from_date." to ".$to_date;
			$date->set($set_date);
		}
		$emp_field = $form->addField('xepan\base\Basic','employee');
		$emp_field->setModel('xepan\hr\Model_Employee')
					->addCondition('status','Active');
		
		$emp_model = $this->add('xepan\communication\Model_EmployeeCommunication',['from_date'=>$from_date,'to_date'=>$to_date]);
		$emp_model->addCondition('status','Active');
		if($emp_id){
			$emp_model->addCondition('id',$emp_id);
		}
		if($_GET['from_date']){
			$emp_model->from_date = $_GET['from_date'];
		}
		if($_GET['to_date']){
			$emp_model->to_date = $_GET['to_date'];
		}
		if($department){
			$emp_model->addCondition('department_id',$department);
		}

		$dept_field = $form->addField('xepan\base\DropDown','department');
		$dept_field->setModel('xepan\hr\Model_Department');
		$dept_field->setEmptyText('All');

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
							->addCondition('created_at','>=',$_GET['from_date'])
							->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
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
								->addCondition('created_at','>=',$_GET['from_date'])
								->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
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

			$grid->js()->reload(
						[
						'employee_id'=>$form['employee'],
						'from_date'=>$date->getStartDate()?:0,
						'to_date'=>$date->getEndDate()?:0,
						'department'=>$form['department']
						]
			)->execute();
		}

		/*Report Digging*/
		$total_call_vp = $this->add('VirtualPage')->set(function($page){
			// $page->add('View_Error')->set($_GET['from_date']);
			$employee_id = $this->app->stickyGET('employee_id');
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
			$all_call->addCondition('created_by_id',$employee_id)
							->addCondition('communication_type','Call')
							->addCondition('created_at','>=',$_GET['from_date'])
							->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
							;

			$grid = $page->add('xepan\hr\Grid');
			$grid->setModel($all_call,['from','to','title','description','sub_type','calling_status','status']);
			$grid->addPaginator(50);
			$grid->addHook('formatRow',function($g){
				$g->current_row_html['description'] = $g->model['description'];
			});
		});

		$grid->addMethod('format_total_call',function($g,$f)use($total_call_vp){
				// VP defined at top of init function
			$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Total Calls',$g->api->url($total_call_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		});

		$grid->addFormatter('total_call','total_call');


		$dial_call_vp = $this->add('VirtualPage')->set(function($page){
			// $page->add('View_Error')->set($_GET['from_date']);
			$employee_id = $this->app->stickyGET('employee_id');
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
			$all_call->addCondition('created_by_id',$employee_id)
							->addCondition('communication_type','Call')
							->addCondition('status','Called')
							->addCondition('created_at','>=',$_GET['from_date'])
							->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
							;

			$grid = $page->add('xepan\hr\Grid');
			$grid->setModel($all_call,['from','to','title','description','sub_type','calling_status','status']);
			$grid->addPaginator(50);

			$grid->addHook('formatRow',function($g){
				$g->current_row_html['description'] = $g->model['description'];
			});
		});

		$grid->addMethod('format_dial_call',function($g,$f)use($dial_call_vp){
				// VP defined at top of init function
			$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Dial Calls',$g->api->url($dial_call_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		});

		$grid->addFormatter('dial_call','dial_call');


		$received_call_vp = $this->add('VirtualPage')->set(function($page){
			// $page->add('View_Error')->set($_GET['from_date']);
			$employee_id = $this->app->stickyGET('employee_id');
			$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
			$all_call->addCondition('created_by_id',$employee_id)
							->addCondition('communication_type','Call')
							->addCondition('status','Received')
							->addCondition('created_at','>=',$_GET['from_date'])
							->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
							;

			$grid = $page->add('xepan\hr\Grid');
			$grid->setModel($all_call,['from','to','title','description','sub_type','calling_status','status']);
			$grid->addPaginator(50);

			$grid->addHook('formatRow',function($g){
				$g->current_row_html['description'] = $g->model['description'];
			});
		});

		$grid->addMethod('format_received_call',function($g,$f)use($received_call_vp){
				// VP defined at top of init function
			$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Dial Calls',$g->api->url($received_call_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		});

		$grid->addFormatter('received_call','received_call');

		/*Communication Subtype Digging*/
		foreach (explode(",", $config_m['sub_type']) as $subtypes) {
			// $grid->addColumn($this->app->normalizeName($subtypes));
			$subtype_vp = $this->add('VirtualPage')->set(function($page)use($subtypes){
				// $page->add('View_Error')->set($_GET['from_date']);
				$employee_id = $this->app->stickyGET('employee_id');
				$subtype_m = $page->add('xepan\communication\Model_Communication')
							->addCondition('created_by_id',$employee_id)
							->addCondition('sub_type',$subtypes)
							->addCondition('created_at','>=',$_GET['from_date'])
							->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
							;

				$grid = $page->add('xepan\hr\Grid');
				$grid->setModel($subtype_m,['from','to','title','description','sub_type','calling_status','status']);
				$grid->addPaginator(50);
				$grid->addHook('formatRow',function($g){
					$g->current_row_html['description'] = $g->model['description'];
				});
			});

			$grid->addMethod('format_'.$this->app->normalizeName($subtypes),function($g,$f)use($subtype_vp,$subtypes){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL($subtypes,$g->api->url($subtype_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
			});

			$grid->addFormatter($this->app->normalizeName($subtypes),$this->app->normalizeName($subtypes));
		}

		$calling_config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						'calling_status'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);
		$calling_config_m->tryLoadAny();

		/*Communication Calling Status*/
		foreach (explode(",", $calling_config_m['calling_status']) as $callingstatus) {
			$callingstatus_vp = $this->add('VirtualPage')->set(function($page)use($callingstatus){
				$employee_id = $this->app->stickyGET('employee_id');
				$calling_m = $page->add('xepan\communication\Model_Communication')
								->addCondition('created_by_id',$employee_id)
								->addCondition('calling_status',$callingstatus)
								->addCondition('created_at','>=',$_GET['from_date'])
								->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
								;

				$grid = $page->add('xepan\hr\Grid');
				$grid->setModel($calling_m,['from','to','title','description','sub_type','calling_status','status']);
				$grid->addPaginator(50);
				$grid->addHook('formatRow',function($g){
					$g->current_row_html['description'] = $g->model['description'];
				});
			});

			$grid->addMethod('format_'.$this->app->normalizeName($callingstatus.'c'),function($g,$f)use($callingstatus_vp,$callingstatus){
				$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL($callingstatus,$g->api->url($callingstatus_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
			});

			$grid->addFormatter($this->app->normalizeName($callingstatus),$this->app->normalizeName($callingstatus."c"));
		}	

	}
}