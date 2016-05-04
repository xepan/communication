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

class Model_Communication_Phone extends Model_Communication {
	
	public $status=['Called','Received'];

	function init(){
		parent::init();
		$this->addCondition('communication_type','Call');	
		$this->getElement('status')->defaultValue('Called');
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

	function addCc($number,$name=null){
		$this->addTo($number,$name);
	}

	function addBcc($email, $name=null){
		$this->addTo($number,$name);
	}

	function setSubject($subject){
		$this['title']=$subject;
	}		

	function setBody($body){
		$this['description']=$body;
	}

	function send(){
		$this->call();
	}

	function call(){
		$this['status']='Received';
		$this->save();
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
}
