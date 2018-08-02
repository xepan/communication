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

class Model_Communication_DefaultSMSSetting extends Model_Communication_SMSSetting{

	function init(){
		parent::init();

		$model = $this->add('xepan\communication\Model_Config_DefaultEmailSmsAndOther');
		$model->tryLoadAny();
		if($model['default_sms']){
			$this->addCondition('id',$model['default_sms']);
		}
		
	}
}
