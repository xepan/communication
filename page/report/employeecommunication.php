<?php

namespace xepan\communication;

class page_report_employeecommunication extends \xepan\base\Page{

	public $title = "Employee Communication Reports";
	public $sub_type_1_fields;
	public $sub_type_1_norm_unnorm_array=[];
	public $sub_type_2_fields;
	public $sub_type_2_norm_unnorm_array=[];
	public $sub_type_3_fields;
	public $sub_type_3_norm_unnorm_array=[];
	public $communication_fields;
	public $communication_type_value = ['Email'=>'Email','Comment'=>'Comment','Call'=>'Call','Personal'=>'Meeting','Sms'=>'SMS','TeleMarketing'=>'TeleMarketing'];
	public $config_m;

	function init(){
		parent::init();
		
		$this->config_m = $this->add('xepan\communication\Model_Config_SubType');
		$this->config_m->tryLoadAny();

		// subtype 1
		foreach(explode(",", $this->config_m['sub_type']) as $subtypes) {
			$subtype_name = $this->app->normalizeName(trim($subtypes));
			$this->sub_type_1_norm_unnorm_array[$subtype_name] = $subtypes;
		}

		foreach(explode(",", $this->config_m['calling_status']) as $subtypes) {
			$subtype_name = $this->app->normalizeName(trim($subtypes));
			$this->sub_type_2_norm_unnorm_array[$subtype_name] = $subtypes;
		}

		foreach(explode(",", $this->config_m['sub_type_3']) as $subtypes) {
			$subtype_name = $this->app->normalizeName(trim($subtypes));
			$this->sub_type_3_norm_unnorm_array[$subtype_name] = $subtypes;
		}		

	}

