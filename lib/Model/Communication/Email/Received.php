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

	function reply($email_setting){
		if(!$this->loaded())
			return false;	

		$subject=$email_setting['email_subject'];
		$message=$email_setting['email_body'];

		$getEmail=$this->getReplyEmailFromTo();
		$reply = $this->add('xepan\communication\Model_Communication_Email_Sent');	
		$reply->setfrom($email_setting['from_email'],$email_setting['from_name']);
		
		foreach ($getEmail['to'] as $to_email) {
			$reply->addTo($to_email['email']);
		}
		foreach ($getEmail['cc'] as $cc_email) {
			$reply->addCc($cc_email['email']);
		}
		foreach ($getEmail['bcc'] as $bcc_email) {
			$reply->addBcc($bcc_email['email']);
		}
		$reply->findContact('to');
		
		$reply->setSubject($subject);
		$reply->setBody($message);
		$reply->send($email_setting);
	}
}
