<?php
namespace xepan\communication;

class View_InternalMessageEmployeeList extends \CompleteLister{
	function init(){
		parent::init();
	}

	function getJSID(){
		return 'email-nav-items';
	}

	function formatRow(){
		if($this->model['check_login'] != '0')
			$this->current_row_html['color'] = "#8bc34a";
		else
			$this->current_row_html['color'] = "red";
		
		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/emails/internalmessageemployeelist'];
	}
}