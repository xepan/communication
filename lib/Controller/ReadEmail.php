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

	function fetch($mail_box){
		$this->imap->setActiveMailbox($mail_box);
		return $this->imap-getEmails(0,3);
	}

	function getMailBoxes(){
		return $this->imap->getMailboxes();
	}
}