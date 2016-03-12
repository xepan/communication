<?php

namespace xepan\communication;

class page_cron extends \Page {

	function init(){
		parent::init();

		$email_settings = $this->add('xepan\base\Model_Epan_EmailSetting')
						->addCondition('is_imap_enabled',true);

		foreach ($email_settings as $email_setting) {
			$cont = $this->add('xepan\communication\Controller_ReadEmail',['email_setting'=>$email_setting]);
			$mbs = $cont->getMailBoxes();
			foreach ($mbs as $mb) {
				$emails = $cont->fetch($mb,'ALL',0,3);
				foreach ($emails as $uid => $email) {
					if(!is_array($email)) continue;
					try{
						$body=(isset($email['body']['text/html']) And $email['body']['text/html'])?$email['body']['text/html']:(isset($email['body']['text/plain']) And $email['body']['text/plain'])?$email['body']['text/plain']:'(no content)';
						$email_model=$this->add('xepan\communication\Model_Communication_Email_Received');	
						$email_model['uid']=$uid;
						$email_model['mailbox']=$email_setting['imap_email_username'].'#'.$email['mailbox'];
						$email_model['from_raw']=json_encode($email['from']);
						$email_model['to_raw']=json_encode($email['to']);
						$email_model['cc_raw']=json_encode($email['cc']);
						$email_model['bcc_raw']=json_encode($email['bcc']);
						$email_model['flags']=$email['flags'];
						$email_model['title']=$email['subject'];
						$email_model['description']=$body;
						$email_model->save();
					}catch(\Exception $e){
						var_dump($email);
						// throw $e;
					}
				}

			}
		}

	}

}