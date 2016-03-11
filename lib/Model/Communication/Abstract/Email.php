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

class Model_Communication_Abstract_Email extends Model_Communication{
	
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

		$this->add('misc/Field_Callback','callback_body')->set(function($m){
			$description=json_decode($m['description'],true);

			return (isset($description['text/html']) AND $description['text/html'] ) ?
							$description['text/html']:
								$description['text/plain']?$description['text/plain']:'(no content)';
		});

		$this->addHook('afterLoad',function($m){
			$m['to_raw'] = json_decode($m['to_raw']);
			$m['from_raw'] = json_decode($m['from_raw']);
		});

		$this->addHook('beforeSave',function($m){
			$m['to_raw'] = json_encode($m['to_raw']);
			$m['from_raw'] = json_encode($m['from_raw']);
			$m['title'] = $m['title']?:("(no subject)");
		});
		
	}

	function addFrom($email,$name=null){
		$from=['name'=>$name,'email'=>$email];
		$this['from_raw'][]=$from;
	}

	function addTo($email,$name=null){
		$to=['name'=>$name,'email'=>$email];
		$this['to_raw'][]=$to;
	}

	function addCc($email,$name=null){
		$cc=['name'=>$name,'email'=>$email];
		$this['cc_raw'][]=$cc;
	}

	function addBcc($email, $name=null){
		$bcc=['name'=>$name,'email'=>$email];
		$this['bcc_raw'][]=$bcc;
	}

	function setSubject($subject){
		$this['title']=$subject;
	}		

	function setBody($body){
		$this['description']=$body;
	}

	function addAttachment($attachment){
		// $this->ref('Attachments')=$attachment;
	}

	function setRelatedDocument($document){
		if($document instanceof \xepan\base\Model_Document)
			$document = $document->id;

		$this['related_document_id'] = $document;
	}

	function send(){
		$this['status']='Outbox';
		try{
			// Try send email in actual
			// throw new \Exception("Mail Not Send", 1);
		}catch(\Exception $e){
			$this->save();
			throw $e;
		}
		$this['status']='Sent';
		$this->save();
	}		
}
