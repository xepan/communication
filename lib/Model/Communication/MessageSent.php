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
				'title'=>'New task dsfhjk dfdk ',
				'message'=>" Task Assigned to you : 'ABCD' by 'GVS' ",
				'type'=>'warning',
				'sticky'=>false,
				'desktop'=>false,
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