<?php

namespace xepan\communication;

class Controller_Cron extends \AbstractController {

	function init(){
		parent::init();

		$email_settings = $this->add('xepan\communication\Model_Communication_EmailSetting')
						->addCondition('is_imap_enabled',true)
						->addCondition('is_active',true);

		foreach ($email_settings as $email_setting) {
			echo "<br/> Fetching from ". $email_setting['name']. '<br/>';
			$cont = $this->add('xepan\communication\Controller_ReadEmail',['email_setting'=>$email_setting]);
			
			$mbs = ['INBOX'] ; // $cont->getMailBoxes();

			foreach ($mbs as $mb) {
				$emails_return = $cont->fetch($mb,'UNSEEN');
				$this->app->hook('emails_fetched',[$emails_return]);
			}
		}

	}

}