<?php

namespace xepan\communication;

/**
* 
*/
class page_sidebar extends \xepan\base\Page
{
	
	function init()
	{
		parent::init();
		$this->app->side_menu->addItem(['General Setting','icon'=>' fa fa-dashboard'],'xepan_communication_generalsetting')->setAttr(['title'=>'General Setting']);
		$this->app->side_menu->addItem(['Admin Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_admin')->setAttr(['title'=>'Admin User Email Setting']);
		$this->app->side_menu->addItem(['User Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_usertool')->setAttr(['title'=>'Frontend User Email Setting']);
	}
}