<?php
namespace xepan\communication;

class View_Lister_InternalMSGList extends \CompleteLister{
	function init(){
		parent::init();
		$this->js('reload')->reload();
		$vp = $this->add('VirtualPage');
	  	$vp->set(function($vp){
			$this->app->stickyGET('mark_id');
			$mark=$this->add('xepan\communication\Model_Communication_AbstractMessage');
			$mark->load($_POST['mark_id']);

			$einfo = $mark['extra_info'];
			$einfo['seen_by'][] = $this->app->employee->id;
			$mark['extra_info'] = $einfo;
			$mark->save();
			exit;
	   	});

		$this->js('click',
				[
					$this->js()->_selectorThis()->parent()->find('.text')->toggle('hide'),
					$this->js()->_selectorThis()->removeClass('unread'),
					$this->js()->_selectorThis()->univ()->ajaxec($vp->getURL(),['mark_id'=>$this->js()->_selectorThis()->data('id')])
				])
				->_selector('.name');	
	}
	function setModel($model){
		$m = parent::setModel($model);
		return $m;
	}

	function formatRow(){
		$attach=$this->add('xepan\communication\View_Lister_Attachment',null,'Attachments');
		$attach->setModel('xepan\communication\Communication_Attachment')->addCondition('communication_id',$this->model->id);
		// $a = $this->add('xepan\communication\Model_Communication_Attachment')->addCondition('communication_id',$this->model->id);
		$this->current_row_html['Attachments'] = $attach->getHtml();
		
		$to_array=[];
		foreach ($this->model['to_raw'] as $to) {
			$to_array[]=$to['name'];
		}
		$this->current_row_html['to_name'] = implode(", ", $to_array);
		$this->current_row_html['from_name'] = $this->model['from_raw']['name'];

		
		if($this->model['from_id'] === $this->app->employee->id)
			$this->current_row_html['position'] = "left";
		else
			$this->current_row_html['position'] = "right";
		
		// if($this->model['is_starred']){
		// 	$this->current_row['starred']='starred';
		// }else{
		// 	$this->current_row['starred']='';
		// }

		$einfo =$this->model['extra_info'];
		if(isset($einfo['seen_by']) And is_array($einfo['seen_by'])){
			if(in_array($this->app->employee->id, $einfo['seen_by'])){
				$this->current_row['unread']='';
			}else{
				$this->current_row['unread']='unread';
			}
		}

		$this->current_row_html['message']  = strip_tags($this->model['description']);
		
		
		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/internalmsglist'];
	}
}