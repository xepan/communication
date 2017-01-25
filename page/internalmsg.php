<?php

namespace xepan\communication;

/**
* 
*/
class page_internalmsg extends \xepan\base\Page{
	public $title = "Internal Message Communication" ; 
	function init(){
		parent::init();

		// $emp->addCondition('status','Active');
		$emp = $this->add('xepan\hr\Model_Employee');
		$emp->addCondition('status','Active');
		$emp->addCondition('id','<>',$this->app->employee->id);

		$emp_nav = $this->add('xepan\communication\View_InternalMessageEmployeeList',null,'message_navigation');
		$emp_nav->setModel($emp,['name']);


		$emp_id = $this->app->stickyGET('employee_id');
		
		$msg_m = $this->add('xepan\communication\Model_Communication_AbstractMessage');
		$msg_m->addCondition([
			['from_raw','like','%"'.$this->app->employee->id.'"%'],
			['to_raw','like','%"'.$this->app->employee->id.'"%'],
			['cc_raw','like','%"'.$this->app->employee->id.'"%']
			]);

		if($emp_id){
			$employee = $this->add('xepan\hr\Model_Employee');
			$employee->load($emp_id);
			$msg_m->addCondition([
			['from_raw','like','%"'.$employee->id.'"%'],
			['to_raw','like','%"'.$employee->id.'"%'],
			['cc_raw','like','%"'.$employee->id.'"%']
			]);
		}


		$msg_m->setOrder('id','desc');
		$msg_list = $this->add('xepan\communication\View_Lister_InternalMSGList',null,'message_lister');
		$msg_list->setModel($msg_m);
		$msg_list->add('xepan\base\Controller_Avatar',['options'=>['size'=>50,'border'=>['width'=>0]],'name_field'=>'contact']);
		$paginator = $msg_list->add('xepan\base\Paginator',['ipp'=>10]);
		$paginator->setRowsPerPage(10);
		//trigger reload
		$msg_list->addClass('xepan-internal-message-trigger-reload');
		// $msg_list->js('reload')->reload();

		$compose_msg = $this->add('xepan\communication\View_ComposeMessagePopup',['employee_id'=>$emp_id],'message_compose_view');

		$emp_nav->js('click',[
				$compose_msg->js()->html(' ')
					->reload(['employee_id'=>$this->js()->_selectorThis()->data('id')]),
				$msg_list->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
					->reload(['employee_id'=>$this->js()->_selectorThis()->data('id')]),	
			])->_selector('.internal-conversion-emp-list');

	}



	function defaultTemplate(){
		return['page/internalmsg'];
	}

}