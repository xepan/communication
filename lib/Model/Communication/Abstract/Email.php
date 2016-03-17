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

		$this->add('misc/Field_Callback','communication_with')->set(function($m){
			
			$from_raw=$m['from_raw'];
			$to_raw=$m['to_raw'];

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
		$this->add('misc/Field_Callback','callback_date')->set(function($m){
			if(date('Y-m-d',strtotime($m['created_at']))==date('Y-m-d',strtotime($this->app->now))){
				return $m['created_at']=date('h:i:a',strtotime($m['created_at']));	
			}
			return $m['created_at']=date('M d',strtotime($m['created_at']));
		});

		$this->add('misc/Field_Callback','callback_to')->set(function($m){
			$to_raw=$m['to_raw'];
				return $m['to_id']?$m['to']:
							($to_raw['name']?$to_raw['name']:$to_raw['email'])
							;

		});


		$this->addHook('afterLoad',function($m){

			$m['from_raw'] = json_decode($m['from_raw'],true);
			$m['to_raw'] = json_decode($m['to_raw'],true);
			$m['cc_raw'] = json_decode($m['cc_raw'],true);
			$m['bcc_raw'] = json_decode($m['bcc_raw'],true);
			$m['title'] = $m['subject'] = $m['title']?:'(no subject)';
			
			$description=json_decode($m['description'],true);
			$m['body'] = $m['description'];
		});
		
		$this->addHook('beforeSave',function($m){
			$m['to_raw'] = json_encode($m['to_raw']);
			$m['from_raw'] = json_encode($m['from_raw']);
			$m['cc_raw'] = json_encode($m['cc_raw']);
			$m['bcc_raw'] = json_encode($m['bcc_raw']);
			$m['title'] = $m['title']?:("(no subject)");
		});

		$this->getElement('status')->defaultValue('Draft');
		
	}

	function setFrom($email,$name=null){		
		$tmp=['name'=>$name,'email'=>$email];
		$tmp = array_merge($tmp,$this['from_raw']);
		$this->set('from_raw',$tmp);
	}

	function addTo($email,$name=null){
		$tmp = $this['to_raw'];
		$to=['name'=>$name,'email'=>$email];
		$tmp[] = $to;
		$this->set('to_raw',$tmp);
	}

	function addCc($email,$name=null){
		$tmp = $this['cc_raw'];
		$to=['name'=>$name,'email'=>$email];
		$tmp[] = $to;
		$this->set('cc_raw',$tmp);
	}

	function addBcc($email, $name=null){
		$tmp = $this['bcc_raw'];
		$to=['name'=>$name,'email'=>$email];
		$tmp[] = $to;
		$this->set('bcc_raw',$tmp);
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

	function send(\xepan\base\Model_Epan_EmailSetting $email_setting){
		$this['status']='Outbox';
		try{
			
			$mail = new \Nette\Mail\Message;
			$mail->setFrom($this['from_raw']['email'],$this['from_raw']['name']?:null);

			foreach ($this['to_raw'] as $to) {
			    $mail->addTo($to['email'],$to['name']?:null);
			}

			if($this['cc_raw'])
				foreach ($this['cc_raw'] as $cc) {
				    $mail->addCC($cc['email'],$cc['name']?:null);
				}

			if($this['bcc_raw'])
				foreach ($this['bcc_raw'] as $bcc) {
				    $mail->addBcc($bcc['email'],$bcc['name']?:null);
				}

			$mail->setSubject($this['title'])
			    ->setHTMLBody($this['description']);

			$mailer = new \Nette\Mail\SmtpMailer(array(
			        'host' => $email_setting['email_host'],
			        'username' => $email_setting['email_username'],
			        'password' => $email_setting['email_password'],
			        'secure' => $email_setting['encryption'],
			));

			$mailer->send($mail);

		}catch(\Exception $e){
			$this->save();
			throw $e;
		}
		$this['status']='Sent';
		$this->save();
	}		
}
