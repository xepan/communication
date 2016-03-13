<?php
namespace xepan\communication;

class View_EmailNavigation extends \View{
	
	function getJSID(){
		return 'email-nav-items';
	}

	function defaultTemplate(){
		return ['view/emails/navigation'];
	}
}