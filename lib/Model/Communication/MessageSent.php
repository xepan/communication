<?php

namespace xepan\communication;


/**
* 
*/
class Model_Communication_MessageSent extends \xepan\communication\Model_Communication_AbstractMessage{

	function init(){
		parent::init();
		$this->addCondition('status','Sent');		
		$this->addCondition('direction','Out');
	}
}