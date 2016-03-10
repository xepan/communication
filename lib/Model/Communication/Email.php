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

class Model_Communication_Email extends Model_Communication{
	
	public $status=['Draft','Sent','Outbox','Received','Trashed'];

	function init(){
		parent::init();

		$this->add('misc/Field_Callback','callback_from')->set(function($m){
			$from_raw=json_decode($m['from_raw'],true);
			$to_raw=json_decode($m['to_raw'],true);
			
			if($m['direction']=='Out'){
				return $m['to_id']?$m['to']:
							($to_raw[0]['name']?$to_raw[0]['name']:$to_raw[0]['email'])
							;
			}

			if($m['direction']=="In"){
				return $m['from_id']?$m['from']:
							($from_raw['name']?$from_raw['name']:$from_raw['email'])
							;
			}	
		});

		$this->add('misc/Field_Callback','callback_subject')->set(function($m){
			return $m['title']?$m['title']:
							("No Subject");
		});

		$this->add('misc/Field_Callback','callback_body')->set(function($m){
			$description=json_decode($m['description'],true);

			return (isset($description['text/html']) AND $description['text/html'] ) ?
							$description['text/html']:
								$description['text/plain']?$description['text/plain']:'(no content)';
		});
		
		$this->addCondition('communication_type','Email');		
	}
}
