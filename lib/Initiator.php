<?php

namespace xepan\communication;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_communication';

	function init(){
		parent::init();
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates'));

		if($this->app->is_admin){
			$this->app->side_menu->addItem('Emails','xepan_communication_emails');
		}
		
	}
}
