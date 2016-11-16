<?php
namespace xepan\communication;

class View_InternalMessageEmployeeList extends \CompleteLister{
	function init(){
		parent::init();
	}

	function getJSID(){
		return 'email-nav-items';
	}

	function defaultTemplate(){
		return ['view/emails/internalmessageemployeelist'];
	}
}