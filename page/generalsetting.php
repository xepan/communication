<?php
namespace xepan\communication;

class page_generalsetting extends \xepan\communication\page_sidebar{
	public $title="General Settings";
	function init(){
		parent::init();
		
		$tabs = $this->add('Tabs');
		
		/*General Email Setting*/
		$email_settings_tab = $tabs->addTab('Email Settings','email-settings');
		$email_setting= $email_settings_tab->add('xepan\communication\Model_Communication_EmailSetting');
		$settingview=$email_settings_tab->add('xepan\hr\CRUD',['action_page'=>'xepan_communication_general_email'],null,['view/setting/email-setting-grid']);
		$settingview->setModel($email_setting);
		$settingview->grid->addQuickSearch(['name','email_username']);
		

		// /*SMS Setting*/
		$sms_settings_tab = $tabs->addTab('SMS Settings','sms-settings');
		$sms_view_model = $sms_settings_tab->add('xepan\communication\Model_Communication_SMSSetting');
		$sms_view=$sms_settings_tab->add('xepan\hr\CRUD',null,null,['view/setting/sms-setting-grid']);
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
		$misc_tab = $tabs->addTab('MISC Settings','misc-settings');
		$misc_m = $misc_tab->add('xepan\base\Model_ConfigJsonModel',
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
		$form = $misc_tab->add('Form_Stacked');
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
		$duplicate_email_settings_tab = $tabs->addTab('Duplicate Emails','dupemail-settings');
		$email_m = $duplicate_email_settings_tab->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'email_duplication_allowed'=>'DropDown'
							],
					'config_key'=>'Email_Duplication_Allowed_Settings',
					'application'=>'base'
			]);
		$email_m->add('xepan\hr\Controller_ACL');
		$email_m->tryLoadAny();		

		$form = $duplicate_email_settings_tab->add('Form_Stacked');
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
		$dupicate_contact_tab = $tabs->addTab('Duplicate Contact','dupcont-settings');
		$contactno_m = $dupicate_contact_tab->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'contact_no_duplcation_allowed'=>'DropDown'
							],
					'config_key'=>'contact_no_duplication_allowed_settings',
					'application'=>'base'
			]);
		$contactno_m->add('xepan\hr\Controller_ACL');
		$contactno_m->tryLoadAny();		

		$form = $dupicate_contact_tab->add('Form_Stacked');
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
		$company_info_tab = $tabs->addTab('Company Info','company-info-settings');
		$company_m = $company_info_tab->add('xepan\base\Model_Config_CompanyInfo');
		
		$company_m->add('xepan\hr\Controller_ACL');
		$company_m->tryLoadAny();

		$company_m->getElement('mobile_no')->caption('contact number')->hint('comma(,) seperated multiple values');

		$c_form = $company_info_tab->add('Form_Stacked');
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

		$communication_sub_type_tab = $tabs->addTab('Communication Sub Types','commsubtypes-settings');
		/*Communication Sub Type Form */
		$config_m = $communication_sub_type_tab->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						'calling_status'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);

		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();

		$communication_sub_type_tab->add('View')->set('Enter comma seperated values with no space');
		$sub_type_form = $communication_sub_type_tab->add('Form_Stacked');
		$sub_type_form->setModel($config_m,['sub_type','calling_status']);
		$sub_type_form->getElement('sub_type')->set($config_m['sub_type']);
		$sub_type_form->getElement('calling_status')->set($config_m['calling_status']);
		$sub_type_form->addSubmit('Save')->addClass('btn btn-primary');
		
		if($sub_type_form->isSubmitted()){
			$sub_type_form->save();
			$sub_type_form->js(null,$sub_type_form->js()->reload())->univ()->successMessage('Information Saved')->execute();
		}

		// Support Ticket Reply exisiting contact Communication Setting
		$verify_tab = $tabs->addTab('Verify?');
		$config_m = $verify_tab->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'varify_to_field_as_contact'=>'DropDown'
							],
					'config_key'=>'Varify_To_Field_As_Exisiting_Conact',
					'application'=>'communication'
			]);
		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();		

		$form = $verify_tab->add('Form_Stacked');
		$form->setModel($config_m);
		$check_existing_contact = array('YES' =>'YES',
									 'No' =>'No',);
		$check_existing_contact_field =$form->getElement('varify_to_field_as_contact')->set($config_m['varify_to_field_as_contact']);
		$check_existing_contact_field->setValueList($check_existing_contact);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$config_m->app->employee
			    ->addActivity("'Varify to Field As Contact' Updated as '".$form['varify_to_field_as_contact']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}


		/**
		Contact Other Info to be asked
		**/

		$contact_other_info_config = $tabs->addTab('Contacts Other Info Fields','contact-info-fields');
		$contact_other_info_config_m = $contact_other_info_config->add('xepan\base\Model_ConfigJsonModel',
						[
							'fields'=>[
										'contact_other_info_fields'=>"Text",
										],
							'config_key'=>'Contact_Other_Info_Fields',
							'application'=>'base'
						]);
		$contact_other_info_config_m->add('xepan\hr\Controller_ACL');
		$contact_other_info_config_m->tryLoadAny();

		$contact_other_info_form = $contact_other_info_config->add('Form');
		$field = $contact_other_info_form->addField('Line','contact_other_info_fields')
					->setFieldHint('Comma separated fields');
				

		if($contact_other_info_config_m['contact_other_info_fields'])
			$field->set($contact_other_info_config_m['contact_other_info_fields']);

		$contact_other_info_form->addSubmit("Save")->addClass('btn btn-primary');
		if($contact_other_info_form->isSubmitted()){
			$contact_other_info_config_m['contact_other_info_fields'] = $contact_other_info_form['contact_other_info_fields'];
			$contact_other_info_config_m->save();
			$contact_other_info_form->js(null,$contact_other_info_form->js()->univ()->successMessage('Information successfully updated'))->reload()->execute();
		}


	}
	
	// function defaultTemplate(){
	// 	return ['page/general-setting'];
	// }
}