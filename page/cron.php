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
				$emails_return = $cont->fetch($mb,'UNSEEN');
				$this->app->hook('emails_fetched',[$emails_return]);
			}
		}

	}

}