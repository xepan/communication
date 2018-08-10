<?php
namespace xepan\communication;

class page_generalsetting extends \xepan\communication\page_sidebar{
	public $title="General Settings";
	
	function page_index(){
		// parent::init();
		
		$tabs = $this->add('Tabs');
		
		/*General Email Setting*/
		$email_settings_tab = $tabs->addTabURL('./emailsetting','Email Settings');
		// /*SMS Setting*/
		$sms_settings_tab = $tabs->addTabURL('./smssettings','SMS Settings');
		// MISC Setting
		$misc_tab = $tabs->addTabURL('./timezone','TimeZone Settings');
		// Email Setting
		$duplicate_email_settings_tab = $tabs->addTabURL('./duplicateemailsetting','Duplicate Emails');
		// Contact No Setting
		$dupicate_contact_tab = $tabs->addTabURL('./duplicatecontactsetting','Duplicate Contact');	
		/*Company Info*/
		$company_info_tab = $tabs->addTabURL('./companyinfo','Company Info');

		$communication_sub_type_tab = $tabs->addTabURL('./commsubtype','Communication Sub Types');
		// Support Ticket Reply exisiting contact Communication Setting
		$verify_tab = $tabs->addTabURL('./supportverify','Verify?');
		/**
		Contact Other Info to be asked
		**/
		$contact_other_info_config = $tabs->addTabURL('./contactsetting','Contacts Other Info');

		// Document Other Info to be asked

		$document_other_info_config = $tabs->addTabURL('./documentsetting','Documents Other Info');
	}
	
	function page_emailsetting(){
		$email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$settingview = $this->add('xepan\hr\CRUD',['action_page'=>'xepan_communication_general_email'],null,['view/setting/email-setting-grid']);
		$settingview->setModel($email_setting);
		$settingview->grid->addQuickSearch(['name','email_username']);
		
		$b = $settingview->addButton("Set Default Email")->addClass("btn btn-primary");
		$b->js('click')->univ()->frameURL('Set System Default Email',$this->app->url('../setdefaultemail'));
	}


	function page_setdefaultemail(){
		$model = $this->add('xepan\communication\Model_Config_DefaultEmailSmsAndOther');
		$model->tryLoadAny();

		$form = $this->add('Form');
		$form->setModel($model,['default_email']);
		$form->addSubmit('save');
		if($form->isSubmitted()){
			$model['default_email'] = $form['default_email'];
			$model->save();
			$form->js(null,[$form->js()->reload(),$form->js()->closest('.dialog')->dialog('close')])->univ()->successMessage('System Default Email Setting Updated by '.$this->app->employee['name']." ( ".$this->app->employee['post']." )")->execute();
		}

	}

	function page_setdefaultsms(){
		$model = $this->add('xepan\communication\Model_Config_DefaultEmailSmsAndOther');
		$model->tryLoadAny();

		$form = $this->add('Form');
		$form->setModel($model,['default_sms']);
		$form->addSubmit('save');
		if($form->isSubmitted()){
			$model['default_sms'] = $form['default_sms'];
			$model->save();
			$form->js(null,[$form->js()->reload(),$form->js()->closest('.dialog')->dialog('close')])->univ()->successMessage('System Default SMS Setting Updated by '.$this->app->employee['name']." ( ".$this->app->employee['post']." )")->execute();
		}

	}


	function page_smssettings(){
		
		$sms_view_model = $this->add('xepan\communication\Model_Communication_SMSSetting');
		$sms_view = $this->add('xepan\hr\CRUD',null,null,['view/setting/sms-setting-grid']);
		if($sms_view->isEditing()){
			$form = $sms_view->form;
			$form->setLayout('view/setting/form/sms-setting');
			$form->js(true)->find('button')->addClass('btn btn-primary');
		}
		$sms_view->setModel($sms_view_model,['name','gateway_url','sms_username','sms_password','sms_user_name_qs_param','sms_password_qs_param','sms_number_qs_param','sm_message_qs_param','sms_prefix','sms_postfix']);
		
		$b = $sms_view->addButton("Set Default SMS")->addClass("btn btn-primary");
		$b->js('click')->univ()->frameURL('Set System Default SMS',$this->app->url('../setdefaultsms'));
	}

