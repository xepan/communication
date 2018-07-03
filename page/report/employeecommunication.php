<?php

namespace xepan\communication;

class page_report_employeecommunication extends \xepan\base\Page{

	public $title = "Employee Communication Reports";
	public $sub_type_1_fields;
	public $sub_type_2_fields;
	public $sub_type_3_fields;
	public $communication_fields;

	function init(){
		parent::init();

		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->tryLoadAny();

		$emp_id = $this->app->stickyGET('employee_id');
		$this->from_date = $from_date  = $this->app->stickyGET('from_date')?:$this->app->today;
		$this->to_date = $to_date = $this->app->stickyGET('to_date')?:$this->app->today;
		$department = $this->app->stickyGET('department');

		$form = $this->add('Form');
		$form->add('xepan\base\Controller_FLC')
			->makePanelsCoppalsible(true)
			->layout([
				'date_range'=>'Filter~c1~2',
				'employee'=>'c2~3',
				'department'=>'c3~3',
				// 'communication_type'=>'c3~2',
				'FormButtons~&nbsp;'=>'c4~2'
			]);

		$date = $form->addField('DateRangePicker','date_range');
		$set_date = $this->app->today." to ".$this->app->today;
		if($from_date){
			$set_date = $from_date." to ".$to_date;
			$date->set($set_date);
		}
		
		$employee_model = $this->add('xepan\hr\Model_Employee',['title_field'=>'name_with_post'])
							->addCondition('status','Active');
		$employee_model->addExpression('name_with_post')->set(function($m,$q){
			return $q->expr('CONCAT_WS("::",[name],[post],[code])',
						[
							'name'=>$m->getElement('name'),
							'post'=>$m->getElement('post'),
							'code'=>$m->getElement('code')
						]
					);
		});	

		$emp_field = $form->addField('xepan\base\Basic','employee');
		$emp_field->setModel($employee_model);
				
		$dept_field = $form->addField('xepan\base\DropDown','department');
		$dept_field->setModel('xepan\hr\Model_Department');
		$dept_field->setEmptyText('All');

		// grid
		$grid = $this->add('xepan\hr\Grid');
		
		$form->addSubmit('Get Details')->addClass('btn btn-primary');
		
		// record model
		$emp_model = $this->add('xepan\communication\Model_EmployeeCommunication',['from_date'=>$from_date,'to_date'=>$to_date]);
		$emp_model->addCondition('status','Active');
		if($emp_id){
			$emp_model->addCondition('id',$emp_id);
		}
		if($from_date){
			$emp_model->from_date = $this->from_date;
		}
		if($to_date){
			$emp_model->to_date = $this->to_date;
		}
		if($department){
			$emp_model->addCondition('department_id',$department);
		}


		$this->communication_fields = ['total_email','total_comment','total_meeting','total_sms','total_telemarketing','total_call','dial_call','received_call'];
		/*Communication Sub Type Form */
		$model_field_array = ['name','communication','total_email','total_comment','total_meeting','total_sms','total_telemarketing','total_call','dial_call','received_call'];

		// sub type 1
		$emp_model->addExpression('communication')->set('""');
		$emp_model->addExpression('subtype_1')->set('""')->caption($config_m['sub_type_1_label_name']?:"Sub Type 1");
		$model_field_array[] = "subtype_1";
		foreach (explode(",", $config_m['sub_type']) as $subtypes) {
			// $grid->addColumn($this->app->normalizeName($subtypes));
			$subtype_name = $this->app->normalizeName($subtypes);
			$this->sub_type_1_fields[] = $subtype_name;
			$model_field_array[] = $subtype_name;

			$emp_model->addExpression($subtype_name)->set(function($m,$q)use($subtypes){
				return $m->add('xepan\communication\Model_Communication')
							->addCondition('created_by_id',$q->getfield('id'))
							->addCondition('sub_type',$subtypes)
							->addCondition('created_at','>=',$this->from_date)
							->addCondition('created_at','<',$this->api->nextDate($this->to_date))
							->count();
			});
		}

		// sub type 2
		$emp_model->addExpression('subtype_2')->set('""')->caption($config_m['sub_type_2_label_name']?:"Sub Type 2");
		$model_field_array[] = "subtype_2";
		foreach (explode(",", $config_m['calling_status']) as $callingstatus) {
			// $grid->addColumn($this->app->normalizeName($callingstatus));
			$subtype_name = $this->app->normalizeName($callingstatus);
			$model_field_array[] = $subtype_name;	
			$this->sub_type_2_fields[] = $subtype_name;

			$emp_model->addExpression($subtype_name)->set(function($m,$q)use($callingstatus){
					return $m->add('xepan\communication\Model_Communication')
								->addCondition('created_by_id',$q->getfield('id'))
								->addCondition('calling_status',$callingstatus)
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->api->nextDate($this->to_date))
								->count();
				});

		}

