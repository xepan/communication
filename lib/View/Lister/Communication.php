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
			
			$form = $p->add('xepan\communication\Form_Communication');
			$form->setContact($model_contact);

			$member_phones = array_reverse($model_contact->getPhones());
			$form->getElement('email_to')->set(implode(", ", $model_contact->getEmails()));
			$form->getElement('called_to')->set(array_pop($member_phones));

			if($form->isSubmitted()){

					$form->process();
					$this->app->db->commit();
					$form->js(null,$self->js()->reload())->univ()->successMessage('Done')->execute();
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
		$attach->setModel('xepan\communication\Communication_Attachment')->addCondition('communication_id',$this->model->id);

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