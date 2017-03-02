<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function init(){
		parent::init();
		$this->js('reload')->reload();

		$this->add('xepan\communication\View_EmailsTopMenu',null,'email_header_tools');

	}

	function formatRow(){
		if($this->model['is_starred']){
			$this->current_row['starred']='starred';
		}else{
			$this->current_row['starred']='';
		}
		if($this->model['is_read']){
				$this->current_row['unread']='';
		}else{
				$this->current_row['unread']='unread';
			
		}
		// $einfo =$this->model['extra_info'];
		// if(isset($einfo['seen_by']) And is_array($einfo['seen_by'])){
		// 	if(in_array($this->app->employee->id, $einfo['seen_by'])){
		// 		$this->current_row['unread']='';
		// 	}else{
		// 	}
		// }
		
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


		$this->current_row['name'] = substr(strip_tags($this->current_row['communication_with']), 0, 15);
		$this->current_row['body'] = substr(strip_tags($this->current_row['body']), 0, 150);
		if($this->model['status']=='Sent'){
			$this->current_row['email_name'] =$email_model['name']." / ".$this->model['status'];
		}else{
			$this->current_row['email_name'] =$email_model['name'];
		}

		$this->current_row_html['description_clear'] = htmlentities($this->model['description']);
		$this->current_row_html['title_clear'] = htmlentities($this->model['title']);

		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}