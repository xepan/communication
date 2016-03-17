<?php
namespace xepan\communication;
class View_EmailDetail extends \View{
	function init(){
		parent::init();
	}
	function setModel($model){
		$m=parent::setModel($model);
		$this->template->setHTML('email_body',$model['description']);
		return $m;
	}
	function defaultTemplate(){
		return ['view/emails/email-detail'];
	}
}