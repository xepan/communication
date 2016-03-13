<?php
namespace xepan\communication;

class View_Lister_EmailLabel extends \CompleteLister{

	function getJSID(){
		return 'email-nav-labels-wrapper';
	}
	function defaultTemplate(){
		return ['view/emails/label'];
	}
}