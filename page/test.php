<?php

namespace xepan\communication;

/**
* 
*/
class page_test extends \xepan\base\Page{
	
	function init(){
		parent::init();

		$comm = $this->add('xepan\communication\View_Communication');
		$comm->setCommunicationsWith($this->app->employee);

	}
}