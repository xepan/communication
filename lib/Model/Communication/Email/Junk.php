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
class Model_Communication_Email_Junk extends Model_Communication_Email{

	function init(){
		parent::init();
		
		$this->addCondition('status','Junk');		
		// $this->addCondition('direction','In');
	}
}
