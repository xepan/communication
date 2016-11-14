<?php

namespace xepan\communication;

class View_Widget_CommunicationLister extends \xepan\base\Grid{
	
	function init(){
		parent::init();
		$m = $this->model;
	}

	function formatRow(){
		$m = $this->model;
		$this->current_row_html['description'] = $m['description'];
		return parent::formatRow();
	}

	function setModel($model){
		parent::setModel($model);
	}
	function defaultTemplate(){
		return['view\widget\communication\lister'];
	}
}