	function page_index(){
		// parent::init();

		$config_m = $this->config_m;

		$emp_id = $this->app->stickyGET('employee_id');
		$this->from_date = $from_date  = $this->app->stickyGET('from_date')?:$this->app->today;
		$this->to_date = $to_date = $this->app->stickyGET('to_date')?:$this->app->today;
		$department = $this->app->stickyGET('department');

		$post_model = $this->app->employee->ref('post_id');
		
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
				
		$dept_field = $form->addField('xepan\base\DropDown','department');
		$model_department = $this->add('xepan\hr\Model_Department');


		switch ($post_model['permission_level']) {
			case "Department":
				$model_department->addCondition('id',$this->app->employee['department_id']);
				$dept_field->set($this->app->employee['department_id']);
				$dept_field->setAttr('disabled',true);
				$department = $this->app->employee['department_id'];

				$employee_model->addCondition('department_id',$this->app->employee['department_id']);
				break;
			case ($post_model['permission_level'] == 'Individual' || $post_model['permission_level'] == 'Sibling'):
				$model_department->addCondition('id',$this->app->employee['department_id']);
				$dept_field->set($this->app->employee['department_id']);
				$dept_field->setAttr('disabled',true);
				$department = $this->app->employee['department_id'];

				$employee_model->addCondition('id',$this->app->employee->id);
				$emp_field->set($this->app->employee->id);
				$emp_field->other_field->setAttr('disabled',true);
				$emp_id = $this->app->employee->id;
				break;
		}

		$emp_field->setModel($employee_model);
		$dept_field->setModel($model_department);
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
		$model_field_array = ['name','unique_lead','communication','total_email','total_comment','total_meeting','total_sms','total_telemarketing','total_call','dial_call','received_call','unique_leads_from','unique_leads_to','attended_others_meeting'];

		// sub type 1
		$emp_model->addExpression('unique_lead')->set('""')->caption('comm. with unique lead');
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

		// $this->app->print_r($this->sub_type_1_norm_unnorm_array);
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
								->addCondition('sub_type_3',$sub_type_3)
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->api->nextDate($this->to_date))
								->count();
				});
		}

		$grid->setModel($emp_model,$model_field_array);
		$order = $grid->addOrder();
		$grid->addpaginator(10);
		// $grid->template->tryDel('Pannel');
		$grid->add("misc\Export");

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

			// unique lead count
			$temp = array_unique (array_merge (explode(",", $g->model['unique_leads_from']), explode(",", $g->model['unique_leads_to'])));
			$unique_lead_count = count($temp);
			if($unique_lead_count == 1 && !$temp[0]){
				$unique_lead_count = 0;
			}
			$g->current_row_html['unique_lead'] = $unique_lead_count;

			// $communication_graph_data = $g->model['total_email'].",".$g->model['total_call'].",".$g->model['total_telemarketing'].",".$g->model['total_sms'].",".$g->model['total_meeting'].",".$g->model['total_comment'];
			$communication_graph_data = [];
			$communication_graph_data_label = [];
			$comm_label_str = "";

			// communication type
			foreach ($this->communication_type_value as $key => $value) {
				$total_field_name = "total_".strtolower($value);

				if($g->model[$total_field_name]){
					$comm_label_str .= '<a href="javascript:void(0);" onclick="'.$g->js()->univ()->frameURL($value.' communication history of employee '.$g->model['name'],$g->api->url('./commdegging',array('from_date'=>$this->from_date,'to_date'=>$this->to_date,'selected_employee_id'=>$g->model['id'],'communication_type'=>$key))).'">'.$value.": ".$g->model[$total_field_name].'</a><br/>';
					$communication_graph_data[] = $g->model[$total_field_name];

					if($total_field_name == "total_call")
						$communication_graph_data_label[] = $value.": ".$g->model[$total_field_name].' ( Dial: '.$g->model['dial_call'].", Received: ".$g->model['received_call'].") ";
					else
						$communication_graph_data_label[] = $value.": ".$g->model[$total_field_name];
				}
			}

			// communication based other count status 
			$comm_type_array = ['dial_call'=>['communication_type'=>'Call','status'=>'Called'],
								'received_call'=>['communication_type'=>'Call','status'=>'Received']
							];
			foreach ($comm_type_array as $field_name => $value) {
				if($g->model[$field_name]){
					$comm_label_str .= '<a href="javascript:void(0);" onclick="'.$g->js()->univ()->frameURL($field_name.' communication history of employee '.$g->model['name'],$g->api->url('./commdegging',array('from_date'=>$this->from_date,'to_date'=>$this->to_date,'selected_employee_id'=>$g->model['id'],'communication_type'=>$value['communication_type'],'communication_status'=>$value['status']))).'">'.$field_name.": ".$g->model[$field_name].'</a><br/>';
				}
			}

			if($g->model['attended_others_meeting']){
				$comm_label_str .= '<a href="javascript:void(0);" onclick="'.$g->js()->univ()->frameURL('Join Meeting communication history of employee '.$g->model['name'],$g->api->url('./commdegging',array('from_date'=>$this->from_date,'to_date'=>$this->to_date,'selected_employee_id'=>$g->model['id'],'communication_type'=>'Meeting Join'))).'">'."Meeting Join: ".$g->model['attended_others_meeting'].'</a><br/>';
			}



			$sub_type_1_label_str = "";
			$sub_type_1_graph_data = [];
			$sub_type_1_graph_data_label = [];
			foreach ($this->sub_type_1_fields as $name) {
				if(!$g->model[$name]) continue;

				$sub_type_1_graph_data[] = $g->model[$name];
				$sub_type_1_graph_data_label[] = $name.": ".$g->model[$name];
				$sub_type_1_label_str .= '<a href="javascript:void(0);" onclick="'.$g->js()->univ()->frameURL($name.' communication history of employee '.$g->model['name'],$g->api->url('./commdegging',array('from_date'=>$this->from_date,'to_date'=>$this->to_date,'selected_employee_id'=>$g->model['id'],'sub_type_1'=>$name))).'"> '.$name.": ".$g->model[$name].'</a><br/>';
			}


			$sub_type_2_label_str = "";
			$sub_type_2_graph_data = [];
			$sub_type_2_graph_data_label = [];
			foreach ($this->sub_type_2_fields as $name) {
				if(!$g->model[$name]) continue;

				$sub_type_2_graph_data[] = $g->model[$name];
				$sub_type_2_graph_data_label[] = $name.": ".$g->model[$name];
				$sub_type_2_label_str .= '<a href="javascript:void(0);" onclick="'.$g->js()->univ()->frameURL($name.' communication history of employee '.$g->model['name'],$g->api->url('./commdegging',array('from_date'=>$this->from_date,'to_date'=>$this->to_date,'selected_employee_id'=>$g->model['id'],'sub_type_2'=>$name))).'"> '.$name.": ".$g->model[$name].'</a><br/>';
			}
			

			$sub_type_3_graph_data = [];
			$sub_type_3_graph_data_label = [];
			$sub_type_3_label_str = "";
			foreach ($this->sub_type_3_fields as $name) {
				if(!$g->model[$name]) continue;

				$sub_type_3_graph_data[] = $g->model[$name];
				$sub_type_3_graph_data_label[] = $name.": ".$g->model[$name];
				$sub_type_3_label_str .= '<a href="javascript:void(0);" onclick="'.$g->js()->univ()->frameURL($name.' communication history of employee '.$g->model['name'],$g->api->url('./commdegging',array('from_date'=>$this->from_date,'to_date'=>$this->to_date,'selected_employee_id'=>$g->model['id'],'sub_type_3'=>$name))).'"> '.$name.": ".$g->model[$name].'</a><br/>';
			}
			

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
	
		$grid->removeColumn('unique_leads_to');
		$grid->removeColumn('attended_others_meeting');
		$grid->removeColumn('unique_leads_from');

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
		
		$grid->js(true)->_load('jquery.sparkline.min');

	}

	function page_commdegging(){
		$employee_id = $this->app->stickyGET('selected_employee_id');
		$from_date = $this->app->stickyGET('from_date');
		$to_date = $this->app->stickyGET('to_date');
		$communication_type = $this->app->stickyGET('communication_type');
		$communication_status = $this->app->stickyGET('communication_status');
		$sub_type_1 = $this->app->stickyGET('sub_type_1');
		$sub_type_2 = $this->app->stickyGET('sub_type_2');
		$sub_type_3 = $this->app->stickyGET('sub_type_3');

		$comm_model = $this->add('xepan\communication\Model_Communication');
		$comm_model->addCondition('created_by_id',$employee_id);

		if($communication_type == "Meeting Join"){
			$rel_emp_model = $this->add('xepan\communication\Model_CommunicationRelatedEmployee',['table_alias'=>'employee_commni_other_meeting']);
			$rel_emp_model->addCondition('employee_id',$employee_id)
					// ->addCondition('comm_created_at','>=',$from_date)
					// ->addCondition('comm_created_at','<',$this->api->nextDate($to_date))
					;
			$comm_ids = $rel_emp_model->_dsql()->del('fields')->field('communication_id')->getAll();
			$comm_ids = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($comm_ids)),false);

			$comm_model->addCondition('id','in',$comm_ids);
		}elseif($communication_type)
			$comm_model->addCondition('communication_type',$communication_type);

		if($sub_type_1)
			$comm_model->addCondition('sub_type',trim($this->sub_type_1_norm_unnorm_array[$sub_type_1]));
		
		if($sub_type_2)
			$comm_model->addCondition('calling_status',trim($this->sub_type_2_norm_unnorm_array[$sub_type_2]));
		
		if($sub_type_3)
			$comm_model->addCondition('sub_type_3',trim($this->sub_type_3_norm_unnorm_array[$sub_type_3]));
		
		if($from_date)
			$comm_model->addCondition('created_at','>=',$from_date);
		if($to_date)
			$comm_model->addCondition('created_at','<',$this->app->nextDate($to_date));

		// if($communication_status)
		// 	$comm_model->addCondition('status',$communication_status);

		$comm_model->setOrder('id','desc');

		$form = $this->add('Form');
		$layout_array = [];
		if(!$communication_type)
			$layout_array['communication_type'] = 'Filter~c1~3';
		// else
		// 	$layout_array['communication_status'] = 'Filter~c1~3';

		if(!$sub_type_1)
			$layout_array['sub_type_1~'.($this->config_m['sub_type_1_label_name']?:"Sub Type 1")] = 'Filter~c2~3';
		if(!$sub_type_2)
			$layout_array['sub_type_2~'.($this->config_m['sub_type_2_label_name']?:"Sub Type 2")] = 'Filter~c3~3';
		if(!$sub_type_3)
			$layout_array['sub_type_3~'.($this->config_m['sub_type_3_label_name']?:"Sub Type 3")] = 'Filter~c4~3';

		$layout_array['FormButtons~&nbsp;'] = 'c5~3';

		$form->add('xepan\base\Controller_FLC')
			->showLables(true)
			->addContentSpot()
			->makePanelsCoppalsible(true)
			->layout($layout_array);
				

		if(!$communication_type){
			$form->addField('DropDown','communication_type')->setValueList([
					'Email'=>'Email',
					'Comment'=>'Comment',
					'Call'=>'Call',
					'Personal'=>'Personal',
					'SMS'=>'SMS',
					'TeleMarketing'=>'TeleMarketing'
				])->setEmptyText('Please Select');
		}
		// temporary commenting
		// else{
		// 	$data = $this->app->db->dsql()->expr('SELECT DISTINCT(status) FROM communication WHERE communication_type = "'.$communication_type.'"')->get();
		// 	$list = [];
		// 	foreach ($data as $key => $value) {
		// 		if(!trim($value['status'])) continue;
		// 		$list[$value['status']] = $value['status'];
		// 	}
		// 	$form->addField('DropDown','communication_status')->setEmptyText('Please Select')->setValueList($list);
		// }
		// $this->config_m['sub_type_1_label_name']
		if(!$sub_type_1){
			$form->addField('DropDown','sub_type_1')->setValueList($this->sub_type_1_norm_unnorm_array)->setEmptyText('Please Select ...');
		}
		if(!$sub_type_2){
			$form->addField('DropDown','sub_type_2')->setValueList($this->sub_type_2_norm_unnorm_array)->setEmptyText('Please Select ...');
		}
		if(!$sub_type_3){
			$form->addField('DropDown','sub_type_3')->setValueList($this->sub_type_3_norm_unnorm_array)->setEmptyText('Please Select ...');
		}

		$form->addSubmit('Filter')->addClass('btn btn-primary');

		$grid = $this->add('xepan\base\Grid');
		$grid->template->tryDel('Pannel');
		$grid->setModel($comm_model,['title','description','created_at','from','to','created_by','sub_type','calling_status','sub_type_3','status']);
		$grid->addPaginator(25);


		if($form->isSubmitted()){
			$reload_param = [];

			if(!$communication_type)
				$reload_param['communication_type'] = $form['communication_type'];
			// else
			// 	$reload_param['communication_status'] = $form['communication_status'];
			if(!$sub_type_1)
				$reload_param['sub_type_1'] = $form['sub_type_1'];
			if(!$sub_type_2)
				$reload_param['sub_type_2'] = $form['sub_type_2'];
			if(!$sub_type_3)
				$reload_param['sub_type_3'] = $form['sub_type_3'];

			$grid->js()->reload($reload_param)->execute();
		}

	}

}