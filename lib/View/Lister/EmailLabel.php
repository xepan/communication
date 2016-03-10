<?php
namespace xepan\communication;

class View_Lister_EmailLabel extends \CompleteLister{
	function init(){
		parent::init();
	}
	function setModel($m){
		parent::setModel($m);
	}
	function defaultTemplate(){
		return ['view/emails/label'];
	}
}