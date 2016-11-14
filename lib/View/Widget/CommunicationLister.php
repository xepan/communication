<?php

namespace xepan\communication;

class View_Widget_CommunicationLister extends \xepan\base\Grid{
	
	function init(){
		parent::init();
		$communication = $this->add('xepan\communication\Model_Communication');
	}

	function defaultTemplate(){
		return['view\widget\communication\lister'];
	}
}