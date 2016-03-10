<?php
namespace xepan\communication;

class View_EmailNavigation extends \View{
	function init(){
		parent::init();

	}

	function defaultTemplate(){
		return ['view/emails/navigation'];
	}
}