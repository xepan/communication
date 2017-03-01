<?php
namespace xepan\communication;

class View_EmailsTopMenu extends \View{
	function init(){
		parent::init();

	}

	function defaultTemplate(){
		return ['view/emails/header-tool'];
	}
}