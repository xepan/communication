<?php
namespace xepan\communication;
class View_EmailDetail extends \View{
	function init(){
		parent::init();
	}
	function defaultTemplate(){
		return ['view/emails/email-detail'];
	}
}