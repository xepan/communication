<?php

namespace xepan\communication;

class page_cron extends \Page {

	function init(){
		parent::init();

		$emails = $this->add('xepan\base\Model_Epan_EmailSetting')
						->addCondition('is_imap_enabled',true);

		foreach ($emails as $email) {
			$cont = $this->add('xepan\communication\Controller_ReadEmail',['email_setting'=>$email]);
			$emails = $cont->fetch('INBOX');
		}
	}

}