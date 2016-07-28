<?php
namespace xepan\communication;
class View_CommunicationViewer extends \View{
	function init(){
		parent::init();

		$id = $_GET['comm_id'];
		$communication_model = $this->add('xepan\communication\Model_Communication');
		$grid = $this->add('View',null,null,['view/communication/viewer']);
		$grid->setModel($communication_model->load($id));
		$grid->template->trySetHtml('detail',$communication_model['description']);
	}
	
}