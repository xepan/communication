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

	function send(){
		$this->save();
	}	

	function verifyTo(){
		return true;
	}
}
