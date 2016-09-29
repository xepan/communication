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
		$cc_lister=$this->add('CompleteLister',null,'cc_lister',['view/emails/email-detail','cc_lister']);
		$cc_lister->setSource($cc_raw);

		$this->template->setHTML('email_body',$model['description']);
		$this->template->setHTML('attachment_count',$model['attachment_count']);
		
		if(!$model['attachment_count']){
			$this->template->tryDel('check_attach');
		}

		$attach=$this->add('xepan\communication\View_Lister_Attachment',null,'Attachments');
		$attach->setModel('xepan\communication\Communication_Attachment')->addCondition('communication_id',$m->id);
			
		$compose_view = $this->add('xepan\communication\View_ComposeEmailPopup',['communication_id'=>$_GET['communication_id'],'mode'=>$_GET['mode']],'compose_view');

		$this->js('click',
			$compose_view->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto;" src="vendor\xepan\communication\templates\images\loader.gif"/></div>')->reload(['communication_id'=>$this->model->id,'mode'=>'reply_email']))
			->_selector('.reply');	

		$this->js('click',
			$compose_view->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto;" src="vendor\xepan\communication\templates\images\loader.gif"/></div>')->reload(['communication_id'=>$this->model->id,'mode'=>'reply_email_all']))
			->_selector('li.reply-all');
		
		$this->js('click',$compose_view->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto;" src="vendor\xepan\communication\templates\images\loader.gif"/></div>')->reload(['communication_id'=>$this->model->id,'mode'=>'fwd_email']))
			->_selector('li.forward');	

		return $m;

	}
	function defaultTemplate(){
		return ['view/emails/email-detail'];
	}
}