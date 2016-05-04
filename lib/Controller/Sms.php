<?php

namespace xepan\communication;

class Controller_Sms extends AbstractController{
	function sendActivationCode($model,$code){

	}

	function sendMessage($no,$msg){
		$curl=$this->add('Controller_CURL');
		$msg=urlencode($msg);

		$url="http://cloud.smsindiahub.in/vendorsms/pushsms.aspx?user=".$this->app->getConfig('user')."&password=".$this->app->getConfig('password')."&msisdn=$no&sid=".$this->app->getConfig('senderId')."&msg=$msg&fl=0&gwid=2";
		
		// echo $url;
		return $curl->get($url);
	}
}