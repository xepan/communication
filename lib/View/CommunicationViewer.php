<?php
namespace xepan\communication;
class View_CommunicationViewer extends \View{
	function init(){
		parent::init();

		$telemarketing = $this->add('xepan/communication/Model_Communication')->addCondition('communication_type','TeleCommunication');
		$grid = $this->add('Grid');
		$grid->setModel($telemarketing);


	}
	
}