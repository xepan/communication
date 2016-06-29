<?php
namespace xepan\communication;

class page_generalsetting extends \xepan\communication\page_sidebar{
	public $title="General Settings";
	function init(){
		parent::init();

		/*General Email Setting*/
		if(!$this->api->auth->model->isSuperUser()){
			$this->add('View_Error')->set('Sorry, you are not permitted to handle this section, Ask respective authority / SuperUser');
			return;
		}else{
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
			$form->addSubmit('Update')->addClass('btn btn-primary');

			if($form->isSubmitted()){
				$misc_config->setConfig('TIME_ZONE',$form['time_zone'],'base');
				
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
									'company_description'=>"text",
									],
						'config_key'=>'COMPANY_AND_OWNER_INFORMATION',
						'application'=>'communication'
					]);
			$company_m->tryLoadAny();
			$c_form = $this->add('Form_Stacked',null,'company_info');
			$c_form->setModel($company_m);
			$c_form->addSubmit('Save')->addClass('btn btn-primary');
			
			if($c_form->isSubmitted()){
				$c_form->save();
				$c_form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
			}
		}


	}
	
	function defaultTemplate(){
		return ['page/general-setting'];
	}
}