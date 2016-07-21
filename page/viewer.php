<?php

namespace xepan\communication;

class page_viewer extends \xepan\base\Page
{
	
	function init(){
		parent::init();

		$this->add('xepan\communication\View_CommunicationViewer');	

	}
}