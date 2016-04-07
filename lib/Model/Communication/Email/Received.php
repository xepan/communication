<?php

/**
* description: ATK Model
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/
namespace xepan\communication;
class Model_Communication_Email_Received extends Model_Communication_Email{

	function init(){
		parent::init();
		
		$this->addCondition('status','Received');		
		$this->addCondition('direction','In');

	}

	function findContact($save=false){
		if(!is_array($this['from_raw'])) {
			$this['from_raw'] = json_decode($this['from_raw'],true);
		}

		$email = $this['from_raw']['email'];
		$contact_emails = $this->add('xepan\base\Model_Contact_Info');
		$contact_emails->addCondition('value',$email);
		$contact_emails->tryLoadAny();

		if($contact_emails->loaded()){
			$this['from_id'] = $contact_emails['contact_id'];
			if($save) $this->save();
		}
	}

	function createTicket(){
		$ticket = $this->add('xepan\crm\Model_SupportTicket');
		$ticket['status'] = "Pending";
		$ticket['uid'] = $this['uid'];
		$ticket['from_id'] = $this['from_id'];
		$ticket['from_email'] = $this['from_raw']['email'];
		$ticket['from_name'] = $this['from_raw']['name'];
		$ticket['to'] = $this['to_raw'];
		$ticket['to_id'] = $this['to_id'];
		$ticket['to_email'] = $this['to_raw']['email'];
		$ticket['cc'] = $this['cc_raw']['email'];
		$ticket['subject'] = $this['title'];
		$ticket['message'] = $this['description'];
		$ticket['contact_id'] = $this['from_id'];
		$ticket->save();

		// foreach ($this->attachment() as $attach) {
		// 	$ticket->addAttachment($attach['attachment_url_id'],$attach['file_id']);	
		// }
		// $t->autoReply();

	}

}
