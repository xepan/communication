<?php

namespace xepan\communication;

/**
* 
*/
class View_ComposeMessagePopup extends \View{
	function init(){
		parent::init();
		$emp_id = $this->app->stickyGET('employee_id');
		$employee = $this->add('xepan\hr\Model_Employee');
		$employee->addCondition('status','Active');
		if($emp_id){
			// throw new \Exception($emp_id, 1);
			
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
		$f = $this->add('Form',null,'form');
		$send_to_all_field = $f->addField('Checkbox','send_to_all', "Send to All Message");
		$message_to_field = $f->addField('xepan\base\DropDown','message_to')->addClass('xepan-push');
		$message_to_field->setModel($employee);
		$cc_field = $f->addField('xepan\base\DropDown','cc')->addClass('xepan-push');
		$cc_field->setModel($employee);

		$f->addField('line','subject');
		
		$message_to_field->setAttr(['multiple'=>'multiple']);
		$cc_field->setAttr(['multiple'=>'multiple']);
		$message_field = $f->addField('xepan\base\RichText','message')->validate('required');
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
			$send_msg['mailbox'] = "InternalMessage";
			$send_msg['from_id'] = $this->app->employee->id;
			$send_msg['from_raw'] = ['name'=>$this->app->employee['name'],'id'=>$this->app->employee->id];
			$send_msg['to_raw'] = json_encode($to_raw);
			$send_msg['cc_raw'] = json_encode($cc_raw);
			$send_msg['title'] = $f['subject'];
			$send_msg['description'] = $f['message'];
			$send_msg->save();

			$upload_images_array = explode(",",$f['attachment']);
			foreach ($upload_images_array as $file_id) {
				$send_msg->addAttachment($file_id);
			}

			$js=[
					$f->js()->univ()->successMessage('Message Send'),
					// $f->js()->closest('.compose-message-view-popup')->removeClass('slide-up'),//->_selector('.compose-message-view-popup');
					$f->js()->_selector('.internal-conversion-lister')->trigger('reload')
				];

			$f->js(null,$js)->reload()->execute();
		}
		$this->js('click',$this->js()->removeClass('slide-up')->_selector('.compose-message-view-popup'))->_selector('.close-compose-message-popup');
	}

	function defaultTemplate(){
		return ['view/emails/internalmsgcompose'];
	}
}