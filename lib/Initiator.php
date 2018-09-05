<?php

namespace xepan\communication;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_communication';

	function setup_admin(){
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('../vendor/xepan/communication/');

		$this->app->jui->addStaticInclude('sip-0.10.0.min');

		if($this->app->inConfigurationMode)
			$this->populateConfigurationMenus();
		else
			$this->populateApplicationMenus();

		
		// $this->app->report_menu->addItem(['Employee Communication','icon'=>'fa fa-users'],'xepan_communication_report_employeecommunication');

		$search_communication = $this->add('xepan\communication\Model_Communication');
		$this->app->addHook('quick_searched',[$search_communication,'quickSearch']);
		$this->app->addHook('activity_report',[$search_communication,'activityReport']);
		// $this->app->addHook('contact_info',[$search_communication,'set_old_communication_info']);
		$this->app->addHook('widget_collection',[$this,'exportWidgets']);
        $this->app->addHook('entity_collection',[$this,'exportEntities']);
        $this->app->addHook('collect_shortcuts',[$this,'collect_shortcuts']);
		return $this;
	}

	function populateConfigurationMenus(){
		$m = $this->app->top_menu->addMenu('System');
    	$m->addItem(['Company Information','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_companyinfo'));
    	$m->addItem(['Branch','icon'=>'fa fa-cog'],$this->app->url('xepan_base_branch'));
    	$m->addItem(['Email Settings','icon'=>'fa fa-envelope'],$this->app->url('xepan_communication_generalsetting_emailsetting'));
    	$m->addItem(['SMS Settings','icon'=>'fa fa-mobile'],$this->app->url('xepan_communication_generalsetting_smssettings'));
    	$m->addItem(['TimeZone Setting','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_timezone'));
    	$m->addItem(['IP Restrictions (For Admin Access)','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_iprestrictions'));
    	$m->addItem(['Duplicate Email Settings','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_duplicateemailsetting'));
    	$m->addItem(['Duplicate Numbers Settings','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_duplicatecontactsetting'));
    	$m->addItem(['Communication Sub Types','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_commsubtype'));
    	$m->addItem(['Contacts Other Info and Tags','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_contactsetting'));
    	$m->addItem(['Documents Other Info and Tags','icon'=>'fa fa-cog'],$this->app->url('xepan_communication_generalsetting_documentsetting'));
    	$m->addItem(['Country/State Management','icon'=>' fa fa-users'],$this->app->url('xepan_communication_general_countrystate'));
    	$m->addItem(['Admin User Setting','icon'=>' fa fa-users'],$this->app->url('xepan_communication_general_emailcontent_admin'));
        $m->addItem(['Frontend User Setting','icon'=>' fa fa-users'],$this->app->url('xepan_communication_general_emailcontent_usertool'));
        $m->addItem(['Backup & Update','icon'=>' fa fa-users'],$this->app->url('xepan_base_update'));
        $m->addItem(['Document Action Notification','icon'=>' fa fa-cog'],$this->app->url('xepan_base_documentactionnotification'));
        $m->addItem(['Top Menu Designer','icon'=>' fa fa-cog'],$this->app->url('xepan_base_menudesigner'));
	}

	function getTopApplicationMenu(){
		return ['Reports'=>[
							[
								'name'=>'Employee Communication',
								'icon'=>'fa fa-users',
								'url'=>'xepan_communication_report_employeecommunication'
							]
		                ]
		        ];
	}

	function getConfigTopApplicationMenu(){
		if($this->app->getConfig('hidden_xepan_communication',false)){return [];}

		return [
				'System'=>[
					[	'name'=>'Company Information',
						'icon'=>'fa fa-cog',
						'url'=>'xepan_communication_generalsetting_companyinfo'
					],
		    		[	'name'=>'Email Settings',
		    			'icon'=>'fa fa-envelope',
		    			'url'=>'xepan_communication_generalsetting_emailsetting'
		    		],
		    		[	'name'=>'SMS Settings',
		    			'icon'=>'fa fa-mobile',
		    			'url'=>'xepan_communication_generalsetting_smssettings'
		    		],
		    		[	'name'=>'TimeZone Setting',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_timezone'
		    		],
		    		[	'name'=>'IP Restrictions (For Admin Access)',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_iprestrictions'
		    		],
		    		[	'name'=>'Duplicate Email Settings',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_duplicateemailsetting'
		    		],
		    		[	'name'=>'Duplicate Numbers Settings',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_duplicatecontactsetting'
		    		],
		    		[	'name'=>'Communication Sub Types',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_commsubtype'
		    		],
		    		[	'name'=>'Contacts Other Info and Tags',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_contactsetting'
		    		],
		    		[	'name'=>'Documents Other Info and Tags',
		    			'icon'=>'fa fa-cog',
		    			'url'=>'xepan_communication_generalsetting_documentsetting'
		    		],
		    		[	'name'=>'Country/State Management',
		    			'icon'=>' fa fa-users',
		    			'url'=>'xepan_communication_general_countrystate'
		    		],
		    		[	'name'=>'Admin User Setting',
		    			'icon'=>' fa fa-users',
		    			'url'=>'xepan_communication_general_emailcontent_admin'
		    		],
		        	[	'name'=>'Frontend User Setting',
		        		'icon'=>' fa fa-users',
		        		'url'=>'xepan_communication_general_emailcontent_usertool'
		        	],
		        	[	'name'=>'Backup & Update',
		        		'icon'=>'fa fa-users',
		        		'url'=>'xepan_base_update'
		        	],
		        	[	'name'=>'Document Action Notification',
		        		'icon'=>'fa fa-cog',
		        		'url'=>'xepan_base_documentactionnotification'
		        	],
		        	[	'name'=>'Document Action Notification',
		        		'icon'=>'fa fa-cog',
		        		'url'=>'xepan_base_documentactionnotification'
		        	]
				]
			];
	}

	function populateApplicationMenus(){
		if(!$this->app->isAjaxOutput() && !$this->app->getConfig('hidden_xepan_communication',false)){
			$this->app->side_menu->addItem(['Emails','icon'=>' fa fa-envelope','badge'=>["0/0",'swatch'=>' label label-primary pull-right']],'xepan_communication_emails')->setAttr(['title'=>'Emails'])->addClass('contact-and-all-email-count');
			$this->app->side_menu->addItem(['Message','icon'=>' fa fa-envelope','badge'=>["0/0",'swatch'=>' label label-primary pull-right']],'xepan_communication_internalmsg')->setAttr(['title'=>'Internal Communication'])->addClass('contact-and-all-message-count');
			// $this->app->side_menu->addItem(['General Setting','icon'=>'fa fa-cog'],'xepan_communication_generalsetting')->setAttr(['title'=>'General Setting']);
		}
	}

	function exportWidgets($app,&$array){
        $array[] = ['xepan\communication\Widget_UnreadMails','level'=>'Individual','title'=>'Unread Mails'];
    }

    function exportEntities($app,&$array){
    	 $array['Communication'] = ['caption'=>'Communication','type'=>'DropDown','model'=>'xepan\communication\Model_Communication'];
    	 $array['Communication_EmailSetting'] = ['caption'=>'Communication_EmailSetting','type'=>'DropDown','model'=>'xepan\communication\Model_Communication_EmailSetting'];
    	 $array['Communication_SMSSetting'] = ['caption'=>'Communication_SMSSetting','type'=>'DropDown','model'=>'xepan\communication\Model_Communication_SMSSetting'];
    	 $array['COMPANY_AND_OWNER_INFORMATION'] = ['caption'=>'COMPANY_AND_OWNER_INFORMATION','type'=>'DropDown','model'=>'xepan\base\Model_Config_CompanyInfo'];
    	 $array['ADMIN_LOGIN_RELATED_EMAIL'] = ['caption'=>'ADMIN_LOGIN_RELATED_EMAIL','type'=>'DropDown','model'=>'xepan\communication\Model_ADMIN_LOGIN_RELATED_EMAIL'];
    	 $array['FRONTEND_LOGIN_RELATED_EMAIL'] = ['caption'=>'FRONTEND_LOGIN_RELATED_EMAIL','type'=>'DropDown','model'=>'xepan\communication\Model_FRONTEND_LOGIN_RELATED_EMAIL'];
    	 $array['COMMUNICATION_SUB_TYPE'] = ['caption'=>'COMMUNICATION_SUB_TYPE','type'=>'DropDown','model'=>'xepan\communication\Model_Config_Subtype'];
    	 $array['Miscellaneous_Technical_Settings'] = ['caption'=>'Miscellaneous_Technical_Settings','type'=>'DropDown','model'=>'xepan\communication\Miscellaneous_Technical_Settings'];
    }

    function collect_shortcuts($app,&$shortcuts){
		// $shortcuts[]=["title"=>"New Email","keywords"=>"new email send","description"=>"Send New Email","normal_access"=>"My Menu -> Tasks / New Task Button","url"=>$this->app->url('xepan/projects/mytasks',['admin_layout_cube_mytasks_virtualpage'=>'true']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Emails","keywords"=>"check email","description"=>"Your Emails","normal_access"=>"SideBar -> Emails","url"=>$this->app->url('xepan/communication/emails'),/*'mode'=>'fullframe'*/];
		$shortcuts[]=["title"=>"Internal Messages","keywords"=>"check message internal in house premises staff inbox","description"=>"Your Internal Messages Page","normal_access"=>"SideBar -> Message","url"=>$this->app->url('xepan/communication/internalmsg'),'mode'=>'fullframe'];
		$shortcuts[]=["title"=>"General Settings","keywords"=>"general settings","description"=>"Manage your general settings","normal_access"=>"SideBar -> General Settings","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1]),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Manage Email Accounts","keywords"=>"add new email account setting imap pop2 smtp transport","description"=>"Manage your email accounts","normal_access"=>"SideBar -> General Settings/Email Settings","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_email-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Manage SMS Accounts","keywords"=>"add new sms account setting gateway","description"=>"Manage your sms gateway accounts","normal_access"=>"SideBar -> General Settings/SMS Settings","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_sms-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Edit Your Time Zone","keywords"=>"timezone date time GMT Misc settings","description"=>"Manage your timezone settings","normal_access"=>"SideBar -> General Settings/MISC Settings","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_misc-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Duplicate Email Settings","keywords"=>"duplicate existing email configuration","description"=>"Configure how system should work for duplicate email entered","normal_access"=>"SideBar -> General Settings/Duplicate Emails","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_dupemail-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Duplicate Contact Number Settings","keywords"=>"duplicate existing number phone mobile contact configuration","description"=>"Configure how system should work for duplicate contact number entered","normal_access"=>"SideBar -> General Settings/Duplicate Contact","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_dupcont-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Basic Company Information","keywords"=>"configuring company name address organization system details","description"=>"Configure your companies basic details","normal_access"=>"SideBar -> General Settings/Company Info","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_company-info-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Communication Sub Types","keywords"=>"configuring communication sub types","description"=>"Configure your companies basic details","normal_access"=>"SideBar -> General Settings/Communication Sub Types","url"=>$this->app->url('xepan/communication/generalsetting',['cut_page'=>1,'cut_object'=>'admin_layout_cube_generalsetting_tabs_commsubtypes-settings']),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Admin Email Content","keywords"=>"admin user password reset update forgot forget email","description"=>"Configure Admin User forgot password & password updated notification email content","normal_access"=>"SideBar -> General Settings/ SideBar -> Email Content -> Admin User Settings","url"=>$this->app->url('xepan_communication_general_emailcontent_admin',['cut_page'=>1]),'mode'=>'frame'];
		$shortcuts[]=["title"=>"User Registration mode & Frontend Email Content","keywords"=>"frontend website customer user password reset update forgot forget verification subscribe email","description"=>"Configuration of Frontend user registration type forgot password & password updated notification ","normal_access"=>"SideBar -> General Settings/ SideBar -> Email Content -> FrontEnd User Settings","url"=>$this->app->url('xepan_communication_general_emailcontent_usertool',['cut_page'=>1]),'mode'=>'frame'];
		$shortcuts[]=["title"=>"Manage Country State List","keywords"=>"country state list manage","description"=>"Configuration of Countries and States","normal_access"=>"SideBar -> General Settings/ SideBar -> country & State","url"=>$this->app->url('xepan_communication_general_countrystate',['cut_page'=>1]),'mode'=>'frame'];
	}

	function setup_frontend(){
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('./vendor/xepan/communication/');


		$this->app->addHook('cron_executor',function($app){
			$now = \DateTime::createFromFormat('Y-m-d H:i:s', $this->app->now);
			echo "Email Fetch <br/>";
			var_dump($now);
			$job1 = new \Cron\Job\ShellJob();
			$job1->setSchedule(new \Cron\Schedule\CrontabSchedule('* * * * *'));
			if(!$job1->getSchedule() || $job1->getSchedule()->valid($now)){
				echo " Executing email fetching <br/>";
				$this->add('xepan\communication\Controller_Cron');				
			}

			$job2 = new \Cron\Job\ShellJob();
			$job2->setSchedule(new \Cron\Schedule\CrontabSchedule('*/30 * * * *'));
			if(!$job2->getSchedule() || $job2->getSchedule()->valid($now)){
				echo " Executing email fetching Bounce <br/>";
				$this->add('xepan\communication\Controller_BounceEmailCheck');				
			}

		});

		return $this;
	}

	function resetDB(){
		// if(!isset($this->app->old_epan)) $this->app->old_epan = $this->app->epan;
  //       if(!isset($this->app->new_epan)) $this->app->new_epan = $this->app->epan;
        
		// $this->app->epan=$this->app->old_epan;
  //       $truncate_model = ['Communication_Attachment','Communication','Communication_EmailSetting','Communication_SMSSetting'];
  //       foreach ($truncate_model as $t) {
  //           $m=$this->add('xepan\communication\Model_'.$t);
  //           foreach ($m as $mt) {
  //               $mt->delete();
  //           }
  //       }
        
  //       $this->app->epan=$this->app->new_epan;

        //Set Config
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
        // $frontend_config = $this->app->epan->config;
		// $frontend_config->setConfig('REGISTRATION_TYPE',"self_activated",'communication');
		$frontend_config_m['user_registration_type'] = "self_activated";

		$file_reg_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_subject.html'));
		$file_reg_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/registration_body.html'));
		$frontend_config_m['registration_subject'] = $file_reg_subject;	
		$frontend_config_m['registration_body'] = $file_reg_body;	
		// $frontend_config->setConfig('REGISTRATION_SUBJECT',$file_reg_subject,'base');
		// $frontend_config->setConfig('REGISTRATION_BODY',$file_reg_body,'base');
		
		$file_reset_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_subject.html'));
		$file_reset_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/reset_password_body.html'));
		$frontend_config_m['reset_subject'] =$file_reset_subject;
		$frontend_config_m['reset_body'] =$file_reset_body;
		// $frontend_config->setConfig('RESET_PASSWORD_SUBJECT',$file_reset_subject,'communication');
		// $frontend_config->setConfig('RESET_PASSWORD_BODY',$file_reset_body,'communication');
		
		$file_verification_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_subject.html'));
		$file_verification_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/verification_mail_body.html'));
		$frontend_config_m['verification_subject'] = $file_verification_subject;
		$frontend_config_m['verification_body'] = $file_verification_body;
		
		// $frontend_config->setConfig('VERIFICATIONE_MAIL_SUBJECT',$file_verification_subject,'communication');
		// $frontend_config->setConfig('VERIFICATIONE_MAIL_BODY',$file_verification_body,'communication');

		$file_update_subject = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_subject.html'));
		$file_update_body = file_get_contents(realpath(getcwd().'/vendor/xepan/communication/templates/default/update_password_body.html'));
		$frontend_config_m['update_subject'] = $file_update_subject;
		$frontend_config_m['update_body'] = $file_update_body;
		$frontend_config_m->save();
		
		// $frontend_config->setConfig('UPDATE_PASSWORD_SUBJECT',$file_update_subject,'communication');
		// $frontend_config->setConfig('UPDATE_PASSWORD_BODY',$file_update_body,'communication');

	}
}
