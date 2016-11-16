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
		if($emp_id){
			throw new \Exception($emp_id, 1);
			
			$employee->addCondition('id',$emp_id);
		}

		$f = $this->add('Form',null,'form'/*,['form\empty']*/);
		$message_to_field = $f->addField('xepan\base\DropDown','message_to')->addClass('xepan-push');
		$message_to_field->setModel($employee);
		$message_to_field->setAttr(['multiple'=>'multiple']);
		$message_field = $f->addField('xepan\base\RichText','message');
		$message_field->options = ['toolbar1'=>"styleselect | bold italic fontselect fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor backcolor",'menubar'=>false];
		
		$f->addSubmit('Send message')->addClass('btn btn-success pull-right xepan-margin-top-small');
		
		if($f->isSubmitted()){
			$to_raw = [];
				$to_emp = $this->add('xepan\hr\Model_Employee');
				foreach (explode(',', $f['message_to']) as $name => $id) {
					$to_emp->load($id);
					$to_raw[] = ['name'=>$to_emp['name'],'id'=>$id];
			}
					
			$send_msg = $this->add('xepan\communication\Model_Communication_MessageSent');
			$send_msg['mailbox'] = "InternalMessage";
			$send_msg['from_id'] = $this->app->employee->id;
			$send_msg['from_raw'] = ['name'=>$this->app->employee['name'],'id'=>$this->app->employee->id];
			$send_msg['to_raw'] = json_encode($to_raw);
			$send_msg['title'] = $this->app->employee['name'].  "Inter Communication";
			$send_msg['description'] = $f['message'];
			$send_msg->save();

			$js=[
					$f->js()->univ()->successMessage('Message Send'),
					$f->js()->closest('.compose-message-view-popup')->removeClass('slide-up')//->_selector('.compose-message-view-popup');
				];

			$f->js(null,$js)->reload()->execute();
		}
	}

	function defaultTemplate(){
		return ['view/emails/internalmsgcompose'];
	}
}