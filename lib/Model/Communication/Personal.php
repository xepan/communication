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

class Model_Communication_Personal extends Model_Communication {
	
	public $status=['Commented'];

	function init(){
		parent::init();
		$this->addCondition('communication_type','Personal');	
		$this->getElement('status')->defaultValue('Personal');
	}

	function setFrom($employee_id,$employee){
		$from_raw = $this['from_raw'];
		if(!is_array($from_raw))
			$from_raw = json_decode($from_raw,true);
		$tmp=['name'=>$employee,'number'=>$employee_id];
		$tmp = array_merge($tmp,$from_raw);
		$this->set('from_raw',$tmp);
	}

	function addTo($contact_id,$contact){
		// $tmp = $this['to_raw'];
		$to=['name'=>$contact,'number'=>$contact_id];
		$tmp[] = $to;
		$this->set('to_raw',$tmp);
	}

	function setSubject($subject){
		$this['title']=$subject;
	}		

	function setBody($body){
		$this['description']=$body;
	}

	function send($email_settings,$notify_to=''){
		$this->save();
		if(trim($notify_to)){
			$this->notifyToEmail($email_settings,$notify_to);
		}
	}	

	function verifyTo(){
		return true;
	}

	function notifyToEmail($email_setting,$to_emails=''){
		$notify = $this->add('xepan\communication\Model_Communication_Email_Sent');
		$notify->setFrom($email_setting['from_email'],$email_setting['from_name']);
		
		foreach (explode(",", $to_emails) as $to) {
			$notify->addTo(trim($to));
		}

		$notify->setSubject($this['title']);
		$notify->setBody($this['description']);
		$notify->findContact('to');

		$notify->send($email_setting);
	}
}
