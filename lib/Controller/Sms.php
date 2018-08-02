<?php

namespace xepan\communication;

class Controller_Sms extends \AbstractController{
	public $debug = false;
	function sendMessage($no,$msg,$sms_setting=[]){
		$curl=$this->add('xepan\communication\Controller_CURL');

		if($this->app->getConfig('send_sms',true)){

			if(is_array($sms_setting) && !sizeof($sms_setting)){
				$sms_setting = $this->add('xepan\communication\Model_Communication_DefaultSMSSetting')
					->tryLoadAny();
			}

			if($sms_setting['sms_prefix'])
				$msg = $sms_setting['sms_prefix']." ".$msg;
			if($sms_setting['sms_postfix'])
				$msg .= " ".$sms_setting['sms_postfix'];
			
			$msg=urlencode($msg);
			$url=$sms_setting['gateway_url'].
			'&'.$sms_setting['sms_user_name_qs_param'] .'='.$sms_setting['sms_username'].
			'&'.$sms_setting['sms_password_qs_param'] .'='.$sms_setting['sms_password'].
			'&'.$sms_setting['sms_number_qs_param'] .'='.$no.
			'&'.$sms_setting['sm_message_qs_param'] .'='.$msg;
			
			if($this->debug) return $url;

			return $curl->get($url);
		}
	}
}