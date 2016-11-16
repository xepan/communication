<?php

namespace xepan\communication;


/**
* 
*/
class Model_Communication_AbstractMessage extends Model_Communication{
	public $status=['Draft','Sent','Outbox','Received','Trashed'];

	function init(){
		parent::init();
		$this->addCondition('communication_type','AbstractMessage');	
		$this->getElement('status')->defaultValue('Draft');

		$this->addHook('afterLoad',function($m){
			$m['from_raw'] = json_decode($m['from_raw'],true);
			$this['to_raw'] = json_decode($m['to_raw'],true);
			// var_dump($this['to_raw']);
			// exit;
			$m['cc_raw'] = json_decode($m['cc_raw'],true);
			$m['bcc_raw'] = json_decode($m['bcc_raw'],true);
			$m['title'] = $m['subject'] = $m['title']?:'(no subject)';
			
			$description=json_decode($m['description'],true);
			$m['body'] = $m['description'];
		});
		
		$this->addHook('beforeSave',function($m){
			if(is_array($m['to_raw']))
				$m['to_raw'] = json_encode($m['to_raw']);
			if(is_array($m['from_raw']))
				$m['from_raw'] = json_encode($m['from_raw']);
			if(is_array($m['cc_raw']))
				$m['cc_raw'] = json_encode($m['cc_raw']);
			if(is_array($m['bcc_raw']))
				$m['bcc_raw'] = json_encode($m['bcc_raw']);
			$m['title'] = $m['title']?:("(no subject)");
			
		});

	}
}