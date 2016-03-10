<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function init(){
		parent::init();
	}
	// function setModel($m){
	// 	$this->template->trySet('from_raw',$m['from_raw']);
	// 	parent::setModel($m);
	// }
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}