<?php
namespace xepan\communication;

class Model_Communication_Email_ContactReceivedEmail extends \xepan\communication\Model_Communication_Email_ContactEmail{
	function init(){
		parent::init();
		$this->addCondition('direction','In');
	}
}