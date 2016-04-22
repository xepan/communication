<?php
namespace xepan\communication;

class View_Lister_EmailsList extends \CompleteLister{
	function formatRow(){
		if($this->model['is_starred']){
			$this->current_row['starred']='starred';
		}else{
			$this->current_row['starred']='';
		}

		$seen_by=$this->model->ref('extra_info')->get('seen_by');
		
		if($seen_by){
			$this->current_row['unread']='';
		}else{
			$this->current_row['unread']='unread';
		}
		if(!$this->model['attachment_count']){
			$this->current_row['check_attach']='';
		}

		$email_model=$this->add('xepan\base\Model_Epan_EmailSetting');
		$email_model->tryLoadBy('email_username',$this->model['to_raw'][0]['email']);


		$this->current_row['body'] = strip_tags($this->current_row['body']);
		$this->current_row['email_name'] =$email_model['name'];

		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/list'];
	}
}