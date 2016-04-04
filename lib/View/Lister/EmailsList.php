<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function formatRow(){
		if($this->model['is_starred']){
			$this->current_row['starred']='starred';
		}else{
			$this->current_row['starred']='';
		}

		$seen_by=$this->model->ref('extra_info')->get('seen_by');
		
		if($seen_by){
			$this->current_row['unread']='';
		}else{
			$this->current_row['unread']='unread';
		}
		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}