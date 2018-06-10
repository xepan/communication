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
		// $x = $this->app->side_menu->addMenu(['Email Content','icon'=>' fa fa-edit'],'xepan_communication_sidebar')->setAttr(['title'=>'Email Content']);
        // $x->addItem([' Admin User Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_admin')->setAttr(['title'=>'Admin User Email Setting']);
        // $x->addItem([' Frontend User Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_usertool')->setAttr(['title'=>'Frontend User Email Setting']);
		
		// $this->app->side_menu->addItem(['General Setting','icon'=>' fa fa-dashboard'],'xepan_communication_generalsetting')->setAttr(['title'=>'General Setting']);
		// $this->app->side_menu->addItem(['Admin Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_admin')->setAttr(['title'=>'Admin User Email Setting']);
		// $this->app->side_menu->addItem(['User Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_usertool')->setAttr(['title'=>'Frontend User Email Setting']);
		// $this->app->side_menu->addItem(['Country/State','icon'=>' fa fa-users'],'xepan_communication_general_countrystate')->setAttr(['title'=>'Country/ State Management']);
	}
}