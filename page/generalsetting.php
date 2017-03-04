<?php
namespace xepan\communication;

class page_generalsetting extends \xepan\communication\page_sidebar{
	public $title="General Settings";
	function init(){
		parent::init();
		
		/*General Email Setting*/
		$email_setting= $this->add('xepan\communication\Model_Communication_EmailSetting');
		$settingview=$this->add('xepan\hr\CRUD',['action_page'=>'xepan_communication_general_email'],'general_setting',['view/setting/email-setting-grid']);
		$settingview->setModel($email_setting);
		$settingview->grid->addQuickSearch(['name','email_username']);
		
		// /*SMS Setting*/
		$sms_view_model = $this->add('xepan\communication\Model_Communication_SMSSetting');
		$sms_view=$this->add('xepan\hr\CRUD',null,'sms_setting',['view/setting/sms-setting-grid']);
		if($sms_view->isEditing()){
			$form=$sms_view->form;
			$form->setLayout('view/setting/form/sms-setting');
			$form->js(true)->find('button')->addClass('btn btn-primary');
		}	

		if($sms_view->isEditing()){
		if($emp_id= $this->app->employee->id)
			$sms_view_model->addCondition('created_by_id',$emp_id);
		}

		$sms_view->setModel($sms_view_model);

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
			$misc_m->app->employee
			    ->addActivity("'Time Zone' Updated as '".$form['time_zone']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$misc_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}

		// Email Setting
		$email_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'email_duplication_allowed'=>'DropDown'
							],
					'config_key'=>'Email_Duplication_Allowed_Settings',
					'application'=>'base'
			]);
		$email_m->add('xepan\hr\Controller_ACL');
		$email_m->tryLoadAny();		

		$form = $this->add('Form_Stacked',null,'email_view');
		$form->setModel($email_m);
		$allow_email_permission = array('duplication_allowed' =>'Duplication Allowed',
									 'no_duplication_allowed_for_same_contact_type' =>'No Duplication Allowed For Same Contact Type',
									 'never_duplication_allowed' =>'Never Duplication Allowed');
		$email_allowed_field =$form->getElement('email_duplication_allowed')->set($email_m['email_duplication_allowed']);
		$email_allowed_field->setValueList($allow_email_permission);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$email_m->app->employee
			    ->addActivity("'Email Duplication Setting' Updated as '".$form['email_duplication_allowed']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$email_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}

		// Contact No Setting
		$contactno_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'contact_no_duplcation_allowed'=>'DropDown'
							],
					'config_key'=>'contact_no_duplication_allowed_settings',
					'application'=>'base'
			]);
		$contactno_m->add('xepan\hr\Controller_ACL');
		$contactno_m->tryLoadAny();		

		$form = $this->add('Form_Stacked',null,'contactno_view');
		$form->setModel($contactno_m);
		$allow_contactno_permission = array('duplication_allowed' =>'Duplication Allowed',
									 'no_duplication_allowed_for_same_contact_type' =>'No Duplication Allowed For Same Contact Type',
									 'never_duplication_allowed' =>'Never Duplication Allowed');
		$email_allowed_field =$form->getElement('contact_no_duplcation_allowed')->set($contactno_m['contact_no_duplcation_allowed']);
		$email_allowed_field->setValueList($allow_contactno_permission);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$contactno_m->app->employee
			    ->addActivity("'Contact No Duplication Setting' Updated as '".$form['contact_no_duplcation_allowed']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$contactno_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
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
			$company_m->app->employee
			    ->addActivity("Company Information Updated", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$company_m);
			$c_form->js(null,$c_form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
		// $this->add('View',null,'company_info',['view/schema-micro-data','person_info'])->setModel($company_m);

		/*Communication Sub Type Form */
		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);

		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();

		$this->add('View',null,'comm_subtype')->set('Enter comma seperated values with no space');
		$sub_type_form = $this->add('Form_Stacked',null,'comm_subtype');
		$sub_type_form->setModel($config_m,['sub_type']);
		$sub_type_form->getElement('sub_type')->set($config_m['sub_type']);
		$sub_type_form->addSubmit('Save')->addClass('btn btn-primary');
		
		if($sub_type_form->isSubmitted()){
			$sub_type_form->save();
			$sub_type_form->js(null,$sub_type_form->js()->reload())->univ()->successMessage('Information Saved')->execute();
		}


	}
	
	function defaultTemplate(){
		return ['page/general-setting'];
	}
}