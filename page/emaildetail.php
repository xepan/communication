<?php
namespace xepan\communication;

class page_emaildetail extends \xepan\base\Page{
	public $title="Email Details";
	public $breadcrumb=['Home'=>'index','Email'=>'xepan_communication_emails','Details'=>'#'];

	function init(){
		parent::init();
		$email_id=$this->api->stickyGET('email_id');
		// throw new \Exception($email_id, 1);
		
		
		$email_model=$this->add('xepan\communication\Model_Communication_Email');
		$email_model->load($_GET['email_id']);

		$email_model->ref('extra_info')->set('seen_by',$this->app->employee->id)->save();

		$email_detail=$this->add('xepan\communication\View_EmailDetail');

		$email_detail->setModel($email_model);
		$email_detail->add('xepan\base\Controller_Avatar');

		$email_detail->on('click','.reply',function($js,$data)use($email_model){
			return $js->univ()->location($this->api->url('xepan_communication_composeemail',['reply_email'=>true,'communication_id'=>$email_model->id]));
		});

		$email_detail->on('click','li.reply-all',function($js,$data)use($email_model){
			return $js->univ()->location($this->api->url(
										'xepan_communication_composeemail',['reply_email_all'=>true,'communication_id'=>$email_model->id]));
		});
		
		$email_detail->on('click','li.forward',function($js,$data)use($email_model){
			$this->app->memorize('subject',$email_model['title']);
			$this->app->memorize('message',$email_model['description']);
			return $js->univ()->location($this->api->url('xepan_communication_composeemail'));
		});

	}
}