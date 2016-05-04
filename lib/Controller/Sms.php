<?php

namespace xepan\communication;

class Controller_Sms extends \AbstractController{
	function sendMessage($no,$msg,$sms_setting){
		$curl=$this->add('Controller_CURL');
		$msg=urlencode($msg);

		$url=$sms_setting['gateway_url'].
		'&'.$sms_setting['sms_user_name_qs_param'] .'='.$sms_setting['sms_username'].
		'&'.$sms_setting['sms_password_qs_param'] .'='.$sms_setting['sms_password'].
		'&'.$sms_setting['sms_number_qs_param'] .'='.$no.
		'&'.$sms_setting['sm_message_qs_param'] .'='.$msg;		
		return $curl->get($url);
	}
}