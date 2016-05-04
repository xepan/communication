<?php

namespace xepan\communication;

class View_Lister_Communication extends \CompleteLister{
	public $contact_id;
	
	function init(){
		parent::init();

		$self = $this;
		$self_url = $this->app->url(null,['cut_object'=>$this->name]);
		
		$vp = $this->add('VirtualPage');
		$vp->set(function($p)use($self,$self_url){

			$contact_id = $this->api->stickyGET('contact_id');	
			$model_contact = $this->add('xepan\base\Model_Contact');
			$model_contact->load($contact_id);
			
			$form = $p->add('Form');
			$form->setLayout('view\communicationform');
			$type_field = $form->addField('dropdown','type')->setValueList(['Email'=>'Email','Phone'=>'Call','Comment'=>'Personal','SMS'=>'SMS']);
			$form->addField('title');
			$form->addField('xepan\base\RichText','body');
			$form->addField('dropdown','from_email')->setModel('xepan\hr\Model_Post_Email_MyEmails');
			$form->addField('line','email_to')->set(implode(", ", $model_contact->getEmails()));
			$form->addField('line','cc_mails');
			$form->addField('line','bcc_mails');
			$form->addField('line','from_phone');
			$form->addField('DropDown','from_person')->setModel('xepan\hr\Employee');
			$form->addField('line','called_to')->set(array_pop(array_reverse($model_contact->getPhones())));
			$form->addField('line','from_number');
			$form->addField('line','sms_to');
			$form->addSubmit('Save');

			$type_field->js(true)->univ()->bindConditionalShow([
					'Email'=>['from_email','email_to','cc_mails','bcc_mails'],
					'Phone'=>['from_phone','from_person','called_to'],
					'Personal'=>['title','type','body'],
					'SMS'=>['from_number','sms_to']
				],'div.atk-form-row');

			if($form->isSubmitted()){				
					$commtype = $form['type'];
					
					$communication = $p->add('xepan\communication\Model_Communication_'.$commtype);
					$communication->addCondition('from_id',$this->app->employee->id);
					$communication->addCondition('to_id',$model_contact->id);

					switch ($commtype) {
						case 'Email':
							$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
							$send_settings->tryLoad($form['from_email']?:-1);
							$_from = $send_settings['from_email'];
							$_from_name = $send_settings['from_name'];
							$_to_field='email_to';
							break;
						case 'Phone':
							$_from = $form['from_phone'];
							$_from_name = $this->add('xepan\hr\Model_Employee')->load($form['from_person'])->get('name');
							$_to_field='called_to';
							$send_settings = $_from;
							break;
						case 'SMS':
							$send_settings = $this->add('xepan\communication\Model_Epan_SMSSetting');
							$send_settings->load($form['from_sms']);
							$_from = $email_settings['from_number'];
							$_from_name = $email_settings['from_sms_code'];
							$_to_field='sms_to';
							break;
						case 'Comment':
							$_from = $this->app->employee->id;
							$_from_name = $this->app->employee['name'];
							$_to = $model_contact->id;
							$_to_name = $model_contact['name'];
							$_to_field=null;
							$communication->addTo($_to, $_to_name);
							break;
						default:
							break;
					}

					$communication->setFrom($_from,$_from_name);
					
					$communication->setSubject($form['title']);
					$communication->setBody($form['body']);

					if($_to_field){	
						foreach (explode(',',$form[$_to_field]) as $to) {
							$communication->addTo(trim($to));
						}			
					}
					
					if($form['bcc_mails']){
						foreach (explode(',',$form['bcc_mails']) as $bcc) {
							$communication->addBcc($bcc);
						}
					}

					if($form['cc_mails']){
						foreach (explode(',',$form['cc_mails']) as $cc) {
							$communication->addCc($cc);
						}
					}
					
					if(!$communication->verifyTo($form[$_to_field], $contact_id)){
						throw new \Exception("Error Processing Request", 1);	
					}

					$communication->send($send_settings);
		
					$this->app->db->commit();
					$form->js()->univ()->successMessage('Done')->execute();
			}
		});	
			

		$this->js('click',$this->js()->univ()->dialogURL("NEW COMMUNICATION",$this->api->url($vp->getURL(),['contact_id'=>$this->contact_id])))->_selector('.create');

		$this->js('click',$this->js()->univ()->alert("Send All As Pdf"))->_selector('.inform');	
	}

	function formatRow(){
		$to_mail = json_decode($this->model['to_raw'],true);
		$to_lister = $this->app->add('CompleteLister',null,null,['view/communication1','to_lister']);
		$to_lister->setSource($to_mail);
			
		$cc_raw = json_decode($this->model['cc_raw'],true);
		$cc_lister = $this->app->add('CompleteLister',null,null,['view/communication1','cc_lister']);
		$cc_lister->setSource($cc_raw);

		$from_mail = json_decode($this->model['from_raw'],true);
		$from_lister = $this->app->add('CompleteLister',null,null,['view/communication1','from_lister']);
		$from_lister->setSource($from_mail);

		$attach=$this->app->add('CompleteLister',null,null,['view/communication1','Attachments']);
		$attach->setModel('xepan\communication\Communication_Attachment')->addCondition('communication_email_id',$this->model->id);

		$this->current_row_html['description'] = $this->current_row['description'];
		
		if($this->model['attachment_count'])
			$this->current_row_html['attachment'] = '<span><i style="color:green" class="fa fa-paperclip"></i></span>';
		else
			$this->current_row_html['attachment']='';

		$this->current_row_html['to_lister'] = $to_lister->getHtml();
		$this->current_row_html['cc_lister'] = $cc_lister->getHtml();
		$this->current_row_html['from_lister'] = $from_lister->getHtml();
		$this->current_row_html['Attachments'] = $attach->getHtml();
		return parent::formatRow();
	}

	function defaultTemplate(){
		return['view\communication1'];
	}
}