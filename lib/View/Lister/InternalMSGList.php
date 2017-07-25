<?php
namespace xepan\communication;

class View_Lister_InternalMSGList extends \CompleteLister{
	function init(){
		parent::init();
		$this->js('reload')->reload();
		$vp = $this->add('VirtualPage');
	  	$vp->set(function($vp){
			$this->app->stickyGET('mark_id');
			$unread_msg = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
			$unread_msg->addCondition('communication_id',$_POST['mark_id']);
			$unread_msg->addCondition('contact_id',$this->app->employee->id);
			$unread_msg->addCondition('is_read',true);
			$unread_msg->tryLoadAny();
			if(!$unread_msg->loaded()){
					$unread_msg->save();
			}
			exit;
	   	});

		$this->js('click',
				[
					$this->js()->_selectorThis()->parent()->find('.text')->toggle('hide'),
					$this->js()->_selectorThis()->removeClass('unread'),
					$this->js()->_selectorThis()->univ()->ajaxec($vp->getURL(),['mark_id'=>$this->js()->_selectorThis()->data('id')])
				])
				->_selector('.internal-conversion-lister .name');
		$delete_id = $this->app->stickyGET('do_delete_employee_id');		
		if($delete_id){
			$delete_m = $this->add('xepan\communication\Model_Communication_AbstractMessage');
			$delete_m->addCondition('id',$delete_id);
			$delete_m->tryLoadAny();
			if($delete_m->loaded()){
				$delete_m->delete();
			}
			$this->js(null,$this->js()->univ()->successMessage('Deleted Successfully'))->_selector('.internal-conversion-lister')->trigger('reload')->execute();
		}

		$this->on('click','.do-delete-conversion')->univ()->confirm('Are you sure?')
			->ajaxec(array(
            	$this->app->url(),
            	'do_delete_employee_id'=>$this->js()->_selectorThis()->data('id')

        ));


	}
	function setModel($model){
		$m = parent::setModel($model);
		return $m;
	}

	function formatRow(){
		$attach=$this->add('xepan\communication\View_Lister_Attachment',null,'Attachments');
		$attach->setModel('xepan\communication\Communication_Attachment')->addCondition('communication_id',$this->model->id);
		// $a = $this->add('xepan\communication\Model_Communication_Attachment')->addCondition('communication_id',$this->model->id);
		$this->current_row_html['Attachments'] = $attach->getHtml();
		
		$to_array=[];
		foreach ($this->model['to_raw'] as $to) {
			$to_array[]=$to['name'];
		}
		$this->current_row_html['to_name'] = implode(", ", $to_array);
		$this->current_row_html['from_name'] = $this->model['from_raw']['name'];

		$cc_array=[];
		foreach ($this->model['cc_raw'] as $cc) {
			$cc_array[]=$cc['name'];
		}
		$this->current_row_html['cc_name'] = implode(", ", $cc_array);


		
		if($this->model['from_id'] === $this->app->employee->id)
			$this->current_row_html['position'] = "left";
		else
			$this->current_row_html['position'] = "right";
		
		// if($this->model['is_starred']){
		// 	$this->current_row['starred']='starred';
		// }else{
		// 	$this->current_row['starred']='';
		// }
		$unread_msg = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
		$unread_msg->addCondition('communication_id',$this->model->id);
		$unread_msg->addCondition('contact_id',$this->app->employee->id);
		$unread_msg->addCondition('is_read',true);
		$unread_msg->tryLoadAny();
		if($unread_msg->loaded()){
			$this->current_row['unread']='';
			$this->current_row['class']='danger';
		}else{
				$this->current_row['unread']='unread';
				$this->current_row['class']='success';
		}
		// $einfo =$this->model['extra_info'];
		// if(isset($einfo['seen_by']) And is_array($einfo['seen_by'])){
		// 	if(in_array($this->app->employee->id, $einfo['seen_by'])){
		// 		$this->current_row['unread']='';
		// 	}else{
		// 		$this->current_row['unread']='unread';
		// 	}
		// }

		$this->current_row_html['message']  = $this->model['description'];
		$this->current_row_html['subject']  = $this->model['title']? " [ ". $this->model['title'] . " ]":"";
		
		if($this->app->employee['scope'] !== 'SuperUser'){
			$this->current_row_html['trash_wrapper'] = '<div>&nbsp; </div>';
			$this->js(true,$this->js()->hide()->_selector('.do-delete-conversion'))->_selector('.do-delete-conversion');			
		}
		
		parent::formatRow();
	}
	function defaultTemplate(){
		return ['view/emails/internalmsglist'];
	}
}