	function page_timezone(){
		$misc_m = $this->add('xepan\base\Model_Config_Misc');
		$misc_m->add('xepan\hr\Controller_ACL');
		$misc_m->tryLoadAny();		

		$form = $this->add('Form_Stacked');
		$form->setModel($misc_m,['time_zone']);

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
	}

	function page_iprestrictions(){
		$misc_m = $this->add('xepan\base\Model_Config_Misc');
		$misc_m->add('xepan\hr\Controller_ACL');
		$misc_m->tryLoadAny();		

		$form = $this->add('Form_Stacked');
		$form->setModel($misc_m,['admin_restricted_ip']);

		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$misc_m->app->employee
			    ->addActivity("'IP Restriction Updated'".$form['admin_restricted_ip']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$misc_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
	}

	function page_duplicateemailsetting(){
		$email_m = $this->add('xepan\base\Model_Config_DuplicateEmailAllowed');
		$email_m->add('xepan\hr\Controller_ACL');
		$email_m->tryLoadAny();

		$form = $this->add('Form_Stacked');
		$form->setModel($email_m);
		// $allow_email_permission = array('duplication_allowed' =>'Duplication Allowed',
		// 							 'no_duplication_allowed_for_same_contact_type' =>'No Duplication Allowed For Same Contact Type',
		// 							 'never_duplication_allowed' =>'Never Duplication Allowed');
		$email_allowed_field =$form->getElement('email_duplication_allowed')->set($email_m['email_duplication_allowed']);
		// $email_allowed_field->setValueList($allow_email_permission);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$email_m->app->employee
			    ->addActivity("'Email Duplication Setting' Updated as '".$form['email_duplication_allowed']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$email_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
	}

	function page_duplicatecontactsetting(){
		$contactno_m = $this->add('xepan\base\Model_Config_DuplicateContactNoAllowed');
		$contactno_m->add('xepan\hr\Controller_ACL');
		$contactno_m->tryLoadAny();		
		$form = $this->add('Form_Stacked');
		$form->setModel($contactno_m);
		$email_allowed_field =$form->getElement('contact_no_duplcation_allowed')->set($contactno_m['contact_no_duplcation_allowed']);
		// $email_allowed_field->setValueList($allow_contactno_permission);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$contactno_m->app->employee
			    ->addActivity("'Contact No Duplication Setting' Updated as '".$form['contact_no_duplcation_allowed']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$contactno_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
	}

	function page_companyinfo(){
		$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
		
		$company_m->add('xepan\hr\Controller_ACL');
		$company_m->tryLoadAny();

		$company_m->getElement('mobile_no')->caption('contact number')->hint('comma(,) seperated multiple values');

		$c_form = $this->add('Form_Stacked');
		$c_form->setModel($company_m);
		$c_form->addSubmit('Save')->addClass('btn btn-primary');
		
		if($c_form->isSubmitted()){
			$c_form->save();
			$company_m->app->employee
			    ->addActivity("Company Information Updated", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$company_m);
			$c_form->js(null,$c_form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
	}

	function page_commsubtype(){
		/*Communication Sub Type Form */
		$config_m = $this->add('xepan\communication\Model_Config_SubType');
		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();

		$sub_type_form = $this->add('Form');
		$sub_type_form->add('xepan\base\Controller_FLC')
			->showLables(true)
			->makePanelsCoppalsible(true)
			->addContentSpot()
			->layout([
					'sub_type_1_label_name~Sub Type 1 Label Name'=>'Sub Types and its Label~c1~4',
					'sub_type~Sub Type 1'=>'c2~8~Enter comma seperated values with no space',
					'sub_type_2_label_name~Sub Type 2 Label Name'=>'c3~4',
					'calling_status~Sub Type 2'=>'c4~8~Enter comma seperated values with no space',
					'sub_type_3_label_name~Sub Type 3 Label Name'=>'c5~4',
					'sub_type_3~Sub Type 3'=>'c6~8~Enter comma seperated values with no space',
					'FormButtons~&nbsp;'=>'c10~4'
				]);

		$sub_type_form->setModel($config_m,['sub_type_1_label_name','sub_type','sub_type_2_label_name','calling_status','sub_type_3_label_name','sub_type_3']);
		$sub_type_form->getElement('sub_type')->set($config_m['sub_type']);
		$sub_type_form->getElement('calling_status')->set($config_m['calling_status']);
		$sub_type_form->getElement('sub_type_3')->set($config_m['sub_type_3']);
		$sub_type_form->getElement('sub_type_1_label_name')->set($config_m['sub_type_1_label_name']?:"Product/ Service/ Related To");
		$sub_type_form->getElement('sub_type_2_label_name')->set($config_m['sub_type_2_label_name']?:"Communication Result");
		$sub_type_form->getElement('sub_type_3_label_name')->set($config_m['sub_type_3_label_name']?:"Communication Remark");

		$sub_type_form->addSubmit('Save')->addClass('btn btn-primary');
		
		if($sub_type_form->isSubmitted()){
			$sub_type_form->save();
			$sub_type_form->js(null,$sub_type_form->js()->reload())->univ()->successMessage('Information Saved')->execute();
		}

	}

	function page_supportverify(){
		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'varify_to_field_as_contact'=>'DropDown'
							],
					'config_key'=>'Varify_To_Field_As_Exisiting_Conact',
					'application'=>'communication'
			]);
		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();		

		$form = $this->add('Form_Stacked');
		$form->setModel($config_m);
		$check_existing_contact = array('YES' =>'YES',
									 'No' =>'No',);
		$check_existing_contact_field = $form->getElement('varify_to_field_as_contact')->set($config_m['varify_to_field_as_contact']);
		$check_existing_contact_field->setValueList($check_existing_contact);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$config_m->app->employee
			    ->addActivity("'Varify to Field As Contact' Updated as '".$form['varify_to_field_as_contact']."'", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}
	}


	function page_contactsetting(){

		$tab = $this->add('Tabs');
		$field_tab = $tab->addTab('Other Info Fields');
		$tag_tab = $tab->addTab('Contact Tags');

		// field tabs
		// contact tags
		$other_fields_model = $field_tab->add('xepan\base\Model_Config_ContactOtherInfo',['sort_by'=>'for']);
		$other_fields_model->add('xepan\hr\Controller_ACL');
		
		$crud = $field_tab->add('xepan\hr\CRUD');
		$crud->setModel($other_fields_model,null);
		$crud->grid->removeColumn('id');

		// $contact_other_info_config_m = $field_tab->add('xepan\base\Model_Config_ContactOtherInfo');
		// $contact_other_info_config_m->add('xepan\hr\Controller_ACL');
		// $contact_other_info_config_m->tryLoadAny();
		// $contact_other_info_form = $field_tab->add('Form');
		// $field = $contact_other_info_form->addField('Line','contact_other_info_fields')
		// 			->setFieldHint('Comma separated fields');

		// if($contact_other_info_config_m['contact_other_info_fields'])
		// 	$field->set($contact_other_info_config_m['contact_other_info_fields']);

		// $contact_other_info_form->addSubmit("Save")->addClass('btn btn-primary');
		// if($contact_other_info_form->isSubmitted()){
		// 	$contact_other_info_config_m['contact_other_info_fields'] = $contact_other_info_form['contact_other_info_fields'];
		// 	$contact_other_info_config_m->save();
		// 	$contact_other_info_form->js(null,$contact_other_info_form->js()->univ()->successMessage('Information successfully updated'))->reload()->execute();
		// }

		// contact tags
		$tag_model = $tag_tab->add('xepan\base\Model_Contact_Tag');
		$tag_model->add('xepan\hr\Controller_ACL');
		
		$crud = $tag_tab->add('xepan\hr\CRUD');
		$crud->setModel($tag_model,null,['name']);
		$crud->grid->removeColumn('id');
	}

	function page_documentsetting(){
		$other_fields_model = $this->add('xepan\base\Model_Config_DocumentOtherInfo',['sort_by'=>'for']);
		$other_fields_model->add('xepan\hr\Controller_ACL');

		$crud = $this->add('xepan\hr\CRUD');
		$crud->setModel($other_fields_model);
		$crud->grid->removeColumn('id');
		$crud->grid->addFormatter('conditional_binding','Wrap');
	}

	// function defaultTemplate(){
	// 	return ['page/general-setting'];
	// }
}