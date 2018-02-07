<?php

namespace xepan\communication;

class Controller_BounceEmailCheck extends \AbstractController  {
	
	function init(){
		parent::init();

		ini_set('memory_limit', '2048M');
		set_time_limit(0);

		$cwsMailBounceHandler = new CwsMailBounceHandler();
		$cwsMailBounceHandler->test_mode = false; // default false
		// $cwsMailBounceHandler->debug_verbose = CWSMBH_VERBOSE_DEBUG; // default CWSMBH_VERBOSE_QUIET
		$cwsMailBounceHandler->purge = true; // default false
		//$cwsMailBounceHandler->disable_delete = false; // default false
		//$cwsMailBounceHandler->open_mode = CWSMBH_OPEN_MODE_IMAP; // default CWSMBH_OPEN_MODE_IMAP
		//$cwsMailBounceHandler->move_soft = false; // default false
		//$cwsMailBounceHandler->folder_soft = 'INBOX.soft'; // default 'INBOX.hard' - NOTE: for open_mode IMAP it must start with 'INBOX.'
		//$cwsMailBounceHandler->move_hard = false; // default false
		//$cwsMailBounceHandler->folder_hard = 'INBOX.hard'; // default 'INBOX.soft' - NOTE: for open_mode IMAP it must start with 'INBOX.'
		/**
		* .eml folder
		*/
		//$cwsMailBounceHandler->open_mode = CWSMBH_OPEN_MODE_FILE;
		//if ($cwsMailBounceHandler->openFolder('emls/')) {
		//$cwsMailBounceHandler->processMails();
		//}
		/**
		* .eml file
		*/
		//$cwsMailBounceHandler->open_mode = CWSMBH_OPEN_MODE_FILE;
		//if ($cwsMailBounceHandler->openFile('test/01.eml')) {
		// $cwsMailBounceHandler->processMails();
		//}
		/**
		* Local mailbox
		*/
		//$cwsMailBounceHandler->open_mode = CWSMBH_OPEN_MODE_IMAP;
		//if ($cwsMailBounceHandler->openImapLocal('/home/email/temp/mailbox')) {
		// $cwsMailBounceHandler->processMails();
		//}
		/**
		* Remote mailbox
		*/

		$mail_box_checked=[];

		$emails_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$emails_setting->addCondition('is_active',true);
		$emails_setting->addCondition('bounce_imap_email_host','<>','');
		
		$invalid_email = [];
		foreach ($emails_setting as  $setting) {
			if(in_array($setting['bounce_imap_email_host'], $mail_box_checked)) continue;
			$mail_box_checked[] = $setting['bounce_imap_email_host'];
			// echo "string".$setting['name'];
			$cwsMailBounceHandler->open_mode = CWSMBH_OPEN_MODE_IMAP;
			$cwsMailBounceHandler->host = $setting['bounce_imap_email_host']; // Mail host server ; default 'localhost'
			$cwsMailBounceHandler->username = $setting['return_path']; // Mailbox username
			$cwsMailBounceHandler->password = $setting['bounce_imap_email_password']; // Mailbox password
			$cwsMailBounceHandler->port = $setting['bounce_imap_email_port']; // the port to access your mailbox ; default 143, other common choices are 110 (pop3), 995 (gmail)
			//$cwsMailBounceHandler->service = 'imap'; // the service to use (imap or pop3) ; default 'imap'
			$cwsMailBounceHandler->service_option = 'ssl'; // the service options (none, tls, notls, ssl) ; default 'notls'
			
			//$cwsMailBounceHandler->cert = CWSMBH_CERT_NOVALIDATE; // certificates validation (CWSMBH_CERT_VALIDATE or CWSMBH_CERT_NOVALIDATE) if service_option is 'tls' or 'ssl' ; default CWSMBH_CERT_NOVALIDATE
			//$cwsMailBounceHandler->boxname = 'TEST'; // the mailbox to access ; default 'INBOX'

			// $cwsMailBounceHandler->imap_opt = $this->api->current_website['bounce_imap_flags'];
			try{
				if ($cwsMailBounceHandler->openImapRemote()) {
					$cwsMailBounceHandler->processMails();
				}
			}catch(\Exception $e){
				echo "bounce email check error on ".$setting['name'].'<br/>';
				$setting['bounce_imap_email_host']='';
				$setting->save();
			}

			// echo "<pre>";
			// print_r($cwsMailBounceHandler->result);
			// echo "</pre>";
			
			$result = $cwsMailBounceHandler->result;
			
			if($result['counter']['processed']){
				foreach ($result['msgs'] as $msg) {
					if($msg['type']=='bounce'){
						foreach ($msg['recipients'] as $receipent) {
							if($receipent['remove']){
								if(!in_array($receipent['email'], $invalid_email))
									$invalid_email[] = $receipent['email'];
							}
						}
					}
				}
			}


		}

		if(count($invalid_email))
			$this->markInValid($invalid_email);
	}

	function markInValid($emails){
		// echo "<pre>";
		// print_r($emails);
		// echo "</pre>";
		// $check=[
		// 		'xepan\base\Model_Contact_Email'=>['email'=>'value']
		// 	];

			// $this->add('xepan\base\Model_Contact_Email')
			// 		->addCondition('value',$emails)
			// 		->set('is_valid',false)
			// 		->debug()
			// 		->update();

			$query="UPDATE contact_info
					SET  is_valid=FALSE
					WHERE value IN ('".implode("','",$emails)."');";
			
			$this->app->db->dsql()->expr($query)->execute();


	}

}