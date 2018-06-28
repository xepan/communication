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

class Model_Communication_SMS extends Model_Communication {
	
	public $status=['Draft','Sent','Outbox','Received','Trashed'];

	function init(){
		parent::init();
		$this->addCondition('communication_type','Sms');
		$this->addCondition('direction','Out');
		$this->getElement('status')->defaultValue('Draft');
	}

	function setFrom($number,$person){
		$tmp=['name'=>$person,'number'=>$number];
		$tmp = array_merge($tmp,$this['from_raw']);
		$this->set('from_raw',$tmp);
	}

	function addTo($number,$name=null){
		$tmp = $this['to_raw'];
		$to=['name'=>$name,'number'=>$number];
		$tmp[] = $to;
		$this->set('to_raw',$tmp);
		
	}

	function setSubject($subject){
		$this['title']=$subject;
	}		

	function setBody($body){
		$this['description']=$body;
	}

	function send($sms_settings=null){
		if($sms_settings && is_numeric($sms_settings)) $sms_settings = $this->add('xepan\communication\Model_Communication_SMSSetting')->load($sms_settings);
		$c = $this->add('xepan\communication\Controller_Sms',['debug'=>false]);

		$reply=[];
		foreach ($this['to_raw'] as $to) {
			$reply[] = $c->sendMessage(trim($to['number']),$this['description'],$sms_settings);
			$this['description'] .= "<br/>===== Gateway Reply for (".$to['number'].") =====<br/>".$reply[count($reply)-1];
		}
		

		$this->post();
		return $reply;
	}

	function post(){
		$this['status']='Sent';
		$this->saveAs('xepan\communication\Model_Communication_SMS');
	}

	function verifyTo($to_field, $contact_id){
		$model_phone = $this->add('xepan\base\Model_Contact_Phone');
		$model_phone->addCondition('contact_id',$contact_id);
		
		$phone_no=[];
		foreach ($model_phone as $value) {
			$phone_no = $model_phone['value'];
		}

		$to_no=[];
		foreach (explode(',', $to_field) as $value) {
			$to_no[]= $value;
		}


		$common_no = array_intersect($phone_no, $to_no);		

		if($common_no)
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
}
