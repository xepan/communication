<?php
namespace xepan\communication;

class page_emaildetail extends \xepan\base\Page{
	public $title="Email Details";
	public $breadcrumb=['Home'=>'index','Email'=>'xepan_communication_emails','Details'=>'#'];

	function init(){
		parent::init();
		$email_id=$this->api->stickyGET('email_id');
		// throw new \Exception($email_id, 1);
		
		
		$email_model=$this->add('xepan\communication\Model_Communication_Email_Received');
		$email_model->load($_GET['email_id']);

		$email_model->ref('extra_info')->set('seen_by',$this->app->employee->id)->save();
		// echo $email_model->ref('extra_info')->get('seen_by');
		// exit;

		// $email_model->tryLoadAny();
		$email_detail=$this->add('xepan\communication\View_EmailDetail');

		$email_detail->setModel($email_model);


		$email_detail->on('click','li.reply',function($js,$data)use($email_model){
			return $js->univ()->location($this->api->url('xepan_communication_composeemail',['to_email_array'=>json_encode($email_model['to_raw'])]));
		});
	}
}