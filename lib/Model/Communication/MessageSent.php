<?php

namespace xepan\communication;


/**
* 
*/
class Model_Communication_MessageSent extends \xepan\communication\Model_Communication_AbstractMessage{

	function init(){
		parent::init();
		$this->addCondition('status','Sent');
		$this->addCondition('direction','Out');
		
		$this->addHook('afterSave',$this);
		$this->addHook('afterInsert',$this);
	}
	
	function afterSave(){

		$msg = [
				'title'=>$this['from'].' messaged you:',
				'message'=>$this['description'],
				'type'=>'success',
				'sticky'=>false,
				'desktop'=>strip_tags($this['description']),
				'js'=>(string) $this->app->js()->_selector('.xepan-internal-message-trigger-reload')->trigger('reload')
			];
		$to_id = [];
		foreach ($this['to_raw'] as $key => $value) {
			$to_id[] = $value['id'];
		}

		$this->add('xepan\hr\Model_Activity')
			->pushToWebSocket($to_id,$msg);
	}

	function afterInsert(){
		$this->breakHook(null);
	}
}