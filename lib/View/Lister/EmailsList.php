<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function init(){
		parent::init();
		$this->js('reload')->reload();

		$no_debug = $this->app->stickyGET('no_debug');
		$fetch_emails = $this->app->stickyGET('fetch_emails');
		
		if($fetch_emails){
			$this->add('xepan\communication\Controller_Cron',['debug'=>false]);
		}



	}
	function formatRow(){
		if($this->model['is_starred']){
			$this->current_row['starred']='starred';
		}else{
			$this->current_row['starred']='';
		}

		$einfo =$this->model['extra_info'];
		if(isset($einfo['seen_by']) And is_array($einfo['seen_by'])){
			if(in_array($this->app->employee->id, $einfo['seen_by'])){
				$this->current_row['unread']='';
			}else{
				$this->current_row['unread']='unread';
			}
		}
		
		if(!$this->model['attachment_count']){
			$this->current_row['check_attach']='';
		}else{
			$this->current_row_html['check_attach']='<a href="#" class="attachment"><i class="fa fa-paperclip"></i></a>';
		}

		if($this->model['status'] == "Draft"){
			$this->current_row['draft']='draft-message';
		}

		$mailbox=explode('#', $this->model['mailbox']);
		$email_model=$this->add('xepan\communication\Model_Communication_EmailSetting');
		$email_model->tryLoadBy('email_username',$mailbox);


		$this->current_row['body'] = strip_tags($this->current_row['body']);
		if($this->model['status']=='Sent'){
			$this->current_row['email_name'] =$email_model['name']." / ".$this->model['status'];
		}else{
			$this->current_row['email_name'] =$email_model['name'];
		}

		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}