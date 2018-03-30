<?php

namespace xepan\communication;

class View_EasySetupWizard extends \View{
	function init(){
		parent::init();

		/**
		............. Frontend (Website)User Mail Config ...............
		*/
		if($_GET[$this->name.'_config_user_settings']){

			$frontend_config_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'user_registration_type'=>'DropDown',
							'reset_subject'=>'xepan\base\RichText',
							'reset_body'=>'xepan\base\RichText',
							'update_subject'=>'Line',
							'update_body'=>'xepan\base\RichText',
							'registration_subject'=>'Line',
							'registration_body'=>'xepan\base\RichText',
							'verification_subject'=>'Line',
							'verification_body'=>'xepan\base\RichText',
							'subscription_subject'=>'Line',
							'subscription_body'=>'xepan\base\RichText',
							],
					'config_key'=>'FRONTEND_LOGIN_RELATED_EMAIL',
					'application'=>'communication'
			]);
			$frontend_config_m->tryLoadAny();

			$reg_type=$frontend_config_m['user_registration_type'];
			$reset_subject = $frontend_config_m['reset_subject'];
			$reset_body = $frontend_config_m['reset_body'];
			$reg_subject = $frontend_config_m['registration_subject'];
			$reg_body = $frontend_config_m['registration_body'];
			$verify_subject = $frontend_config_m['verification_subject'];
			$verify_body = $frontend_config_m['verification_body'];
			$update_subject = $frontend_config_m['update_subject'];
			$update_body = $frontend_config_m['update_body'];
			$subscription_subject = $frontend_config_m['subscription_subject'];
			$subscription_body = $frontend_config_m['subscription_body'];

			$file_reg_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_subject.html'));
			$file_reg_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_body.html'));
		
			$file_reset_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_subject.html'));
			$file_reset_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_body.html'));
		
			$file_verification_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_subject.html'));
			$file_verification_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_body.html'));
			
			$file_update_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_subject.html'));
			$file_update_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_body.html'));
			
			$file_subscription_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/subscription_subject.html'));
			$file_subscription_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/subscription_body.html'));
			
			if(!$reg_type){
				$frontend_config_m['user_registration_type'] = "self_activated";
			}

			if(!$reg_subject){
				$frontend_config_m['registration_subject']= $file_reg_subject;
			}
			
			if(!$reg_body){
				$frontend_config_m['registration_body'] = $file_reg_body;
			}

			if(!$reset_subject){
				$frontend_config_m['reset_subject'] = $file_reset_subject;
			}

			if(!$reset_body){
				$frontend_config_m['reset_body'] = $file_reset_body;
			}

			if(!$verify_subject){
				$frontend_config_m['verification_subject'] = $file_verification_subject;
			}
			
			if(!$verify_body){
				$frontend_config_m['verification_body'] = $file_verification_body;
			}

			if(!$update_subject){
				$frontend_config_m['update_subject'] = $file_update_subject;
			}
			
			if(!$update_body){
				$frontend_config_m['update_body'] = $file_update_body;
			}
			
			if(!$subscription_subject){
				$frontend_config_m['subscription_subject'] = $file_subscription_subject;
			}

			if(!$subscription_body){
				$frontend_config_m['subscription_body'] = $file_subscription_body;
			}			

			$frontend_config_m->save();	
			$this->js(true)->univ()->frameURL("User Configuration For Activation/Deactivation",$this->app->url('xepan_communication_general_emailcontent_usertool'));

		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_config_user_settings'=>1]);

			$frontend_config_info = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'user_registration_type'=>'DropDown',
							'reset_subject'=>'xepan\base\RichText',
							'reset_body'=>'xepan\base\RichText',
							'update_subject'=>'Line',
							'update_body'=>'xepan\base\RichText',
							'registration_subject'=>'Line',
							'registration_body'=>'xepan\base\RichText',
							'verification_subject'=>'Line',
							'verification_body'=>'xepan\base\RichText',
							'subscription_subject'=>'Line',
							'subscription_body'=>'xepan\base\RichText',
							],
					'config_key'=>'FRONTEND_LOGIN_RELATED_EMAIL',
					'application'=>'communication'
			]);
			$frontend_config_info->tryLoadAny();
			
			$all = $frontend_config_info;
			$r_type = $all['user_registration_type'];
			$reg_sub = $all['registration_subject'];
			$reg_body = $all['registration_body'];
			$reset_pwd_sub = $all['reset_subject'];
			$reset_pwd_body = $all['reset_body'];
			$verify_subject = $all['verification_subject'];
			$verify_body = $all['verification_body'];
			$update_subject = $all['update_subject'];
			$update_body = $all['update_body'];
			$subscription_subject = $all['subscription_subject'];
			$subscription_body = $all['subscription_body'];


			if(!$r_type || !$reg_sub || !$reg_body || !$reset_pwd_sub || !$reset_pwd_body || !$verify_subject || !$verify_body || !$update_body || !$update_subject || !$subscription_subject || !$subscription_body){
				$isDone = false;
			}else{	
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already config the user settings, visit page ? <a href="'. $this->app->url('xepan_communication_general_emailcontent_usertool')->getURL().'"> click here to go </a>');
			}

			$user_config_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - Communication')
				->setTitle('Configure Settings For New Users On Your Website')
				->setMessage('Configuration setting for web user activation & deactivation mailing content.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		/**
		............. Backend (ERP)User Mail Config ...............
		*/
		if($_GET[$this->name.'_config_erp_user_settings']){

			$erp_config_m = $this->add('xepan\base\Model_ConfigJsonModel',
				[
					'fields'=>[
								'reset_subject'=>'Line',
								'reset_body'=>'xepan\base\RichText',
								'update_subject'=>'Line',
								'update_body'=>'xepan\base\RichText',
								],
						'config_key'=>'ADMIN_LOGIN_RELATED_EMAIL',
						'application'=>'communication'
				]);
			$erp_config_m->tryLoadAny();

			$reset_subject_file = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/erp-users-mailing-content/reset_subject.html'));
			$reset_body_file = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/erp-users-mailing-content/reset_body.html'));
			$update_subject_file = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/erp-users-mailing-content/update_subject.html'));
			$update_body_file = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/erp-users-mailing-content/update_body.html'));
			
			if(!$erp_config_m['reset_subject']){
				$erp_config_m['reset_subject'] = $reset_subject_file;
			}

			if(!$erp_config_m['reset_body']){
				$erp_config_m['reset_body'] = $reset_body_file;
			}

			if(!$erp_config_m['update_subject']){
				$erp_config_m['update_subject'] = $update_subject_file;
			}

			if(!$erp_config_m['update_body']){
				$erp_config_m['update_body'] = $update_body_file;
			}

			$erp_config_m->save();	
			$this->js(true)->univ()->frameURL("User Configuration For Activation/Deactivation",$this->app->url('xepan_communication_general_emailcontent_admin'));
		}

			$isDone = false;

			$action = $this->js()->reload([$this->name.'_config_erp_user_settings'=>1]);
			
			$erp_config_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
				[
					'fields'=>[
								'reset_subject'=>'Line',
								'reset_body'=>'xepan\base\RichText',
								'update_subject'=>'Line',
								'update_body'=>'xepan\base\RichText',
								],
						'config_key'=>'ADMIN_LOGIN_RELATED_EMAIL',
						'application'=>'communication'
				]);
			$erp_config_mdl->tryLoadAny();

			if(!$erp_config_mdl['reset_subject'] || !$erp_config_mdl['reset_body'] || !$erp_config_mdl['update_subject'] || !$erp_config_mdl['update_body']){
				$isDone = false;
			}else{
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already config the user settings, visit page ? <a href="'. $this->app->url('xepan_communication_general_emailcontent_admin')->getURL().'"> click here to go </a>');
			}

			$erp_user_config_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - Communication')
				->setTitle('Configure Settings For ERP Users')
				->setMessage('Configuration setting for erp user reset & update password mailing content.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		/**
		............. E-mail Settings ...............
		*/
		if($_GET[$this->name.'_set_emailsetting']){
			$this->js(true)->univ()->frameURL("Mail Config",$this->app->url('xepan_communication_general_email&action=add'));
			if($this->add('xepan\communication\Model_Communication_EmailSetting')->count()->getOne() > 0)
				return 1;
		}

		$isDone = false;
		
			$action = $this->js()->reload([$this->name.'_set_emailsetting'=>1]);

			if($this->add('xepan\communication\Model_Communication_EmailSetting')->count()->getOne() > 0){
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already have emailsetting, visit page ? <a href="'. $this->app->url('xepan_communication_general_email')->getURL().'"> click here to go </a>');
			}

			$email_view = $this->add('xepan\base\View_Wizard_Step');

			$email_view->setAddOn('Application - Communication')
				->setTitle('Configure Email Setting To Communicate Via Email')
				->setMessage('Please configure Email Settings, for Communication with your clients via email.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		/**
		............. Support Mail Setting ...............
		*/

		if($_GET[$this->name.'_check_supportemail_options']){
			$this->js(true)->univ()->frameURL("Mail Config",$this->app->url('xepan_communication_general_email&action=add'));
		}

		$isDone = false;
		
			$action = $this->js()->reload([$this->name.'_check_supportemail_options'=>1]);

			$support_mail = $this->add('xepan\communication\Model_Communication_EmailSetting');
			$support_mail->addCondition('is_support_email',true);
			$support_mail->tryLoadAny();

			if($support_mail->loaded()){
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You already have emailsetting, visit page ? <a href="'. $this->app->url('xepan_communication_general_email')->getURL().'"> click here to go </a>');
			}

			$support_view = $this->add('xepan\base\View_Wizard_Step')
				->setAddOn('Application - Communication')
				->setTitle('Support Email System')
				->setMessage('For Support Services System You have to select option "is support system" in Email Settings.')
				->setHelpMessage('Need help ! click on the help icon')
				->setHelpURL('#')
				->setAction('Click Here',$action,$isDone);

		/**
		............. Company Info ...............
		*/

		if($_GET[$this->name.'_company_info']){
			$this->js(true)->univ()->frameURL("Company Information",$this->app->url('xepan_communication_generalsetting'));
		}

		$isDone = false;
		
		$action = $this->js()->reload([$this->name.'_company_info'=>1]);

		$company_m = $this->add('xepan\base\Model_Config_CompanyInfo');
		$company_m->tryLoadAny();

		if(!$company_m['company_name'] || !$company_m['company_owner'] || !$company_m['mobile_no'] || !$company_m['company_email'] || !$company_m['company_address'] || !$company_m['company_pin_code'] || !$company_m['company_logo_absolute_url']){
			$isDone = false;
		}else{
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You already filled info of company/firm, visit page ? <a href="'. $this->app->url('xepan_communication_generalsetting')->getURL().'"> click here to go </a>');
		}

		$company_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Communication')
			->setTitle('Company/Firm Information')
			->setMessage('Please fill Information about your company/firm. For fill click here and Go to tab of Company Info')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		/**
		............. Communication Sub Types Coonfiguration ...............
		*/

		if($_GET[$this->name.'_communication_sub_type']){
			$this->js(true)->univ()->frameURL("Communication Sub Type",$this->app->url('xepan_communication_generalsetting'));
		}

		$isDone = false;
		
		$action = $this->js()->reload([$this->name.'_communication_sub_type'=>1]);

		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'sub_type'=>'text',
						],
				'config_key'=>'COMMUNICATION_SUB_TYPE',
				'application'=>'Communication'
		]);

		$config_m->tryLoadAny();

		if(!$config_m['sub_type']){
			$isDone = false;
		}else{
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You already define sub type of communication, visit page ? <a href="'. $this->app->url('xepan_communication_generalsetting')->getURL().'"> click here to go </a>');
		}

		$company_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Communication')
			->setTitle('Communication Sub Type Configuration')
			->setMessage('Please define sub type of communication. For define sub type click here and Go to tab of Communication Sub Type')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);
	}
}