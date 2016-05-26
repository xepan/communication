<?php

/*

ALL - return all messages matching the rest of the criteria
ANSWERED - match messages with the \\ANSWERED flag set
BCC "string" - match messages with "string" in the Bcc: field
BEFORE "date" - match messages with Date: before "date"
BODY "string" - match messages with "string" in the body of the message
CC "string" - match messages with "string" in the Cc: field
DELETED - match deleted messages
FLAGGED - match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
FROM "string" - match messages with "string" in the From: field
KEYWORD "string" - match messages with "string" as a keyword
NEW - match new messages
OLD - match old messages
ON "date" - match messages with Date: matching "date"
RECENT - match messages with the \\RECENT flag set
SEEN - match messages that have been read (the \\SEEN flag is set)
SINCE "date" - match messages with Date: after "date"
SUBJECT "string" - match messages with "string" in the Subject:
TEXT "string" - match messages with text "string"
TO "string" - match messages with "string" in the To:
UNANSWERED - match messages that have not been answered
UNDELETED - match messages that are not deleted
UNFLAGGED - match messages that are not flagged
UNKEYWORD "string" - match messages that do not have the keyword "string"
UNSEEN - match messages which have not been read yet

 */

namespace xepan\communication;


class Controller_ReadEmail extends \AbstractController {
	
	public $email_setting=null;
	public $imap;
	public $connection;

	function init(){
		parent::init();

		if(!$this->email_setting or !($this->email_setting instanceof \xepan\communication\Model_Communication_EmailSetting)){
			throw $this->exception('Please provide email_setting value as loaded xepan\communication\Model_Communication_EmailSetting instance');
		}
		
	}


	function fetch($mailbox_name, $conditions=null){
		$imap_email_host = $this->email_setting['imap_email_host']; 
		$imap_email_port = $this->email_setting['imap_email_port'];
		$imap_email_username = $this->email_setting['imap_email_username']; 
		$imap_email_password = $this->email_setting['imap_email_password']; 
		$imap_flags = $this->email_setting['imap_flags'];

		$mailbox = new ImapMailbox('{'.$imap_email_host.':'.$imap_email_port.$imap_flags.'}'.$mailbox_name, $imap_email_username, $imap_email_password, "websites/".$this->app->current_website_name."/upload", 'utf-8');
		
		$return=[];

		try{
			$conditions = $conditions?:'UNSEEN';
			$mailsIds = $mailbox->searchMailBox($conditions);

			if(!$mailsIds) {
				$mailbox->disconnect();
				return $return;
			}

			$i=1;
			$fetch_email_array = array();
			foreach ($mailsIds as $mailId) {
				$fetched_mail = $mailbox->getMail($mailId);

				
				$mail_m = $this->add('xepan\communication\Model_Communication_Email_Received');
				$mail_m->addCondition('uid',$fetched_mail->id);
				$mail_m->addCondition('mailbox',$this->email_setting['imap_email_username'].'#'.$mailbox_name);
				$mail_m->tryLoadAny();
				
				if($mail_m->loaded()) continue;
				
				$mail_m->setFrom($fetched_mail->fromAddress,$fetched_mail->fromName);

				/*Fetch TO Email Array & Convert To array name or email format*/
				$to_email_arry = $fetched_mail->to;
				foreach ($to_email_arry as $email => $name) {
					$mail_m->addTo($email,$name);
				}

				/*Fetch CC Email Array & Convert To array name or email format*/
				$cc_email_array=$fetched_mail->cc;
				foreach ($cc_email_array as $email => $name) {
					$mail_m->addCc($email,$name);
				}
				if(isset($fetched_mail->bcc)){
					$bcc_email_array=$fetched_mail->bcc;
					foreach ($bcc_email_array as $email => $name) {
						$mail_m->addBcc($email,$name);
					}
				}
				
				$mail_m['created_at']= $fetched_mail->date;
				$mail_m['title'] = $fetched_mail->subject;
				$mail_m['description'] = $fetched_mail->textHtml?:$fetched_mail->textPlain;
				$mail_m['flags'] = $conditions;
				$mail_m->findContact('from');
				$mail_m->save();

				if($this->email_setting['auto_reply']){
					$mail_m->reply($this->email_setting);
				}
				$fetch_email_array[] = $mail_m->id;
				
				if(!isset($return['fetched_emails_from']))
					$return['fetched_emails_from'] = $mail_m->id;
				
				//MAIL ATTACHME  NT 
				$attachments = $fetched_mail->getAttachments();
				foreach ($attachments as $attach) {
					$file =	$this->add('filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'move','import_source'=>$attach->filePath));
					$file['filestore_volume_id'] = $file->getAvailableVolumeID();
					$file['original_filename'] = $attach->name;
					$file->save();
					$mail_m->addAttachment($file->id);
				}

				$mail_m->unload();
				$i++;
			}
			
		}catch(\Exception $e){
			$mailbox->disconnect();
			throw $e;
		}

		$mailbox->disconnect();
		return $return;
	}
}