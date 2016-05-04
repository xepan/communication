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


		$this->containsOne(['extra_info','json'=>true],function($m){
			$m->addField('seen_by')->defaultValue(null);
		});

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
				return date('h:i a',strtotime($m['created_at']));	
			}
			return date('M d',strtotime($m['created_at']));
		});

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

	function addAttachment($attach_id){
		if(!$attach_id) return;
		$attach = $this->add('xepan\communication\Model_Communication_Attachment');
		$attach['file_id'] = $attach_id;
		$attach['communication_email_id'] = $this->id;
	
		$attach->save();

		return $attach;
	}

	function getAttachments($urls=true){
		$attach_arry = array();
		if($this->loaded()){
			foreach ($this->ref('EmailAttachments') as $attach) {
				$attach_arry[] = $urls?$attach['file']:$attach['id'];
			}

		}
		
		return $attach_arry;
	}


	function send(\xepan\base\Model_Epan_EmailSetting $email_setting){
		$this['status']='Outbox';
		$this['direction']='Out';
		$this['mailbox']=$email_setting['email_username'].'#SENT';
		try{
			
			$mail = new \Nette\Mail\Message;
			$mail->setFrom($this['from_raw']['email'],$this['from_raw']['name']?:null);
			
			foreach ($this['to_raw'] as $to) {
			    $mail->addTo(trim($to['email']),$to['name']?:null);
			}

			if($this['cc_raw'])
				foreach ($this['cc_raw'] as $cc) {
				    $mail->addCC(trim($cc['email']),$cc['name']?:null);
				}

			if($this['bcc_raw'])
				foreach ($this['bcc_raw'] as $bcc) {
				    $mail->addBcc(trim($bcc['email']),$bcc['name']?:null);
				}
				
			foreach ($this->getAttachments() as $attach) {
				$mail->addAttachment($_SERVER["DOCUMENT_ROOT"].$attach);				
			}
				
			$mail->setSubject($this['title'])
			    ->setHTMLBody($this['description'].$email_setting['signature'],$this->app->pathfinder->base_location->base_path);

			$mailer = new \Nette\Mail\SmtpMailer(array(
			        'host' => $email_setting['email_host'],
			        'username' => $email_setting['email_username'],
			        'password' => $email_setting['email_password'],
			        'secure' => $email_setting['encryption'],
			));
			
			// $mailer->send($mail);

			$email_setting['last_emailed_at'] = $this->app->now;
			$email_setting->saveAndUnload();

		}catch(\Exception $e){
			$this->save();
			throw $e;
		}
		$this['status']='Sent';
		$this['direction']='Out';
		$this->save();
	}	

	function findContact($save=false, $field='from'){
		if(!is_array($this[$field.'_raw'])) {
			$this[$field.'_raw'] = json_decode($this[$field.'_raw'],true);
		}

		if($field=='from')
			$email = [['email'=>$this[$field.'_raw']['email']]];
		else
			$email = $this[$field.'_raw'];

		foreach ($email as $em) {
			$contact_emails = $this->add('xepan\base\Model_Contact_Info');
			$contact_emails->addCondition('value',$em['email']);
			$contact_emails->tryLoadAny();

			if($contact_emails->loaded()){
				$this[$field.'_id'] = $contact_emails['contact_id'];
				if($save) $this->save();
				return true;
			}
		}

		return false;
	}	
}
