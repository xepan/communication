<?php
namespace xepan\communication;

class page_emails extends \Page{

	public $title="Emails";

	function init(){
		parent::init();

	}
	
	function defaultTemplate(){
		return['page/emails'];
	}
}