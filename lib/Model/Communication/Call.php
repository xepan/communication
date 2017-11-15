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

class Model_Communication_Call extends Model_Communication {
	
	public $status=['Called','Received'];

	function init(){
		parent::init();
		$this->addCondition('communication_type','Call');	
		$this->getElement('status')->defaultValue('Called');

		$this->addHook('beforeSave',function($m){
			if($m['status']=='Called') 
				$m['direction']='Out';
			else
				$m['direction']='In';
		});
	}

	function setFrom($number,$person){
		$from_raw = $this['from_raw'];
		if(!is_array($from_raw))
			$from_raw = json_decode($from_raw,true);
		$tmp=['name'=>$person,'number'=>$number];
		$tmp = array_merge($tmp,$from_raw);
		$this->set('from_raw',$tmp);
	}

	function addTo($number,$name=null){
		// $tmp = $this['to_raw'];
		$to=['name'=>$name,'number'=>$number];
		$tmp[] = $to;
		$this->set('to_raw',$tmp);
	}

	function addCc($number,$name=null){
		$this->addTo($number,$name);
	}

	function addBcc($number, $name=null){
		$this->addTo($number,$name);
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


	function verifyTo($to_field, $contact_id){
		$model_phone = $this->add('xepan\base\Model_Contact_Phone');
		$model_phone->addCondition('contact_id',$contact_id);
		$model_phone->addCondition('value','in',$to_field);
		$model_phone->tryLoadAny();

		if($model_phone->loaded())
			// throw new \Exception("loaded", 1);
			return true;
		return false;

	}

	function findContact($field='from',$save=false){
		if(!is_array($this[$field.'_raw'])) {
			$this[$field.'_raw'] = json_decode($this[$field.'_raw'],true);
		}

		if($field=='from')
			$numbers = [['number'=>$this[$field.'_raw']['number']]];
		else
			$numbers = $this[$field.'_raw'];

		foreach ($numbers as $num) {
			$contact_phones = $this->add('xepan\base\Model_Contact_Info');
			$contact_phones->addCondition('value',$num['number']);
			$contact_phones->tryLoadAny();

			if($contact_phones->loaded()){
				$this[$field.'_id'] = $contact_phones['contact_id'];
				if($save) $this->save();
				return true;
			}
		}

		return false;
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
