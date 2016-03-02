<?php
namespace xepan\communication;

class page_composeemail extends \Page{
	function init(){
		parent::init();
		// $mail=$this->add('xepan\communication\Model_compose-email');
		// $compose_tab = $this->add('xepan\hr\View_Document',['action'=> $action],null,['view/compose']);

	}


	
	function defaultTemplate(){
		return['view/composeemail'];
	}
}