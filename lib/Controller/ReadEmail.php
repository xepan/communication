<?php



namespace xepan\communication;

// use Ddeboer\Imap;
// use Ddeboer\Imap\Server;
// use Ddeboer\Imap\SearchExpression;

class Controller_ReadEmail extends \AbstractController {
	
	public $email_setting=null;
	public $imap;
	public $connection;

	function init(){
		parent::init();

		if(!$this->email_setting or !($this->email_setting instanceof \xepan\base\Model_Epan_EmailSetting)){
			throw $this->exception('Please provide email_setting value as loaded xepan\base\Model_Epan_EmailSetting instance');
		}


		// $server = new Server(
		//     $hostname, // required
		//     $port,     // defaults to 993
		//     $flags,    // defaults to '/imap/ssl/validate-cert'
		//     $parameters
		// );
		// $this->imap = new \Ddeboer\Imap\Server(
		//     $this->email_setting['imap_email_host'], 
		//     $this->email_setting['imap_email_port'], 
		//     $this->email_setting['imap_flags']);

		// $this->connection = $this->imap->authenticate($this->email_setting['imap_email_username'],$this->email_setting['imap_email_password']);
	}

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

	function fetch($mail_box, $conditions=null){
		$imap_email_host = $this->email_setting['imap_email_host']; 
		$imap_email_port = $this->email_setting['imap_email_port'];
		$imap_email_username = $this->email_setting['imap_email_username']; 
		$imap_email_password = $this->email_setting['imap_email_password']; 
		$imap_flags = $this->email_setting['imap_flags'];

		$mailbox = new ImapMailbox('{'.$imap_email_host.':'.$imap_email_port.$imap_flags.'}'.$mail_box, $imap_email_username, $imap_email_password, "upload", 'utf-8');
		
		$mails = array();
		// var_dump($mailbox->getMailboxInfo());
		// return;
		// Get some mail
		try{
			$conditions = $conditions?:'UNSEEN'/*.date('d-M-Y',strtotime('-1 day'))*/;
			$mailsIds = $mailbox->searchMailBox($conditions);
			// var_dump($mailsIds);
			// exit;
			if(!$mailsIds) {
				$mailbox->disconnect();
			}else{
				$mail_m = $this->add('xepan\communication\Model_Communication_Email_Received');
				$i=1;
				$fetch_email_array = array();
				foreach ($mailsIds as $mailId) {
					$mail = $mailbox->getMail($mailId);
					// var_dump($mail->getAttachments());
					$mail_m['uid']= $mail->id;
					
					$fetched = $this->add('xepan\communication\Model_Communication_Email_Received');
					
	
					$fetched->addCondition('uid',$mail->id);
					$fetched->addCondition('from_raw',$mail->fromAddress);
					$fetched->addCondition('to_raw',$mail->to);
					$fetched->addCondition('created_at',$mail->date);
					$fetched->tryLoadAny();
					if($fetched->loaded()) continue;
					
					$from_raw = json_decode($fetched['from_raw']);
					$from=['name'=>$mail->fromName,'email'=>$mail->fromAddress];
					$from_raw[] = $from;
					// var_dump($mail->to);
					// exit;

					/*Fetch TO Email Array & Convert To array name or email format*/
					$to_email_arry = $mail->to;
					$to_raw = [];
					foreach ($to_email_arry as $key => $value) {
						$temp = ['name'=>$value,'email'=>$key];
						$to_raw[] = $temp;
					}

					/*Fetch CC Email Array & Convert To array name or email format*/
					$cc_email_array=$mail->cc;
					$cc_raw=[];
					foreach ($cc_email_array as $key => $cc_value) {
						$cc=['name'=>$cc_value,'email'=>$key];
						$cc_raw[]=$cc;
					}
					// var_dump($cc_raw);
					// exit;
					
					$mail_m['mailbox']=$this->email_setting['imap_email_username'].'#'.$mail_box;
					$mail_m['created_at']= $mail->date;
					$mail_m['created_at']= $mail->date;
					$mail_m['from_raw'] = json_encode($from_raw); //$mail->fromAddress;
					$mail_m['to_raw'] =json_encode($to_raw); 
					$mail_m['cc_raw'] =json_encode($cc_raw); 
					$mail_m['title'] = $mail->subject;
					$mail_m['description'] = $mail->textHtml?:$mail->textPlain;
					$mail_m['uid'] = $mail->id;
					$mail_m['direction'] = 'In';
					$mail_m['flags'] = $conditions;
					$mail_m->findContact(true);
					// $mail_m['from_name'] = $mail->fromName;
					$mail_m->save();
					$fetch_email_array[] = $mail_m->id;
					
					//MAIL ATTACHMENT 
					$attachments = $mail->getAttachments();
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
			}

		}catch(\Exception $e){
			$mailbox->disconnect();
			throw $e;
		}
	}

	// function fetch($mail_box,$query='Ddeboer\Imap\Search\Flag\Unseen',$start=null,$range=null,$or_search=false,$body=true){
	// 	$mailbox = $this->connection->getMailbox($mail_box);

	// 	$search = new SearchExpression();
	// 	// if(is_array($query)){
	// 	// 	foreach ($query as $q) {
	// 	// 		$search->addCondition(new $query);
	// 	// 	}
	// 	// }else{
	// 	// }
	// 		$search->addCondition(new \Ddeboer\Imap\Search\Flag\Unseen);

	// 	$mails = $mailbox->getMessages($search);
		
	// 	// $mails[0]->keepUnseen()->getBodyHtml();

	// 	$return_mails=[];
	// 	foreach ($mails as $mail) {
	// 		$return_mails=[
	// 			'body'=>[
	// 					'text/html'=>$mail->keepUnseen()->getBodyHtml(),
	// 					'text/plain'=>$mail->keepUnseen()->getBodyText()
	// 					],

	// 			'uid'=>$mail->getNumber(),	
	// 			'uuid'=>$mail->getId(),	
	// 			'subject'=>$mail->getSubject(),	
	// 			'from_raw'=>$mail->getFrom(),	
	// 			'to_raw'=>$mail->getTo(),	
	// 			'cc_raw'=>$mail->getCc(),	
	// 				];
	// 		echo "<pre/>";
	// 		var_dump($return_mails);
	// 		exit;
	// 	}

	// 	return $return_mails;
	// }

	// function getUniqueEmails($uid, $getBody=true){
	// 	return $this->imap->getUniqueEmails($uid);
	// }

	// function getMailBoxes(){
	// 	return $this->connection->getMailboxes();
	// }
}