<?php

namespace xepan\communication;

class View_Widget_CommunicationLister extends \xepan\base\Grid{
	
	function init(){
		parent::init();
		
	}

	function defaultTemplate(){
		return['view\widget\communication\lister'];
	}
}