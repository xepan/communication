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

class Model_Communication_Email extends Model_Communication_Abstract_Email{
	
	public $status=['Draft','Sent','Outbox','Received','Trashed'];

	function init(){
		parent::init();
		$this->addCondition('communication_type','Email');	
	}

	function verifyTo($to_field, $contact_id){
		$model_contact = $this->add('xepan\base\Model_Contact')->load($contact_id);
		$contact_email=$model_contact->getEmails();
		
		$to_emails=[];
		foreach (explode(',', $to_field) as $value) {
			$to_emails[]= $value;
		}
		

		$common_email = array_intersect($to_emails, $contact_email);		
		var_dump($common_email);
		exit;
		if($common_email)
			return true;
		return false;
	}
}
