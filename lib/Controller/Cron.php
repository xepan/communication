<?php

namespace xepan\communication;

class Controller_Cron extends \AbstractController {
	public $loop_time_duration = 5; // in minute
	public $debug = true;
	function init(){
		parent::init();
		
		$email_settings = $this->add('xepan\communication\Model_Communication_EmailSetting')
						->addCondition('is_imap_enabled',true)
						->addCondition('is_active',true);

		$total_email_to_fetch = $email_settings->count()->getOne();
		$total_email_to_fetch_per_minute = ceil($total_email_to_fetch / $this->loop_time_duration);
		
		$time_before_five_minute = date("Y-m-d H:i:s", strtotime("-".$this->loop_time_duration." minutes", strtotime($this->app->now)));

		$email_settings->addCondition('last_email_fetched_at','<',$time_before_five_minute);
		$email_settings->setOrder('last_email_fetched_at','asc');
		$email_settings->setLimit($total_email_to_fetch_per_minute);		

		foreach ($email_settings as $email_setting) {
			if($this->debug)
				echo "<br/> Fetching from ". $email_setting['name']. '<br/>';

			$cont = $this->add('xepan\communication\Controller_ReadEmail',['email_setting'=>$email_setting,'debug'=>$this->debug]);
			$mbs = ['INBOX'] ; // $cont->getMailBoxes();
			foreach ($mbs as $mb) {
				$emails_return = $cont->fetch($mb,'UNSEEN');
				$this->app->hook('emails_fetched',[$emails_return]);
			}
			$email_setting['last_email_fetched_at'] = $this->app->now;
			$email_setting->saveAndUnload();
		}

	}

}