		// sub type 3
		$emp_model->addExpression('subtype_3')->set('""')->caption($config_m['sub_type_3_label_name']?:"Sub Type 3");
		$model_field_array[] = "subtype_3";
		foreach (explode(",", $config_m['sub_type_3']) as $sub_type_3) {
			// $grid->addColumn($this->app->normalizeName($callingstatus));
			$subtype_name = $this->app->normalizeName($sub_type_3);
			$model_field_array[] = $subtype_name;
			$this->sub_type_3_fields[] = $subtype_name;

			$emp_model->addExpression($subtype_name)->set(function($m,$q)use($sub_type_3){
					return $m->add('xepan\communication\Model_Communication')
								->addCondition('created_by_id',$q->getfield('id'))
								->addCondition('calling_status',$sub_type_3)
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->api->nextDate($this->to_date))
								->count();
				});
		}

		$grid->setModel($emp_model,$model_field_array);
		$order = $grid->addOrder();
		$grid->addpaginator(10);
		$grid->template->tryDel('Pannel');
		
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

		// grid formatter
		$grid->addHook('formatRow',function($g){
			// $communication_graph_data = $g->model['total_email'].",".$g->model['total_call'].",".$g->model['total_telemarketing'].",".$g->model['total_sms'].",".$g->model['total_meeting'].",".$g->model['total_comment'];

			$communication_graph_data = [];
			$communication_graph_data_label = [];
			$comm_label_str = "";
			if($g->model['total_email']){
				$comm_label_str .= "Email: ".$g->model['total_email']."<br/>";
				$communication_graph_data[] = $g->model['total_email'];
				$communication_graph_data_label[] = "Email: ".$g->model['total_email'];
			}
			if($g->model['total_comment']){
				$comm_label_str .= "Comment: ".$g->model['total_comment']."<br/>";
				$communication_graph_data[] = $g->model['total_comment'];
				$communication_graph_data_label[] = "Comment: ".$g->model['total_comment'];
			}

			if($g->model['total_meeting']){
				$comm_label_str .= "Meeting: ".$g->model['total_meeting']."<br/>";
				$communication_graph_data[] = $g->model['total_meeting'];
				$communication_graph_data_label[] = "Meeting: ".$g->model['total_meeting'];
			}
			if($g->model['total_sms']){
				$comm_label_str .= "SMS: ".$g->model['total_sms']."<br/>";
				$communication_graph_data[] = $g->model['total_sms'];
				$communication_graph_data_label[] = "SMS: ".$g->model['total_sms'];
			}
			if($g->model['total_telemarketing']){
				$comm_label_str .= "Tele: ".$g->model['total_telemarketing']."<br/>";
				$communication_graph_data[] = $g->model['total_telemarketing'];
				$communication_graph_data_label[] = "Tele: ".$g->model['total_telemarketing'];
			}
			if($g->model['total_call']){
				$comm_label_str .= "Call: ".$g->model['total_call']."<br/>";
				$communication_graph_data[] = $g->model['total_call'];
				$communication_graph_data_label[] = "Call: ".$g->model['total_call'];
			}

			$sub_type_1_label_str = "";
			$sub_type_1_graph_data = [];
			$sub_type_1_graph_data_label = [];
			foreach ($this->sub_type_1_fields as $name) {
				// $sub_type_1_graph_data .= $g->model[$name].","
				if(!$g->model[$name]) continue;

				$sub_type_1_graph_data[] = $g->model[$name];
				$sub_type_1_graph_data_label[] = $name.": ".$g->model[$name];
				$sub_type_1_label_str .= $name.": ".$g->model[$name]."<br/>";
			}
			// $sub_type_1_graph_data = trim($sub_type_1_graph_data,',');
			$sub_type_1_label_str = trim($sub_type_1_label_str,'<br/>');


			$sub_type_2_label_str = "";
			$sub_type_2_graph_data = [];
			$sub_type_2_graph_data_label = [];
			foreach ($this->sub_type_2_fields as $name) {
				if(!$g->model[$name]) continue;

				$sub_type_2_graph_data[] = $g->model[$name];
				$sub_type_2_graph_data_label[] = $name.": ".$g->model[$name];
				$sub_type_2_label_str .= $name.": ".$g->model[$name]."<br/>";
				// $sub_type_2_graph_data .= $g->model[$name].",";
			}
			// $sub_type_2_graph_data = trim($sub_type_2_graph_data,',');
			$sub_type_2_label_str = trim($sub_type_2_label_str,'<br/>');

			$sub_type_3_graph_data = [];
			$sub_type_3_graph_data_label = [];
			$sub_type_3_label_str = "";
			foreach ($this->sub_type_3_fields as $name) {
				if(!$g->model[$name]) continue;

				$sub_type_3_graph_data[] = $g->model[$name];
				$sub_type_3_graph_data_label[] = $name.": ".$g->model[$name];
				$sub_type_3_label_str .= $name.": ".$g->model[$name]."<br/>";
			}
			// $sub_type_3_graph_data = trim($sub_type_3_graph_data,',');
			// $sub_type_3_label_str = trim($sub_type_3_label_str,'<br/>');

			$g->current_row_html['communication'] = '<div class="row""><div class="col-md-7"> <div data-id="'.$g->model->id.'" sparkType="pie" sparkHeight="70px" class="sparkline communication"></div></div><div class="col-md-5"> <small>'.$comm_label_str."</small></div></div>";
			$g->current_row_html['subtype_1'] = '<div class="row"><div class="col-md-7"> <div data-id="'.$g->model->id.'" sparkType="pie" sparkHeight="70px" class="sparkline subtype1"></div></div><div class="col-md-5"><small>'.$sub_type_1_label_str."</small></div></div>";
			$g->current_row_html['subtype_2'] = '<div class="row"><div  class="col-md-7"> <div data-id="'.$g->model->id.'" sparkType="pie" sparkHeight="70px" class="sparkline subtype2"></div></div><div class="col-md-5"><small>'.$sub_type_2_label_str."</small></div></div>";
			$g->current_row_html['subtype_3'] = '<div class="row"><div class="col-md-7"> <div data-id="'.$g->model->id.'" sparkType="pie" sparkHeight="70px" class="sparkline subtype3"></div></div><div class="col-md-5"><small>'.$sub_type_3_label_str."</small></div></div>";

			if(count($communication_graph_data_label)){
				$g->js(true)->_selector('.sparkline.communication[data-id='.$g->model->id.']')
					->sparkline($communication_graph_data, [
						'enableTagOptions' => true,
						'tooltipFormat'=>'{{offset:offset}} ({{percent.1}}%)',
						'tooltipValueLookups'=>['offset'=>$communication_graph_data_label]
					]);
			}

			if(count($sub_type_1_graph_data_label)){
				$g->js(true)->_selector('.sparkline.subtype1[data-id='.$g->model->id.']')
					->sparkline($sub_type_1_graph_data, [
						'enableTagOptions' => true,
						'tooltipFormat'=>'{{offset:offset}} ({{percent.1}}%)',
						'tooltipValueLookups'=>['offset'=>$sub_type_1_graph_data_label]
					]);
			}

			if(count($sub_type_2_graph_data_label)){
				$g->js(true)->_selector('.sparkline.subtype2[data-id='.$g->model->id.']')
					->sparkline($sub_type_2_graph_data, [
						'enableTagOptions' => true,
						'tooltipFormat'=>'{{offset:offset}} ({{percent.1}}%)',
						'tooltipValueLookups'=>['offset'=>$sub_type_2_graph_data_label]
					]);
			}

			if(count($sub_type_3_graph_data_label)){
				$g->js(true)->_selector('.sparkline.subtype3[data-id='.$g->model->id.']')
					->sparkline($sub_type_3_graph_data, [
						'enableTagOptions' => true,
						'tooltipFormat'=>'{{offset:offset}} ({{percent.1}}%)',
						'tooltipValueLookups'=>['offset'=>$sub_type_3_graph_data_label]
					]);
			}

		});

		foreach ($this->communication_fields as $name) {
			$grid->removeColumn($name);
		}
		foreach ($this->sub_type_1_fields as $name) {
			$grid->removeColumn($name);
		}
		foreach ($this->sub_type_2_fields as $name) {
			$grid->removeColumn($name);
		}
		foreach ($this->sub_type_3_fields as $name) {
			$grid->removeColumn($name);
		}


		// /*Report Digging*/
		// $total_call_vp = $this->add('VirtualPage')->set(function($page){			
		// 	$employee_id = $this->app->stickyGET('employee_id');
		// 	$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
		// 	$all_call->addCondition('created_by_id',$employee_id)
		// 					->addCondition('communication_type','Call')
		// 					->addCondition('created_at','>=',$_GET['from_date'])
		// 					->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
		// 					;
		// 	$grid = $page->add('xepan\hr\Grid');
		// 	$grid->setModel($all_call,['to','title','description','sub_type','calling_status','sub_type_3','created_at','status','created_by']);
		// 	$grid->addPaginator(10);
		// 	$all_call->setOrder('created_at','desc');
		// 	$grid->addHook('formatRow',function($g){
		// 		$g->current_row_html['description'] = $g->model['description'];
		// 	});
		// });
		// $grid->addMethod('format_total_call',function($g,$f)use($total_call_vp){
		// 	// VP defined at top of init function
		// 	$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Total Calls',$g->api->url($total_call_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		// });
		// $grid->addFormatter('total_call','total_call');


		// $dial_call_vp = $this->add('VirtualPage')->set(function($page){
		// 	// $page->add('View_Error')->set($_GET['from_date']);
		// 	$employee_id = $this->app->stickyGET('employee_id');
		// 	$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
		// 	$all_call->addCondition('created_by_id',$employee_id)
		// 					->addCondition('communication_type','Call')
		// 					->addCondition('status','Called')
		// 					->addCondition('created_at','>=',$_GET['from_date'])
		// 					->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
		// 					;

		// 	$grid = $page->add('xepan\hr\Grid');
		// 	$grid->setModel($all_call,['from','to','to_contact_str','title','description','sub_type','calling_status','status']);
		// 	$grid->addPaginator(50);

		// 	$grid->addHook('formatRow',function($g){
		// 		$g->current_row_html['description'] = $g->model['description'];
		// 	});
		// });

		// $grid->addMethod('format_dial_call',function($g,$f)use($dial_call_vp){
		// 		// VP defined at top of init function
		// 	$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Dial Calls',$g->api->url($dial_call_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		// });

		// $grid->addFormatter('dial_call','dial_call');


		// $received_call_vp = $this->add('VirtualPage')->set(function($page){
		// 	// $page->add('View_Error')->set($_GET['from_date']);
		// 	$employee_id = $this->app->stickyGET('employee_id');
		// 	$all_call = $this->add('xepan\communication\Model_Communication_Call',['table_alias'=>'employee_commni_all_calls']);
		// 	$all_call->addCondition('created_by_id',$employee_id)
		// 					->addCondition('communication_type','Call')
		// 					->addCondition('status','Received')
		// 					->addCondition('created_at','>=',$_GET['from_date'])
		// 					->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
		// 					;

		// 	$grid = $page->add('xepan\hr\Grid');
		// 	$grid->setModel($all_call,['from','to','to_contact_str','title','description','sub_type','calling_status','status']);
		// 	$grid->addPaginator(50);

		// 	$grid->addHook('formatRow',function($g){
		// 		$g->current_row_html['description'] = $g->model['description'];
		// 	});
		// });

		// $grid->addMethod('format_received_call',function($g,$f)use($received_call_vp){
		// 		// VP defined at top of init function
		// 	$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL('Dial Calls',$g->api->url($received_call_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		// });

		// $grid->addFormatter('received_call','received_call');

		// /*Communication Subtype Digging*/
		// foreach (explode(",", $config_m['sub_type']) as $subtypes) {
		// 	// $grid->addColumn($this->app->normalizeName($subtypes));
		// 	$subtype_vp = $this->add('VirtualPage')->set(function($page)use($subtypes){
		// 		// $page->add('View_Error')->set($_GET['from_date']);
		// 		$employee_id = $this->app->stickyGET('employee_id');
		// 		$subtype_m = $page->add('xepan\communication\Model_Communication')
		// 					->addCondition('created_by_id',$employee_id)
		// 					->addCondition('sub_type',$subtypes)
		// 					->addCondition('created_at','>=',$_GET['from_date'])
		// 					->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
		// 					;

		// 		$grid = $page->add('xepan\hr\Grid');
		// 		$grid->setModel($subtype_m,['from','to','to_contact_str','title','description','sub_type','calling_status','status']);
		// 		$grid->addPaginator(50);
		// 		$grid->addHook('formatRow',function($g){
		// 			$g->current_row_html['description'] = $g->model['description'];
		// 		});
		// 	});

		// 	$grid->addMethod('format_'.$this->app->normalizeName($subtypes),function($g,$f)use($subtype_vp,$subtypes){
		// 		$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL($subtypes,$g->api->url($subtype_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		// 	});

		// 	$grid->addFormatter($this->app->normalizeName($subtypes),$this->app->normalizeName($subtypes));
		// }

		// $calling_config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		// [
		// 	'fields'=>[
		// 				'sub_type'=>'text',
		// 				'calling_status'=>'text',
		// 				],
		// 		'config_key'=>'COMMUNICATION_SUB_TYPE',
		// 		'application'=>'Communication'
		// ]);
		// $calling_config_m->tryLoadAny();

		// /*Communication Calling Status*/
		// foreach (explode(",", $calling_config_m['calling_status']) as $callingstatus) {
		// 	$callingstatus_vp = $this->add('VirtualPage')->set(function($page)use($callingstatus){
		// 		$employee_id = $this->app->stickyGET('employee_id');
		// 		$calling_m = $page->add('xepan\communication\Model_Communication')
		// 						->addCondition('created_by_id',$employee_id)
		// 						->addCondition('calling_status',$callingstatus)
		// 						->addCondition('created_at','>=',$_GET['from_date'])
		// 						->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']))
		// 						;

		// 		$grid = $page->add('xepan\hr\Grid');
		// 		$grid->setModel($calling_m,['from','to','to_contact_str','title','description','sub_type','calling_status','status']);
		// 		$grid->addPaginator(50);
		// 		$grid->addHook('formatRow',function($g){
		// 			$g->current_row_html['description'] = $g->model['description'];
		// 		});
		// 	});

		// 	$grid->addMethod('format_'.$this->app->normalizeName($callingstatus.'c'),function($g,$f)use($callingstatus_vp,$callingstatus){
		// 		$g->current_row_html[$f]= '<a href="javascript:void(0)" onclick="'.$g->js()->univ()->frameURL($callingstatus,$g->api->url($callingstatus_vp->getURL(),array('employee_id'=>$g->model->id,'from_date'=>$g->model->from_date,'to_date'=>$g->model->to_date))).'">'.$g->current_row[$f].'</a>';
		// 	});

		// 	$grid->addFormatter($this->app->normalizeName($callingstatus),$this->app->normalizeName($callingstatus."c"));
		// }	
		
		$grid->js(true)->_load('jquery.sparkline.min');

	}

}