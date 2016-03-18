<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function formatRow(){
		if($this->model['is_starred']){
			$this->current_row['starred']='starred';
		}else{
			$this->current_row['starred']='';
		}
		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}