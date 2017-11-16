<?php

/**
* description: epan may have many Email Settings for sending and receiving enails.
* Since xEpan is primarily for cloud multiuser SaaS. Email settings are considered as base
* and included in Epan, not in any top layer Application.
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;

class Model_Communication_ActiveEmailSetting extends Model_Communication_EmailSetting{

	function init(){
		parent::init();
		$this->addCondition('is_active',true);
	}
}
