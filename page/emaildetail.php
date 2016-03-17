<?php
namespace xepan\communication;

class page_emaildetail extends \Page{
	public $title="Email Details";
	function init(){
		parent::init();
		$email_id=$this->api->stickyGET('email_id');
		
		$email_model=$this->add('xepan\communication\Model_Communication_Email_Received');
		// $m_date= date('Y-m-d',strtotime($email_model['created_at']));
		// $today_date= date('Y-m-d',strtotime($this->app->now));
		// $diff= date_diff($today_date,$m_date);
		
		// echo $diff;
		
		$email_model->add('misc/Field_Callback','callback_date')->set(function($m){
			if(date('Y-m-d',strtotime($m['created_at']))==date('Y-m-d',strtotime($this->app->now))){
				return $m['created_at']=date('h:i:a',strtotime($m['created_at']));	
			}
			return $m['created_at']=date('M d',strtotime($m['created_at']));
		});
		// $email_model->add('misc/Field_Callback','callback_from')->set(function($m){
		// 	return $m['from_raw']=json_encode($m['from_raw']['email']);
		// });
		// $email_model->add('misc/Field_Callback','callback_to')->set(function($m){
		// 	return $m['to_raw']=json_encode($m['to_raw']['email']);
		// });


		$email_model->addCondition('id',$_GET['email_id']);
		$email_model->tryLoadAny();
		$email_detail=$this->add('xepan\communication\View_EmailDetail');

		$email_detail->setModel($email_model);
	}
}