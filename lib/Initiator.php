<?php

namespace xepan\communication;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_communication';

	function setup_admin(){
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('../vendor/xepan/communication/');

		if(!$this->app->isAjaxOutput() && !$this->app->getConfig('hidden_xepan_communication',false)){
			$this->app->side_menu->addItem(['Emails','icon'=>' fa fa-envelope','badge'=>["0/0",'swatch'=>' label label-primary pull-right']],'xepan_communication_emails')->setAttr(['title'=>'Emails'])->addClass('contact-and-all-email-count');
			$this->app->side_menu->addItem(['Message','icon'=>' fa fa-envelope','badge'=>["0/0",'swatch'=>' label label-primary pull-right']],'xepan_communication_internalmsg')->setAttr(['title'=>'Internal Communication'])->addClass('contact-and-all-message-count');
			$this->app->side_menu->addItem(['General Setting','icon'=>'fa fa-cog'],'xepan_communication_generalsetting')->setAttr(['title'=>'General Setting']);

		}
		$this->app->report_menu->addItem(['Employee Communication','icon'=>'fa fa-users'],'xepan_communication_report_employeecommunication');

		$search_communication = $this->add('xepan\communication\Model_Communication');
		$this->app->addHook('quick_searched',[$search_communication,'quickSearch']);
		$this->app->addHook('activity_report',[$search_communication,'activityReport']);
		// $this->app->addHook('contact_info',[$search_communication,'set_old_communication_info']);
		$this->app->addHook('widget_collection',[$this,'exportWidgets']);
        $this->app->addHook('entity_collection',[$this,'exportEntities']);
		return $this;
	}

	function exportWidgets($app,&$array){
        $array[] = ['xepan\communication\Widget_UnreadMails','level'=>'Individual','title'=>'Unread Mails'];
    }

    function exportEntities($app,&$array){
    	 $array['Communication'] = ['caption'=>'Communication','type'=>'DropDown','model'=>'xepan\communication\Model_Communication'];
    	 $array['Communication_EmailSetting'] = ['caption'=>'Communication_EmailSetting','type'=>'DropDown','model'=>'xepan\communication\Model_Communication_EmailSetting'];
    	 $array['Communication_SMSSetting'] = ['caption'=>'Communication_SMSSetting','type'=>'DropDown','model'=>'xepan\communication\Model_Communication_SMSSetting'];
    	 $array['COMPANY_AND_OWNER_INFORMATION'] = ['caption'=>'COMPANY_AND_OWNER_INFORMATION','type'=>'DropDown','model'=>'xepan\communication\Model_COMPANY_AND_OWNER_INFORMATION'];
    	 $array['ADMIN_LOGIN_RELATED_EMAIL'] = ['caption'=>'ADMIN_LOGIN_RELATED_EMAIL','type'=>'DropDown','model'=>'xepan\communication\Model_ADMIN_LOGIN_RELATED_EMAIL'];
    	 $array['FRONTEND_LOGIN_RELATED_EMAIL'] = ['caption'=>'FRONTEND_LOGIN_RELATED_EMAIL','type'=>'DropDown','model'=>'xepan\communication\Model_FRONTEND_LOGIN_RELATED_EMAIL'];
    	 $array['COMMUNICATION_SUB_TYPE'] = ['caption'=>'COMMUNICATION_SUB_TYPE','type'=>'DropDown','model'=>'xepan\communication\Model_COMMUNICATION_SUB_TYPE'];
    	 $array['Miscellaneous_Technical_Settings'] = ['caption'=>'Miscellaneous_Technical_Settings','type'=>'DropDown','model'=>'xepan\communication\Miscellaneous_Technical_Settings'];
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
