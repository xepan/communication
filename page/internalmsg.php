<?php

namespace xepan\communication;

/**
* 
*/
class page_internalmsg extends \xepan\base\Page{
	public $title = "Internal Message Communication" ; 
	function init(){
		parent::init();

		$search_string = $this->app->stickyGET('search_string');
			
		// $emp->addCondition('status','Active');
		$emp = $this->add('xepan\hr\Model_Employee');
		$emp->addCondition('status','Active');
		$emp->addCondition('id','<>',$this->app->employee->id);


		$emp_nav = $this->add('xepan\communication\View_InternalMessageEmployeeList',null,'message_navigation');
		$emp_nav->setModel($emp,['name']);

		$emp_id = $this->app->stickyGET('employee_id');
		
		$msg_m = $this->add('xepan\communication\Model_Communication_AbstractMessage');
		$msg_m->setOrder('id','desc');
		$msg_m->addCondition([
			['from_raw','like','%"'.$this->app->employee->id.'"%'],
			['to_raw','like','%"'.$this->app->employee->id.'"%'],
			['cc_raw','like','%"'.$this->app->employee->id.'"%']
			]);

		if($emp_id){
			$msg_m->addCondition('from_id',[$emp_id,$this->app->employee->id]);
			$msg_m->addCondition(
				[	
					['to_raw','like','%"'.$emp_id.'"%'],
					['cc_raw','like','%"'.$emp_id.'"%'],
					['to_raw','like','%"'.$this->app->employee->id.'"%'],
					['cc_raw','like','%"'.$this->app->employee->id.'"%']
				]
			);
			// $msg_m->addCondition([['cc_raw','like','%"'.$emp_id.'"%'],['to_raw','like','%"'.$this->app->employee->id.'"%']]);
		}

			
		$com_id = $this->app->stickyGET('communication_id');
		$mode = $this->app->stickyGET('mode');

		if($search_string){
			$msg_m->addExpression('Relevance')->set('MATCH(title,description,communication_type) AGAINST ("'.$search_string.'")');
			$msg_m->addCondition('Relevance','>',0);
 			$msg_m->setOrder('Relevance','Desc');
		}
			
		$msg_list = $this->add('xepan\communication\View_Lister_InternalMSGList',null,'message_lister');
		$msg_list->setModel($msg_m);
		$msg_list->add('xepan\base\Controller_Avatar',['options'=>['size'=>50,'border'=>['width'=>0]],'name_field'=>'contact']);
		$paginator = $msg_list->add('xepan\base\Paginator',['ipp'=>10]);
		$paginator->setRowsPerPage(10);
		//trigger reload
		$msg_list->addClass('xepan-internal-message-trigger-reload');
		// $msg_list->js('reload')->reload();

		$compose_msg = $this->add('xepan\communication\View_ComposeMessagePopup',['employee_id'=>$emp_id,'communication_id'=>$com_id,'mode'=>$mode],'message_compose_view');

		$emp_nav->js('click',[
				$compose_msg->js()->html(' ')
					->reload(['employee_id'=>$this->js()->_selectorThis()->data('id')]),
				$msg_list->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
					->reload(['employee_id'=>$this->js()->_selectorThis()->data('id')]),	
			])->_selector('.internal-conversion-emp-list');

		$msg_list->js('click',
				$compose_msg->js()
				->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
				->reload(
						[
							'communication_id'=>$this->js()->_selectorThis()->data('id'),
							'mode'=>'msg-reply'
						]
					)
				)->_selector('.do-msg-reply');

		$msg_list->js('click',
				$compose_msg->js()
				->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
				->reload(
						[
							'communication_id'=>$this->js()->_selectorThis()->data('id'),
							'mode'=>'msg-reply-all'
						]
					)
				)->_selector('.do-msg-reply-all');

		$msg_list->js('click',
				$compose_msg->js()
				->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
				->reload(['communication_id'=>$this->js()->_selectorThis()->data('id'),'mode'=>'msg-fwd']))->_selector('.do-msg-fwd');

		/*filter Form */
		$f = $this->add('Form',null,'form',['form\empty']);
		$f->addField('line','search');
		$f->addSubmit('Search');
		if($f->isSubmitted()){
			$f->js(null,$msg_list->js()->reload(['search_string'=>$f['search']]))->execute();
		}

	}



	function defaultTemplate(){
		return['page/internalmsg'];
	}

}