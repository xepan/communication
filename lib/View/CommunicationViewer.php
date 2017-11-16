<?php
namespace xepan\communication;
class View_CommunicationViewer extends \View{
	function init(){
		parent::init();

		$id = $_GET['comm_id'];
		$communication_model = $this->add('xepan\communication\Model_Communication');
		$communication_model->tryLoadBy('id',$id);
		
		if(!$communication_model->loaded()){
			$this->add('View')->setElement('H4')->set('Communication not found, It might be deleted')->addClass('project-box-header red-bg well-sm')->setstyle('color','white');
			return;
		}

		$grid = $this->add('View',null,null,['view/communication/viewer']);
		$grid->setModel($communication_model);
		$grid->template->trySetHtml('detail',$communication_model['description']);
	}
}