<?php
namespace xepan\communication;

class View_EmailHeader extends \View{
	function init(){
		parent::init();

	}

	function defaultTemplate(){
		return ['view/emails/header'];
	}
}