<?php

namespace xepan\communication;

class page_cron extends \Page {

	function init(){
		parent::init();

		$emails = $this->add('xepan\base\Model_Epan_EmailSetting');

		foreach ($emails as $email) {
			if(!$email['imap_email_password']) continue;
			$cont = $this->add('xepan\communication\Controller_ReadEmail',['email_setting'=>$email]);
			$emails = $cont->fetch('INBOX');

			var_dump($emails);
			exit;
		}
	}

}