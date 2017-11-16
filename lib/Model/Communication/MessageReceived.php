<?php

namespace xepan\communication;


/**
* 
*/
class Model_Communication_MessageReceived extends \xepan\communication\Model_Communication_AbstractMessage{

	function init(){
		parent::init();
		$this->addCondition('status','Received');		
		$this->addCondition('direction','In');

	}
}