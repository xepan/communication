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

	public $acl=false;

	function init(){
		parent::init();
		// TODO : add all required fields for email + can_use_in_mass_emails
		$this->hasOne('xepan\base\Epan','epan_id');
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

		$this->addField('auto_reply')->type('boolean');
		$this->addField('email_subject')->group('ar~12');
		$this->addField('email_body')->type('text')->display(['form'=>'xepan\base\RichText']);
		$this->addField('signature')->type('text')->display(['form'=>'xepan\base\RichText']);

		$this->addField('denied_email_subject');
		$this->addField('denied_email_body')->type('text');

		$this->addField('footer')->type('text');

		$this->addField('mass_mail')->caption('Use For Mass Mailing')->type('boolean');
		
		$this->hasMany('xepan\hr\Post_Email_Association','emailsetting_id',null,'EmailAssociation');
	}

	function isUsable(){
		// emails sent in this minute is under limit
		$this_minute_ok=false;
		$this_month_ok=false;

		$in_same_minute=false;
		if(date('Y-m-d H:i:00',strtotime($this['last_emailed_at'])) == date('Y-m-d H:i:00',strtotime($this->app->now)))
			$in_same_minute= true;
		if(!$in_same_minute) {
			$this['email_sent_in_this_minute']=0;
			$this->save();
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

		if($this_month_ok==true && $this_month_ok==true)
			return true;

		return false;
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
	}
}
