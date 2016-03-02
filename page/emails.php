<?php
namespace xepan\communication;

class page_emails extends \Page{
	function init(){
		parent::init();

	}
	
	function defaultTemplate(){
		return['view/emails'];
	}
}