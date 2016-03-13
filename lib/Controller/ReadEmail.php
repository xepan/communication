<?php



namespace xepan\communication;

use Eden\Core;

class Controller_ReadEmail extends \AbstractController {
	
	public $email_setting=null;
	public $imap;

	function init(){
		parent::init();

		if(!$this->email_setting or !($this->email_setting instanceof \xepan\base\Model_Epan_EmailSetting)){
			throw $this->exception('Please provide email_setting value as loaded xepan\base\Model_Epan_EmailSetting instance');
		}

		$this->imap = new \Eden\Mail\Imap(
		    $this->email_setting['imap_email_host'], 
		    $this->email_setting['imap_email_username'],
		    $this->email_setting['imap_email_password'], 
		    $this->email_setting['imap_email_port'], 
		    true);
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

	function fetch($mail_box,$query='UNSEEN',$start=null,$range=null,$or_search=false,$body=true){
		$this->imap->setActiveMailbox($mail_box);
		return $this->imap->search([$query],$start,$range,$or_search,$body);
	}

	function getUniqueEmails($uid, $getBody=true){
		return $this->imap->getUniqueEmails($uid);
	}

	function getMailBoxes(){
		return $this->imap->getMailboxes();
	}
}