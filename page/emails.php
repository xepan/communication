<?php
namespace xepan\communication;

class page_emails extends \Page{

	public $title="Emails";

	function init(){
		parent::init();
		/*Emails Header*/
		// $this->add('xepan\communication\View_EmailHeader',null,'email_header');
		
		/*Emails Navigation*/
		
		$this->add('xepan\communication\View_EmailNavigation',null,'email_navigation');
		/*Emails Label*/
		$label_view=$this->add('xepan\communication\View_Lister_EmailLabel',null,'email_labels');
		$label_view->setModel('xepan\base\Epan_EmailSetting');
		/*Email List*/
		$email_model=$this->add('xepan\communication\Model_Communication_Email');
		
		$email_view=$this->add('xepan\communication\View_Lister_EmailsList',null,'email_lister');
		$email_view->setModel($email_model);
		
	}
	
	function defaultTemplate(){
		return['page/emails'];
	}
}