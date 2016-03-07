<?php

/**
* description: ATK Model
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;

class Model_Communication_Email extends Model_Communication{

	function init(){
		parent::init();
		
		$this->addCondition('communication_type','Email');		
	}
}
