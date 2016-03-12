<?php
namespace xepan\communication;

class page_emails extends \Page{

	public $title="Emails";

	function init(){
		parent::init();
		/*Emails Header*/
		$this->add('xepan\communication\View_EmailHeader',null,'email_header');
		
		/*Emails Navigation*/
		
		$this->add('xepan\communication\View_EmailNavigation',null,'email_navigation');
		/*Emails Label*/
		$label_view=$this->add('xepan\communication\View_Lister_EmailLabel',null,'email_labels');
		$label_view->setModel('xepan\base\Epan_EmailSetting');
		/*Email List*/
		$email_model=$this->add('xepan\communication\Model_Communication_Email');

		$email_model->add('misc/Field_Callback','callback_date')->set(function($m){
			if(date('Y-m-d',strtotime($m['created_at']))==date('Y-m-d',strtotime($this->app->now))){
				return $m['created_at']=date('h:i:a',strtotime($m['created_at']));	
			}
			return $m['created_at']=date('M d',strtotime($m['created_at']));
		});


		
		$email_view=$this->add('xepan\communication\View_Lister_EmailsList',null,'email_lister');
		$email_view->setModel($email_model);
		
	}
	
	function defaultTemplate(){
		return['page/emails'];
	}
}