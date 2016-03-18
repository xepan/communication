<?php
namespace xepan\communication;

class page_emaildetail extends \Page{
	public $title="Email Details";
	function init(){
		parent::init();
		$email_id=$this->api->stickyGET('email_id');
		
		$email_model=$this->add('xepan\communication\Model_Communication_Email_Received');
		$email_model->addCondition('id',$_GET['email_id']);
		$email_model->tryLoadAny();
		$email_detail=$this->add('xepan\communication\View_EmailDetail');

		$email_detail->setModel($email_model);
	}
}