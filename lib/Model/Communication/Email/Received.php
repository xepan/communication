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
class Model_Communication_Email_Received extends Model_Communication_Email{

	function init(){
		parent::init();
		
		$this->addCondition('status','Received');		
		$this->addCondition('direction','In');

	}

	function findContact($save=false){
		if(!is_array($this['from_raw'])) {
			$this['from_raw'] = json_decode($this['from_raw'],true);
		}

		$email = $this['from_raw']['email'];
		$contact_emails = $this->add('xepan\base\Model_Contact_Info');
		$contact_emails->addCondition('value',$email);
		$contact_emails->tryLoadAny();

		if($contact_emails->loaded()){
			$this['from_id'] = $contact_emails['contact_id'];
			if($save) $this->save();
		}
	}
}
