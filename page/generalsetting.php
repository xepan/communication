<?php
namespace xepan\communication;

class page_generalsetting extends \xepan\base\Page{
	public $title="General Settings";
	function init(){
		parent::init();
		$this->app->side_menu->addItem(['Admin Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_admin');
		$this->app->side_menu->addItem(['User Setting','icon'=>' fa fa-users'],'xepan_communication_general_emailcontent_usertool');

		/*General Email Setting*/
		$setiingview=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_communication_general_email'],'general_setting',['view/setting/email-setting-grid']);
		$setiingview->setModel('xepan\communication\Communication_EmailSetting');
	
		/*SMS Setting*/
		$sms_view=$this->add('xepan\hr\CRUD',null,'sms_setting',['view/setting/sms-setting-grid']);
		if($sms_view->isEditing()){
			$form=$sms_view->form;
			$form->setLayout('view/setting/form/sms-setting');
			$form->js(true)->find('button')->addClass('btn btn-primary');
		}	
		$sms_view->setModel('xepan\communication\Model_Communication_SMSSetting');

		/*MISC Setting*/
		$misc_config = $this->app->epan->config;
		$misc_time_zone = $misc_config->getConfig('TIME_ZONE');
		$form = $this->add('Form_Stacked',null,'misc_view');
		$time_zone_field=$form->addField('DropDown','time_zone')->set($misc_time_zone);
		$time_zone_field->setValueList(array_combine(timezone_identifiers_list(),timezone_identifiers_list()));
		$form->addSubmit('Update');

		if($form->isSubmitted()){
			$misc_config->setConfig('TIME_ZONE',$form['time_zone'],'base');
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

	}
	
	function defaultTemplate(){
		return ['page/general-setting'];
	}
}