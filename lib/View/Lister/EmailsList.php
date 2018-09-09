<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function init(){
		parent::init();
		$this->js('reload')->reload();

	}

	function formatRow(){
		if($this->model['is_starred']){
			$this->current_row['starred']='starred';
		}else{
			$this->current_row['starred']='';
		}
		// $unread_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
		// $unread_email->addCondition('communication_id',$this->model->id);
		// $unread_email->addCondition('contact_id',$this->app->employee->id);
		// $unread_email->addCondition('is_read',true);
		// $unread_email->tryLoadAny();
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

		if($this->model['related_id']){
			$this->current_row['replied_color']='gray';
		}else{
			$this->current_row['replied_color']='transparent';
		}

		if($this->model['status'] == "Draft"){
			$this->current_row['draft']='draft-message';
		}

		$mailbox=explode('#', $this->model['mailbox']);
		// $email_model=$this->add('xepan\communication\Model_Communication_EmailSetting');
		// $email_model->tryLoadBy('email_username',$mailbox[0]);


		$this->current_row['body'] = strip_tags($this->current_row['body']);
		if($this->model['status']=='Sent'){
			$this->current_row['email_name'] =$mailbox[0]." / ".$this->model['status'];
		}else{
			$this->current_row['email_name'] =$mailbox[0];
		}

		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}