<?php

/**
* description: epan may have many Email Settings for sending and receiving enails.
* Since xEpan is primarily for cloud multiuser SaaS. Email settings are considered as base
* and included in Epan, not in any top layer Application.
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;

class Model_Communication_EmailSetting extends \xepan\base\Model_Table{

	public $table='emailsetting';
	public $acl_type="Communication_EmailSetting";
	
	public $status="";
	public $actions=[
		'Active'=>['view','edit','delete','deactivate','toggle_mass_email_use','duplicate','checkConnection'],
		'InActive'=>['view','edit','delete','activate','toggle_mass_email_use'],
	];
	function init(){
		parent::init();
		// TODO : add all required fields for email + can_use_in_mass_emails
		// $this->hasOne('xepan\base\Epan','epan_id');
		$this->hasOne('xepan\base\Contact','created_by_id')->defaultValue(@$this->app->employee->id);
		$this->addField('name');
		$this->addField('email_transport')->setValueList(array('SmtpTransport'=>'SMTP','SendmailTransport'=>'SendMail','MailTransport'=>'PHP Mail function'))->defaultValue('SmtpTransport')->display(['form'=>'xepan\base\DropDown']);
		$this->addField('is_active')->type('boolean')->defaultValue(false);
		$this->addField('is_support_email')->type('boolean')->defaultValue(false);

		$this->addField('encryption')->enum(array('none','ssl','tls'))->mandatory(true);
		$this->addField('email_host');
		$this->addField('email_port');
		$this->addField('email_username');
		$this->addField('email_password')->type('password');

		$this->addField('from_email');
		$this->addField('from_name');
		$this->addField('sender_email');
		$this->addField('sender_name');
		$f=$this->addField('email_reply_to');
		$f=$this->addField('email_reply_to_name');

		$this->addField('imap_email_host')->caption('Host');
		$this->addField('imap_email_port')->caption('Port');
		$this->addField('imap_email_username')->caption('Username');
		$this->addField('imap_email_password')->type('password')->caption('Password');
		$this->addField('imap_flags')->mandatory(true)->defaultValue('/imap/ssl/novalidate-cert')->caption('Flags');
		$this->addField('is_imap_enabled')->type('boolean')->defaultValue(true);
		$this->addField('bounce_imap_email_host')->caption('Host');
		$this->addField('bounce_imap_email_port')->caption('Port');
		$this->addField('return_path')->Caption('Username / Email');
		
		$this->addField('bounce_imap_email_password')->type('password')->caption('Password');
		$this->addField('bounce_imap_flags')->mandatory(true)->defaultValue('/imap/ssl/novalidate-cert')->caption('Flags');

		$this->addField('smtp_auto_reconnect')->type('int')/*->hint('Auto Reconnect by n number of emails')*/;
		$this->addField('email_threshold')->type('int')/*->hint('Threshold To send emails with this Email Configuration PER MINUTE')*/;
		$this->addField('email_threshold_per_month')->type('int');

		$this->addField('emails_in_BCC')->type('int')/*->hint('Emails to be sent by bunch of Bcc emails, to will be used same as From, 0 to send each email in to field')*/->defaultValue(0);
		$this->addField('last_emailed_at')->type('datetime')->system(true);
		$this->addField('email_sent_in_this_minute')->type('int')->system(true);
		$this->addField('last_email_fetched_at')->type('datetime')->system(true)->defaultValue($this->app->now);

		$this->addField('auto_reply')->type('boolean');
		$this->addField('email_subject')->group('ar~12');
		$this->addField('email_body')->type('text')->display(['form'=>'xepan\base\RichText']);
		$this->addField('signature')->type('text')->display(['form'=>'xepan\base\RichText']);

		$this->addField('denied_email_subject');
		$this->addField('denied_email_body')->type('text');

		$this->addField('footer')->type('text');

		$this->addField('mass_mail')->caption('Use For Mass Mailing')->type('boolean');
		
		$this->hasMany('xepan\hr\Post_Email_Association','emailsetting_id',null,'EmailAssociation');

		$this->addExpression('status')->set(function($m,$q){
			// return '"Active"';
			return $q->expr('IF([0]=1,"Active","InActive")',[$q->getField('is_active')]);
		});

		$this->addHook('beforeSave',[$this,'checkLimits']);
		// $this->addHook('beforeSave',[$this,'checkThresholdLimit']);
	}

	function checkLimits(){
		$extra_info = $this->app->recall('epan_extra_info_array',false);
		// check email account are allowed or not
		// 0 means unlimitted value
        if((isset($extra_info ['specification']['Email Accounts'])) AND ($extra_info ['specification']['Backend User Limit'] > 0) ){
        	$email_count = $this->add('xepan\communication\Model_Communication_EmailSetting');
        	if($this->loaded())
        		$email_count->addCondition('id','<>',$this->id);

        	$email_count = $email_count->count()->getOne();
			        	
        	if($email_count >= $extra_info ['specification']['Email Accounts']){
        		throw $this->exception("Sorry ! You cannot add more email settings. Your usage limit is over")
        				->addMoreInfo('Email Settings Count',$email_count)
        				->addMoreInfo('Email Settings Limit',$extra_info ['specification']['Email Accounts']);
        	}
        }

        // check email imap is allowed or not -- Email IMAP Account Allowed
     	if(isset($extra_info['specification']['Email IMAP Account Allowed']) AND ($extra_info['specification']['Email IMAP Account Allowed'] > 0)){
			$imap_count = $this->add('xepan\communication\Model_Communication_EmailSetting')
							->addCondition('is_imap_enabled',true);
			if($this->loaded())
				$imap_count->addCondition('id','<>',$this->id);

			$imap_count = $imap_count->count()->getOne();

			if(($imap_count >= $extra_info['specification']['Email IMAP Account Allowed']) AND $this['is_imap_enabled']){
				throw $this->exception("Sorry ! You cannot add more email IMAP settings. Your usage limit is over")
					->addMoreInfo('Email Setting IMAP Count',$imap_count)
					->addMoreInfo('Email Setting IMAP Limit',$extra_info ['specification']['Email IMAP Account Allowed']);
			}
		}

		// check email Mass Email Setting Allowed
     	if(isset($extra_info['specification']['Mass Email Setting Allowed']) AND ($extra_info['specification']['Mass Email Setting Allowed'] > 0) ){
			$mass_email_count = $this->add('xepan\communication\Model_Communication_EmailSetting')
							->addCondition('mass_mail',true);
			if($this->loaded())
				$mass_email_count->addCondition('id','<>',$this->id);

			$mass_email_count =  $mass_email_count->count()->getOne();
			
			if(($mass_email_count >= $extra_info['specification']['Mass Email Setting Allowed']) AND $this['mass_mail']){
				throw $this->exception("Sorry ! You cannot add more email account with mass mailling options . Your usage limit is over")
					->addMoreInfo('Email Mass Mailling Count',$mass_email_count)
					->addMoreInfo('Email Setting IMAP Limit',$extra_info['specification']['Mass Email Setting Allowed']);
			}
		}

		// checking threshold account -- Sendig Email Threshold Per Minute Per Setting
		if(isset($extra_info['specification']['Sendig Email Threshold Per Minute Per Setting'])){

			if($this['email_threshold'] > $extra_info['specification']['Sendig Email Threshold Per Minute Per Setting']){
				throw $this->exception("Sorry ! You are not allowed to add this threshold limit .")
					->addMoreInfo('Email Threshold you are adding',$this['email_threshold'])
					->addMoreInfo('Email Threshold Allowed',$extra_info['specification']['Sendig Email Threshold Per Minute Per Setting']);
			}
		}

	}

	function checkThresholdLimit(){
		$extra_info = $this->app->recall('epan_extra_info_array',false);

        if((isset($extra_info ['specification']['threshold'])) AND ($extra_info ['specification']['threshold'] != 0)){
        	$email_threshold = $this->add('xepan\communication\Model_Communication_EmailSetting')
        							->addCondition('mass_mail',true)
        							->addCondition('id','<>',$this->id)
        							->sum('email_threshold')->getOne();
        	$old_threshold = $email_threshold;
        	$email_threshold += $this['email_threshold'];
        									
        	if($email_threshold > $extra_info ['specification']['threshold']){
        		throw $this->exception("Sorry ! You cannot add this much threshold. Your usage limit is over")
        				->addMoreInfo('New Threshold',$email_threshold)
        				->addMoreInfo('Threshold Limit',$extra_info ['specification']['threshold']);
        	}
        }
	}

	function page_duplicate($p){
		$f = $p->add('Form');
		$f->addField('line','name')->validate('required');
		$f->addField('line','email_username')->validate('required');
		$f->addField('password','email_password')->validate('required');
		$f->addField('line','from_email')->validate('required');
		$f->addField('line','from_name')->validate('required');

		$f->addSubmit('Duplicate');

		if($f->isSubmitted()){
			$duplicate_email_m = $this->add('xepan\communication\Model_Communication_EmailSetting')->load($this->id);
			$duplicate_email_m->duplicate($f['name'],$f['email_username'],$f['email_password'],$f['from_email'],$f['from_name']);
			$this->app->page_action_result = $f->js(null,$f->js()->closest('.dialog')->dialog('close'))->univ()->successMessage('Duplicate SuccessFully');
		}
		
	}

	function duplicate($name,$email_username,$email_password,$from_email,$from_name){
		$new_email = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$new_email['name'] = $name ;
		$new_email['encryption'] = $this['encryption'];
		$new_email['email_host'] = $this['email_host'];
		$new_email['email_port'] = $this['email_port'] ;
		$new_email['email_username'] = $email_username;			
		$new_email['email_password'] = $email_password;			
		$new_email['from_email'] = $from_email;			
		$new_email['from_name'] = $from_name;
		$new_email['email_transport'] = $this['email_transport'];			
		$new_email['is_active'] = $this['is_active'];			
		$new_email['is_support_email'] = $this['is_support_email'];	
		$new_email['sender_email'] = $from_email;			
		$new_email['sender_name'] = $from_name;			
		$new_email['email_reply_to'] = $from_email;			
		$new_email['email_reply_to_name'] = $from_name;	
		$new_email['imap_email_host'] = $this['imap_email_host'];	
		$new_email['imap_email_port'] = $this['imap_email_port'];	
		$new_email['imap_email_username'] = $email_username;	
		$new_email['imap_email_password'] = $email_password;	
		$new_email['imap_flags'] = $this['imap_flags'];	
		$new_email['is_imap_enabled'] = $this['is_imap_enabled'];	
		$new_email['bounce_imap_email_host'] = $this['bounce_imap_email_host'];	
		$new_email['bounce_imap_email_port'] = $this['bounce_imap_email_port'];	
		$new_email['return_path'] = $this['return_path'];	
		$new_email['bounce_imap_email_password'] = $this['bounce_imap_email_password'];	
		$new_email['smtp_auto_reconnect'] = $this['smtp_auto_reconnect'];	
		$new_email['email_threshold'] = $this['email_threshold'];	
		$new_email['email_threshold_per_month'] = $this['email_threshold_per_month'];	
		$new_email['emails_in_BCC'] = $this['emails_in_BCC'];	
		$new_email['last_emailed_at'] = $this['last_emailed_at'];	
		$new_email['email_sent_in_this_minute'] = $this['email_sent_in_this_minute'];	
		$new_email['auto_reply'] = $this['auto_reply'];	
		$new_email['email_subject'] = $this['email_subject'];	
		$new_email['email_body'] = $this['email_body'];	
		$new_email['signature'] = $this['signature'];	
		$new_email['denied_email_subject'] = $this['denied_email_subject'];	
		$new_email['denied_email_body'] = $this['denied_email_body'];	
		$new_email['mass_mail'] = $this['mass_mail'];	
		$new_email->addHook('afterSave',function($m){
			$this->app->employee
					->addActivity("Email Settings Of Email : '".$this['name']."' Duplicated To New Email : '".$m['name']."' ", $m->id/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_email&emailsetting_id=".$m->id."")
					->notifyWhoCan('used','Active');
		});
		$new_email->save();

	}

	function page_checkConnection($page){
		$page->add('View')->set('TODO');
	}	

	function isUsable(){
		// emails sent in this minute is under limit
		// echo "Testing ". $this['name']. '<br/>';
		// echo "Last Email at " . $this['last_emailed_at'] .'<br/>';

		// echo 'date("Y-m-d H:i:00",strtotime($this["last_emailed_at"])) = ' .date('Y-m-d H:i:00',strtotime($this['last_emailed_at'])) . '<br/>';
		// echo 'date("Y-m-d H:i:00",strtotime($this->app->now)) = ' . date('Y-m-d H:i:00',strtotime($this->app->now)) . '<br/>';

		$this_minute_ok=false;
		$this_month_ok=false;

		$in_same_minute=false;
		if(date('Y-m-d H:i:00',strtotime($this['last_emailed_at'])) == date('Y-m-d H:i:00',strtotime($this->app->now)))
			$in_same_minute= true;
		
		if(!$in_same_minute) {
			$this['email_sent_in_this_minute']=0;
			$this->save();
			$this_minute_ok = true;
		}elseif($this['email_sent_in_this_minute'] < $this['email_threshold']){
			$this_minute_ok = true;
		}

		// emails sent in this month is under limit
		$month_emails_count = $this->add('xepan\communication\Model_Communication')
			->addCondition('communication_channel_id',$this->id)
			->addCondition('created_at','>=',date('Y-m-01',strtotime($this->app->now)))
			->addCondition('created_at','<',$this->app->nextDate(date('Y-m-t',strtotime($this->app->now))))
			->count();

		if($month_emails_count < $this['email_threshold_per_month'])
			$this_month_ok = true;

		if($this_minute_ok==true && $this_month_ok==true){
			echo $this['name']." is usable<br/>";
			return true;
		}

		echo $this['name']." is un-usable<br/>";
		return false;
	}

	function deactivate(){
		$this['is_active']=false;
		$this->save();
	}

	function activate(){
		$this['is_active']=true;
		$this->save();
	}

	function toggle_mass_email_use(){
		$this['mass_mail'] = !$this['mass_mail'];
		$this->save();
	}

	function loadNextMassEmail(){
		$other_settings = $this->add('xepan\communication\Model_Communication_EmailSetting')
								->addCondition('mass_mail',true)
								->addCondition('is_active',true)
								->addCondition('id','<>',$this->id)
								;

		foreach ($other_settings as $settings) {
			if($settings->isUsable()){
				return $this->load($settings->id);
			}
		}

		// echo "-- Did not foind any next mass email setting <br/>";
		return false;
	}
}
