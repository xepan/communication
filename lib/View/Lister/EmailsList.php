<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}