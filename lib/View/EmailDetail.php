<?php
namespace xepan\communication;
class View_EmailDetail extends \View{
	function init(){
		parent::init();

	}

	function setModel($model){
		$m=parent::setModel($model);
		$to_raw=$m['to_raw'];
		$to_lister=$this->add('CompleteLister',null,'to_lister',['view/emails/email-detail','to_lister']);
		// var_dump($m['to_raw']);
		// exit;
		$to_lister->setSource($to_raw);

		$cc_raw=$m['cc_raw'];
		if($cc_raw){
			$cc_lister=$this->add('CompleteLister',null,'cc_lister',['view/emails/email-detail','cc_lister']);
			$cc_lister->setSource($cc_raw);
		}else{
			$this->template->set('cc_lister',"");
		}

		$this->template->setHTML('email_body',$model['description']);
		$this->template->setHTML('attachment_count',$model['attachment_count']);
		
		if(!$model['attachment_count']){
			$this->template->tryDel('check_attach');
		}
		$attach_m = $this->add('xepan\communication\Model_Communication_Attachment');
		$attach_m->addCondition('communication_id',$m->id);
		$attach_m->addCondition('type','attach');
		$attach=$this->add('xepan\communication\View_Lister_Attachment',null,'Attachments');
		$attach->setModel($attach_m);
			
		// $(".reply").click(function(){
  //       $('.compose-email-view-popup').show();
  //   });

  //   $("li.reply-all").click(function(){
  //       $('.compose-email-view-popup').show();
  //   });

  //   $("li.forward").click(function(){
  //       $('.compose-email-view-popup').show();
  //   });

		$this->js('click',[$this->js()->show()->_selector('.compose-email-view-popup'),$this->js()->reload()])->_selector('.reply');
		$this->js('click',[$this->js()->show()->_selector('.compose-email-view-popup'),$this->js()->reload()])->_selector('li.reply-all');
		$this->js('click',[$this->js()->show()->_selector('.compose-email-view-popup'),$this->js()->reload()])->_selector('li.forward');
		$this->add('xepan\base\Controller_Avatar',['options'=>['size'=>45,'border'=>['width'=>0]],'name_field'=>'communication_with','default_value'=>'']);	

		return $m;

	}
	function defaultTemplate(){
		return ['view/emails/email-detail'];
	}
}