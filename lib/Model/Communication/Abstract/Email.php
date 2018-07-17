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

	public $connections=[];

	function init(){
		parent::init();

		// $this->containsOne(['extra_info','json'=>true],function($m){
		// 	$m->addField('seen_by')->defaultValue(null);
		// });

		$this->add('misc/Field_Callback','communication_with')->set(function($m){
			
			$from_raw=$m['from_raw'];
			$to_raw=$m['to_raw'];


			if($m['direction']=='Out'){
				return ($m['to_id'] And $m['to']!=null)?$m['to']:
							($to_raw[0]['name']?$to_raw[0]['name']:$to_raw[0]['email'])
							;
			}

			if($m['direction']=="In"){
				return $m['from_id']?$m['from'].", ".$from_raw['email']:
							($from_raw['name']?$from_raw['name'].", ".$from_raw['email']:$from_raw['email'])
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

	
	/**
	 reply_to_all param passed only true or false value
	*/

	function getReplyEmailFromTo($reply_to_all=null){
		$mail_box = explode('#',$this['mailbox']);
		$mail_box = $mail_box[0];		

		$return =[
			'to'=>[['email'=>$this['from_raw']['email']]],
			'cc'=>[],
			'bcc'=>[],
			'from'=>[$mail_box]
		];
		if($reply_to_all){
			foreach ($this['to_raw'] as  $to_mail) {
				if(trim($to_mail['email']) != $mail_box){
					$return['to'][] = ['email'=>trim($to_mail['email'])];
				}
			}
		}

		if($this['cc_raw']){
			foreach ($this['cc_raw'] as  $cc_mail) {
				if(trim($cc_mail['email']) != $mail_box){
					$return['cc'][]  = ['email'=>trim($cc_mail['email'])];
				}
			}	
		}

		if($this['bcc_raw']){
			foreach ($this['bcc_raw'] as  $bcc_mail) {
				if(trim($bcc_mail['email']) != $mail_box){
					$return['bcc'][] = ['email'=>trim($bcc_mail['email'])];
				}
			}
		}

		return $return;
	}


	function send(\xepan\communication\Model_Communication_EmailSetting $email_setting, $mailer=null, $add_signature=true){
		$this['status']='Outbox';
		$this['direction']='Out';
		$this['mailbox']=$email_setting['email_username'].'#SENT';
		$this['description'] = $this['description'];
		if($add_signature)
			$this['description'] = $this['description'].$email_setting['signature'];
		if(!$this['to_id']) $this->findContact('to');
		$this['communication_channel_id'] = $email_setting->id;
		
		

		// in case of index existing error, means this email is already sent.. let error be thrown
		$this->save();

		if(!$this->app->getConfig('test_mode',false)){			
			try{
				$mail = new \Nette\Mail\Message;
				$mail->setFrom($this['from_raw']['email'],$this['from_raw']['name']?:null);

				$return_path = $email_setting['return_path'];
				if(!$return_path) $return_path = $this['from_raw']['email'];

				$mail->setReturnPath($return_path);
				
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
				    ->setHTMLBody($this['description'],$this->app->pathfinder->base_location->base_path);

				if(!$mailer){
					if(isset($this->app->mailer)) 
						$mailer = $this->app->mailer;
					else{
						$mailer = new \Nette\Mail\SmtpMailer(array(
					        'host' => $email_setting['email_host'],
					        'username' => $email_setting['email_username'],
					        'password' => $email_setting['email_password'],
					        'secure' => $email_setting['encryption'],
					        'persistent'=>true
						));
						$this->app->mailer = $mailer;
					}
				}
				$mailer->send($mail);

				$email_setting['last_emailed_at'] = $this->app->now;
				$email_setting['email_sent_in_this_minute'] = $email_setting['email_sent_in_this_minute'] + 1;
				$email_setting->save();

			}catch(\Exception $e){
				$this->save();
				throw $e;
			}	
		}

		if($this->app->getConfig('test_mode',false)){
			// echo "setting last_emailed_at on ". $email_setting['name']. ' as '. $this->app->now . '<br/>';
			$email_setting['last_emailed_at'] = $this->app->now;
			$email_setting['email_sent_in_this_minute'] = $email_setting['email_sent_in_this_minute'] + 1;
			$email_setting->save();
		}

		$this['status']='Sent';
		$this->save();
	}	

	function verifyTo($to_field, $contact_id){
		// Support Ticket Reply exisiting contact Communication Setting
		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'varify_to_field_as_contact'=>'DropDown'
							],
					'config_key'=>'Varify_To_Field_As_Exisiting_Conact',
					'application'=>'communication'
			]);
		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();

		if($config_m['varify_to_field_as_contact'] === "No")
			return true;

		
		$model_contact = $this->add('xepan\base\Model_Contact')->load($contact_id);
		$contact_email=$model_contact->getEmails();
		
		foreach (explode(',', $to_field) as $value) {
			if(in_array(trim($value),$contact_email)){
				return true;
			}
		}

		return false;
	}

	function findContact($field='from',$save=false){
		if(!is_array($this[$field.'_raw'])) {
			$this[$field.'_raw'] = json_decode($this[$field.'_raw'],true);
		}

		if($field=='from')
			$email = [['email'=>$this[$field.'_raw']['email']]];
		else
			$email = $this[$field.'_raw'];

		foreach ($email as $em) {
			$contact_emails = $this->add('xepan\base\Model_Contact_Info');
			$contact_emails->addCondition('value',trim($em['email']));
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
