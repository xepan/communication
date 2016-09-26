<?php
namespace xepan\communication;

class page_generalsetting extends \xepan\communication\page_sidebar{
	public $title="General Settings";
	function init(){
		parent::init();

		/*General Email Setting*/
		$setiingview=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_communication_general_email'],'general_setting',['view/setting/email-setting-grid']);
		$setiingview->setModel('xepan\communication\Communication_EmailSetting');
		
		// /*SMS Setting*/
		$sms_view=$this->add('xepan\hr\CRUD',null,'sms_setting',['view/setting/sms-setting-grid']);
		if($sms_view->isEditing()){
			$form=$sms_view->form;
			$form->setLayout('view/setting/form/sms-setting');
			$form->js(true)->find('button')->addClass('btn btn-primary');
		}	
		$sms_view->setModel('xepan\communication\Model_Communication_SMSSetting');

		// MISC Setting
		$misc_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'time_zone'=>'DropDown'
							],
					'config_key'=>'Miscellaneous_Technical_Settings',
					'application'=>'base'
			]);
		$misc_m->add('xepan\hr\Controller_ACL');
		$misc_m->tryLoadAny();		

		// $misc_config = $this->app->epan->config;
		// $misc_time_zone = $misc_config->getConfig('TIME_ZONE');
		$form = $this->add('Form_Stacked',null,'misc_view');
		$form->setModel($misc_m);

		$time_zone_field=$form->getElement('time_zone')->set($misc_m['time_zone']);
		$time_zone_field->setValueList(array_combine(timezone_identifiers_list(),timezone_identifiers_list()));
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		/*Company Info*/

		$company_m = $this->add('xepan\base\Model_ConfigJsonModel',
				[
					'fields'=>[
								'company_name'=>"Line",
								'company_owner'=>"Line",
								'mobile_no'=>"Line",
								'company_email'=>"Line",
								'company_address'=>"Line",
								'company_pin_code'=>"Line",
								'company_description'=>"xepan\base\RichText",
								'company_logo_absolute_url'=>"Line",
								'company_twitter_url'=>"Line",
								'company_facebook_url'=>"Line",
								'company_google_url'=>"Line",
								'company_linkedin_url'=>"Line",
								],
					'config_key'=>'COMPANY_AND_OWNER_INFORMATION',
					'application'=>'communication'
				]);
		
		$company_m->add('xepan\hr\Controller_ACL');
		$company_m->tryLoadAny();

		$c_form = $this->add('Form_Stacked',null,'company_info');
		$c_form->setModel($company_m);
		$c_form->addSubmit('Save')->addClass('btn btn-primary');
		
		if($c_form->isSubmitted()){
			$c_form->save();
			$c_form->js(null,$c_form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		// $this->add('View',null,'company_info',['view/schema-micro-data','person_info'])->setModel($company_m);


	}
	
	function defaultTemplate(){
		return ['page/general-setting'];
	}
}