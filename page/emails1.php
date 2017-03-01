<?php
namespace xepan\communication;

class page_emails1 extends \xepan\base\Page{

	public $title="Emails";

	function init(){
		parent::init();


		$compose_view = $this->add('xepan\communication\View_ComposeEmailPopup',['communication_id'=>$_GET['communication_id'],'mode'=>$_GET['mode']],'compose_btn_header');
		// $compose_view = $this->add('xepan\communication\View_EmailsComposeBtn',null,'compose_btn_header');
		
		// $this->add('xepan\communication\View_EmailNavigation',null,'email_navigation');
		
		// $label_view=$this->add('xepan\communication\View_Lister_EmailLabel',null,'email_labels');
		// $my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
		// $label_view->setModel($my_email);

		$email_view = $this->add('xepan\communication\View_Lister_EmailsList',null,'email_lister');
		// $email_model=$this->add('xepan\communication\Model_Communication_Email'.$mailbox);
		$email_model=$this->add('xepan\communication\Model_Communication_Email_Received')->setLimit(100);
		$email_view->setModel($email_model);

		$email_detail = $this->add('xepan\communication\View_EmailDetail',null,'email_detail');

		$this->js(true)->_load('emails')->xepan_emails();

	}

	function defaultTemplate(){
		return['page/emails1'];
	}
}