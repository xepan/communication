<?php

namespace xepan\communication;

class View_Lister_Communication extends \CompleteLister{
	public $contact_id;
	
	function init(){
		parent::init();
		
		if(!$this->contact_id)
			return;			
		$this->addClass('xepan-communication-lister');
		$this->js('reload')->reload();
		
		$self = $this;
		$self_url = $this->app->url(null,['cut_object'=>$this->name]);
		$vp = $this->add('VirtualPage');
		$vp->set(function($p)use($self,$self_url){

			$contact_id = $this->api->stickyGET('contact_id');	
			$model_contact = $this->add('xepan\base\Model_Contact');
			$model_contact->load($contact_id);
			
			$form = $p->add('xepan\communication\Form_Communication',null,null,['form/empty']);
			$form->setContact($model_contact);

			$member_phones = array_reverse($model_contact->getPhones());
			$form->getElement('email_to')->set(implode(", ", $model_contact->getEmails()));
			$form->getElement('notify_email_to')->set(implode(", ", $model_contact->getEmails()));
			$form->getElement('called_to')->set(array_pop($member_phones));

			if($form->isSubmitted()){

				try{
					$this->api->db->beginTransaction();
					$form->process();
					$this->app->db->commit();
				}catch(\Exception $e){
					// if($this->api->db->inTransaction()) 
						$this->api->db->rollback();
					throw $e;
				}

				$form->js(null,$this->js()->_selector('.xepan-communication-lister')->trigger('reload'))->univ()->successMessage('Done')->closeDialog()->closeDialog()->execute();
			}
		});	
		
		$this->js('click',$this->js()->univ()->dialogURL("NEW COMMUNICATION",$this->api->url($vp->getURL(),['contact_id'=>$this->contact_id])))->_selector('.create');
		$this->js('click',$this->js()->univ()->frameURL("SEND ALL COMMUNICATION",$this->api->url('xepan_communication_contactcommunications',['contact_id'=>$this->contact_id])))->_selector('.inform');
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
		if($this->model['communication_type']==='Email'){
			$this->current_row_html['cc_lister'] = $cc_lister->getHtml();
		}else{
			$this->current_row_html['cc_lister'] = "";
		}
		$this->current_row_html['from_lister'] = $from_lister->getHtml();
		$this->current_row_html['Attachments'] = $attach->getHtml();
		return parent::formatRow();
	}

	function defaultTemplate(){
		return['view\communication1'];
	}
}