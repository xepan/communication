<?php
namespace xepan\communication;
class View_EmailDetail extends \View{
	function init(){
		parent::init();
	}
	function setModel($model){
		$m=parent::setModel($model);
		$this->template->setHTML('email_body',$model['description']);

		$to_raw=$m['to_raw'];
		$to_str = "";
			foreach ($to_raw as $name) {
				$to_str.= $name['name']."<".$name['email'].">,";
			}

		$this->template->set('to_name',$to_str);
		
		$cc_raw=$m['cc_raw'];
		$cc_str = "";
			foreach ($cc_raw as $name) {
				$email=$this->add('xepan\base\Model_Contact_Email');
				$email->addCondition('value',$name['email']);
				$email->tryLoadAny();
				if($email->loaded()){
					$contact=$email->ref('contact_id');
				}
				$cc_str.= $name['name']."<".$name['email'].">,";
			}

		$this->template->trySet('cc_name',$cc_str);

		$bcc_raw=$m['bcc_raw'];
		$bcc_str = "";
			foreach ($bcc_raw as $name) {
				$email=$this->add('xepan\base\Model_Contact_Email');
				$email->addCondition('value',$name['email']);
				$email->tryLoadAny();
				if($email->loaded()){
					$contact=$email->ref('contact_id');
				}
				$bcc_str.= $name['name']."<".$name['email'].">,";
			}

		$this->template->trySet('bcc_name',$bcc_str);

		return $m;
	}
	function defaultTemplate(){
		return ['view/emails/email-detail'];
	}
}