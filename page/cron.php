<?php

namespace xepan\communication;

class page_cron extends \Page {

	function init(){
		parent::init();

		$email_settings = $this->add('xepan\base\Model_Epan_EmailSetting')
						->addCondition('is_imap_enabled',true);

		foreach ($email_settings as $email_setting) {
			$cont = $this->add('xepan\communication\Controller_ReadEmail',['email_setting'=>$email_setting]);
			
			$mbs = ['INBOX'] ; // $cont->getMailBoxes();

			foreach ($mbs as $mb) {
				$emails = $cont->fetch($mb);
				// $emails = $cont->fetch($mb,'Ddeboer\Imap\Search\LogicalOperator\ALL',0,10,false,false);
				// var_dump($emails);
				// exit;
				// foreach ($emails as $uid => $email) {
				// 	if(!is_array($email)) continue;
				// 	try{
				// 		$body=(isset($email['body']['text/html']) And $email['body']['text/html'])?$email['body']['text/html']:(isset($email['body']['text/plain']) And $email['body']['text/plain'])?$email['body']['text/plain']:'(no content)';
				// 		$email_model=$this->add('xepan\communication\Model_Communication_Email_Received');	
				// 		$email_model['uid']=$uid;
				// 		$email_model['uuid']=$email['id'];
				// 		$email_model['mailbox']=$email_setting['imap_email_username'].'#'.$email['mailbox'];
				// 		$email_model['from_raw']=json_encode($email['from']);
				// 		$email_model['to_raw']=json_encode($email['to']);
				// 		$email_model['cc_raw']=json_encode($email['cc']);
				// 		$email_model['bcc_raw']=json_encode($email['bcc']);
				// 		$email_model['flags']=$email['flags'];
				// 		$email_model['title']=$email['subject'];
				// 		$email_model['description']=$body;
				// 		$email_model->findContact(false);

				// 		if($email_model['from_id'] || $email_model['to_id']){
				// 			$details = $cont->getUniqueEmails($uid);
				// 			$email_model['description'] = $details['body']['text/html']?:$details['body']['text/plain'];
				// 			$email_model['detailed']=true;

				// 			if($details['attachment']){
				// 				var_dump($details['body']['attachment']);
				// 				var_dump($details['attachment']);
				// 			}
				// 		}
				// 		$email_model->save();
				// 		var_dump($email);
				// 		exit;
				// 	}catch(\Exception $e){
				// 		var_dump($email);
				// 		throw $e;
				// 	}
				// }

			}
		}

	}

}