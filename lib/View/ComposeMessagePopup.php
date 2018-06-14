<?php

namespace xepan\communication;

/**
* 
*/
class View_ComposeMessagePopup extends \View{
	public $subject="";
	public $message="";
	public $mode="";
	public $communication_id="";

	public $related_contact_id=null;

	function init(){
		parent::init();
		$this->communication_id = $this->app->stickyGET('communication_id');
		$this->mode = $this->app->stickyGET('mode');
		$msg_model = $this->add('xepan\communication\Model_Communication_AbstractMessage');
		if($this->communication_id)
			$msg_model->load($this->communication_id);

		if($this->related_contact_id){
			$msg_model->addCondition('related_contact_id',$this->related_contact_id);
		}

		$emp_id = $this->app->stickyGET('employee_id');
		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addCondition('status','Active');
		$employee->addCondition('id','<>',$this->app->employee->id);

		if($emp_id && ($this->mode != "msg-fwd")){
			$employee->addCondition('id',$emp_id);
		}

		$employee->addExpression('employee_message_to')->set(function($m,$q){

			// return $q->expr("CONCAT([0],' :: ',IF([1] > 0,'Present','Absent'))",
			return $q->expr("CONCAT([0],' :: [ ',IF([1] > 0,'PRESENT','ABSENT'),' ]')",
					[
						$m->getElement('name'),
						$m->getElement('check_login'),
					]);

		});

		$employee->title_field = 'employee_message_to';
		$f = $this->add('Form');
		$f->setLayout(['view/emails/internalmsgcompose']);

		$send_to_all_field = $f->addField('Checkbox','send_to_all', "Send Message to All Employee`s");
		$message_to_field = $f->addField('xepan\base\DropDown','message_to')->addClass('xepan-push');
		$message_to_field->validate_values=false;



		$cc_field = $f->addField('xepan\base\DropDown','cc')->addClass('xepan-push');
		$cc_field->validate_values=false;

		if($this->mode == 'msg-reply'){
			$msg_to=$msg_model->getReplyMessageFromTo()['to'][0];
			// $msg_to=$msg_model['from_raw'];
			// var_dump($msg_to);
			$message_to_field->js(true)->append("<option value='".$msg_to['id']."'>".$msg_to['name']." </option>")->trigger('change');
			$message_to_field->set($msg_to['id']);


			$this->subject="Re: ".$msg_model['title'];
			$this->message="<br/><br/><br/><br/><blockquote>".$msg_model['description']."<blockquote>";
		}
		
			// $msg_cc=$msg_model['cc_raw'];
			// foreach ($msg_cc as $cc_field_msg) {
			// 	$msg_cc [] = $cc_field_msg['id'];
			// 	$cc_field->js(true)->append("<option value='".$cc_field_msg['id']."'>".$cc_field_msg['name']." </option>")->trigger('change');
			// }
			// $cc_field->set($msg_cc)->js(true)->trigger('changed');
	
		if($this->mode == 'msg-reply-all'){
			$msg_to =[];		
			foreach ($msg_model->getReplyMessageFromTo(true)['to'] as $to_field_msg) {
				$msg_to [] = $to_field_msg['id'];
				$message_to_field->js(true)->append("<option value='".$to_field_msg['id']."'>".$to_field_msg['name']." </option>")->trigger('change');
			}
			// var_dump($msg_to);
			$message_to_field->set($msg_to);

			$msg_cc =[];		
			foreach ($msg_model->getReplyMessageFromTo()['cc'] as $cc_field_msg) {
				$msg_cc [] = $cc_field_msg['id'];
				$cc_field->js(true)->append("<option value='".$cc_field_msg['id']."'>".$cc_field_msg['name']." </option>")->trigger('change');
			}
			$cc_field->set($msg_cc);

			$this->subject="Re: ".$msg_model['title'];
			$this->message="<br/><br/><br/><br/><blockquote>".$msg_model['description']."<blockquote>";
		}

		if($this->mode != "msg-reply" && $this->mode != 'msg-reply-all'){
			$cc_field->setModel($employee);
			$message_to_field->setModel($employee);
		}
		if($this->mode == 'msg-fwd'){
			$this->subject="Fwd: ".$msg_model['title'];
			$this->message="<br/><br/><br/><br/><blockquote> ---------- Forwarded message ----------<br>".$msg_model['description']."</blockquote>";

			$attach_m = $this->add('xepan\communication\Model_Communication_Attachment');
			$attach_m->addCondition('communication_id', $this->communication_id);
			$attach=$f->layout->add('xepan\communication\View_Lister_Attachment',null,'existing_attachments');
			$attach->setModel($attach_m);

		}
		
		$message_to_field->setAttr(['multiple'=>'multiple']);
		$cc_field->setAttr(['multiple'=>'multiple']);
		$f->addField('line','subject')->set($this->subject);
		$message_field = $f->addField('xepan\base\RichText','message')->validate('required');
		$message_field->set($this->message);
		$message_field->options = ['toolbar1'=>"styleselect | bold italic fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor",'menubar'=>false];
		
		$multi_upload_field = $f->addField('xepan\base\Form_Field_Upload','attachment',"")
									->allowMultiple()->addClass('xepan-padding');
		$filestore_image=$this->add('xepan\filestore\Model_File',['policy_add_new_type'=>true]);
		$multi_upload_field->setModel($filestore_image);
		
		$send_to_all_field->js(true)->univ()->bindConditionalShow([
			''=>['message_to','cc'],
			'*'=>[]
		],'div.atk-form-row');

		$f->addSubmit('Send message')->addClass('btn btn-success pull-right xepan-margin-top-small');
		
		if($f->isSubmitted()){

			$to_raw = [];
			$cc_raw = [];
			if($f['send_to_all']){
				$all_emp = $this->add('xepan\hr\Model_Employee');
				$all_emp->addCondition('status','Active');
				foreach ($all_emp as $emp) {
					$to_raw[] = ['name'=>$emp['name'],'id'=>$emp->id];
				}
			}else{
				if(!$f['message_to']){
					$f->displayError('message_to',"must not be empty Message to");
				}
				$to_emp = $this->add('xepan\hr\Model_Employee');
				foreach (explode(',', $f['message_to']) as $name => $id) {
					$to_emp->load($id);
					$to_raw[] = ['name'=>$to_emp['name'],'id'=>$id];
				}
				if($f['cc']){
						$cc_emp = $this->add('xepan\hr\Model_Employee');
						foreach (explode(',', $f['cc']) as $name => $id) {
							$cc_emp->load($id);
							$cc_raw[] = ['name'=>$cc_emp['name'],'id'=>$id];
					}
				}
			}
			
			$send_msg = $this->add('xepan\communication\Model_Communication_MessageSent');
			if($this->related_contact_id)
				$send_msg['related_contact_id'] = $this->related_contact_id;
				
			$send_msg['related_contact_id'] = $msg_model['related_contact_id']; // if communication is around some contact
			$send_msg['mailbox'] = "InternalMessage";
			$send_msg['from_id'] = $this->app->employee->id;
			$send_msg['from_raw'] = ['name'=>$this->app->employee['name'],'id'=>$this->app->employee->id];
			$send_msg['to_raw'] = json_encode($to_raw);
			$send_msg['cc_raw'] = json_encode($cc_raw);
			$send_msg['title'] = $f['subject'];
			$send_msg['description'] = $f['message'];
			$send_msg->save();

			if($f['message_to'] OR $f['send_to_all']){
				foreach (explode(',', $f['message_to']) as $name => $id) {
					$comm_read_model = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
					$comm_read_model['is_read'] = false;
					$comm_read_model['communication_id'] = $send_msg->id;
					$comm_read_model['contact_id'] = $id;
					$comm_read_model['type'] = "TO";
					$comm_read_model->save();
				}
			}
			if($f['cc']){
				foreach (explode(',', $f['cc']) as $name => $id) {
					$comm_read_model = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
					$comm_read_model['is_read'] = false;
					$comm_read_model['communication_id'] = $send_msg->id;
					$comm_read_model['contact_id'] = $id;
					$comm_read_model['type'] = "CC";
					$comm_read_model->save();
				}	
			}

			$upload_images_array = array();
			if($this->mode == "msg-fwd"){
				$attach_m = $this->add('xepan\communication\Model_Communication_Attachment');
				$attach_m->addCondition('communication_id', $this->communication_id);
				foreach ($attach_m as  $existing_attachment_model) {
						$upload_images_array [] = $existing_attachment_model['file_id'];
				}
			}else{
				$upload_images_array = explode(",",$f['attachment']);
			}

			foreach ($upload_images_array as $file_id) {
				$send_msg->addAttachment($file_id);
			}

			$this->app->stickyForget('communication_id');
			$this->app->stickyForget('mode');

			$js=[
					$f->js()->univ()->successMessage('Message Send'),
					// $f->js()->closest('.compose-message-view-popup')->removeClass('slide-up'),//->_selector('.compose-message-view-popup');
					$f->js()->_selector('.internal-conversion-lister')->trigger('reload')
				];

			$f->js(null,$js)->reload()->execute();
		}
		$this->js('click',$this->js()->removeClass('slide-up')->_selector('.compose-message-view-popup'))->_selector('.close-compose-message-popup');
	}

	// function defaultTemplate(){
	// 	return ['view/emails/internalmsgcompose'];
	// }
}