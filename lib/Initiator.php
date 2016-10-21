<?php

namespace xepan\communication;

class Initiator extends \Controller_Addon {
	public $addon_name = 'xepan_communication';

	function setup_admin(){
		$this->routePages('xepan_communication');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js'))
			->setBaseURL('../vendor/xepan/communication/');

		if(!$this->app->isAjaxOutput()){
			$my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
			$my_email->addExpression('post_email')->set(function($m,$q){
				return $q->getField('email_username');
			});

			$contact_email=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');
			$or = $contact_email->dsql()->orExpr();
			$i=0;
				foreach ($my_email as $email) {
					$or->where('mailbox','like',$email['post_email'].'%');
					$i++;
				}
				if($i == 0) $or->where('mailbox',-1);		
			
			
			$contact_email->addCondition($or);
			$contact_email->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
			$contact_count=$contact_email->count()->getOne();

			$all_email=$this->add('xepan\communication\Model_Communication_Email_Received');
			$or = $all_email->dsql()->orExpr();
			$i=0;
				foreach ($my_email as $email) {
					$or->where('mailbox','like',$email['post_email'].'%');
					$i++;
				}
				if($i == 0) $or->where('mailbox',-1);		
			
			
			$all_email->addCondition($or);
			$all_email->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
			$all_count=$all_email->count()->getOne();
			
			$this->app->side_menu->addItem(['Emails','icon'=>' fa fa-envelope','badge'=>[$contact_count. " / " .$all_count ,'swatch'=>' label label-primary pull-right']],'xepan_communication_emails')->setAttr(['title'=>'Emails']);
			$this->app->side_menu->addItem(['General Setting','icon'=>'fa fa-cog'],'xepan_communication_generalsetting')->setAttr(['title'=>'General Setting']);

		}

		$search_communication = $this->add('xepan\communication\Model_Communication');
		$this->app->addHook('quick_searched',[$search_communication,'quickSearch']);
		$this->app->addHook('contact_info',[$search_communication,'set_old_communication_info']);
		return $this;
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
			$job1->setSchedule(new \Cron\Schedule\CrontabSchedule('*/5 * * * *'));
			if(!$job1->getSchedule() || $job1->getSchedule()->valid($now)){
				echo " Executing email fetching <br/>";
				$this->add('xepan\communication\Controller_Cron');
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
