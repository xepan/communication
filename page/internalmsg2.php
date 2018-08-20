<?php

namespace xepan\communication;

/**
* 
*/
class page_internalmsg2 extends \xepan\base\Page{
	public $title = "Internal Message Communication" ; 
	function init(){
		parent::init();

	}

	function defaultTemplate(){
		return['page/internalmsg2'];
	}

}