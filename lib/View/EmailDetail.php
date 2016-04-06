<?php
namespace xepan\communication;
class View_EmailDetail extends \View{
	function init(){
		parent::init();
	}
	function setModel($model){
		$m=parent::setModel($model);
		$to_raw=$m['to_raw'];
		$to_lister=$this->add('CompleteLister',null,'to_lister',['view/emails/email-detail','to_lister']);
		// var_dump($m['to_raw']);
		// exit;
		$to_lister->setSource($to_raw);

		$cc_raw=$m['cc_raw'];
		$cc_lister=$this->add('CompleteLister',null,'cc_lister',['view/emails/email-detail','cc_lister']);
		$cc_lister->setSource($cc_raw);

		$this->template->setHTML('email_body',$model['description']);
	
		return $m;
	}
	function defaultTemplate(){
		return ['view/emails/email-detail'];
	}
}