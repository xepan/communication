<?php

namespace xepan\communication;

class page_general_emailcontent_usertool extends \xepan\communication\page_sidebar{
	public $title="User Panel Setting (For Website Users)";
	function init(){
		parent::init();

		/*Frontend User Configuration*/
		$frontend_config_m = $this->add('xepan\base\Model_ConfigJsonModel',
		[
			'fields'=>[
						'user_registration_type'=>'DropDown',
						'reset_subject'=>'Line',
						'reset_body'=>'xepan\base\RichText',
						'reset_sms_content'=>'Text',
						'update_subject'=>'Line',
						'update_body'=>'xepan\base\RichText',
						'update_sms_content'=>'Text',
						'registration_subject'=>'Line',
						'registration_body'=>'xepan\base\RichText',
						'registration_sms_content'=>'Text',
						'verification_subject'=>'Line',
						'verification_body'=>'xepan\base\RichText',
						'verification_sms_content'=>'Text',
						'subscription_subject'=>'Line',
						'subscription_body'=>'xepan\base\RichText',
					],
				'config_key'=>'FRONTEND_LOGIN_RELATED_EMAIL',
				'application'=>'communication'
		]);
		$frontend_config_m->add('xepan\hr\Controller_ACL');
		$frontend_config_m->tryLoadAny();

		$f = $this->add('Form',null,'frontend_user_config');
		$f->setModel($frontend_config_m,['user_registration_type']);
		$user_registration_type = $f->getElement('user_registration_type')->set($frontend_config_m['user_registration_type']);
		$user_registration_type->setValueList(['self_activated'=>'Self Activation Via Email','admin_activated'=>'Admin Activated',"default_activated"=>'Default Activated'])->validate('required');

		$f->addSubmit('Update')->addClass('btn btn-primary');
		
		if($f->isSubmitted()){
			$f->save();
			if($f['user_registration_type'] == 'self_activated'){
				$type = 'Self Activated Via Email';
			}elseif ($f['user_registration_type'] == 'admin_activated') {
				$type = 'Admin Activated';
			}elseif ($f['user_registration_type'] == 'default_activated') {
				$type = 'Default Activated';
			}
			$frontend_config_m->app->employee
			    ->addActivity("'In Frontend User Configuration' User Registration Type Updated as '".$type."' For Website Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$frontend_config_m);
			$f->js(null,$f->js()->reload())->univ()->successMessage('Information Successfully Updated')->execute();
		}

		/*Reset Password Email Content*/
		$form=$this->add('Form',null,'reset_email');
		$form->add('View')->setHtml('Use this values in your layout: {$username}, {$email_id}, {$click_here}, {$url}, {$hash}, {$status}')
				->addClass('alert alert-info');
		$form->setModel($frontend_config_m,['reset_subject','reset_body','reset_sms_content']);
		$form->getElement('reset_subject')->set($frontend_config_m['reset_subject']);
		$form->getElement('reset_body')->set($frontend_config_m['reset_body']);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$frontend_config_m->app->employee
			    ->addActivity("'Reset Password Email' Content's Layout Updated For Website Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$frontend_config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}

		/*Registration Email Content*/
		$form=$this->add('Form',null,'registration_view');
		$form->add('View')->setHtml('Use this values in your layout: {$username}, {$email_id}, {$click_here}, {$url}, {$hash}, {$status}')
				->addClass('alert alert-info');
		$form->setModel($frontend_config_m,['registration_subject','registration_body','registration_sms_content']);
		$form->getElement('registration_subject')->set($frontend_config_m['registration_subject']);
		$form->getElement('registration_body')->set($frontend_config_m['registration_body'])->setFieldHint('{$username},{$email_id},{$password},{$click_here},{$url}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$frontend_config_m->app->employee
			    ->addActivity("'New Registration Email' Content's Layout Updated For Website Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$frontend_config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}
		/*Verification Email Content*/
		$form=$this->add('Form',null,'verification_view');
		$form->add('View')->setHtml('Use this values in your layout: {$username}, {$email_id}, {$click_here}, {$url}, {$hash}, {$status}')
				->addClass('alert alert-info');
		$form->setModel($frontend_config_m,['verification_subject','verification_body','verification_sms_content']);
		$form->getElement('verification_subject')->set($frontend_config_m['verification_subject']);
		$form->getElement('verification_body')->set($frontend_config_m['verification_body'])->setFieldHint('{$username},{$email_id},{$password}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$frontend_config_m->app->employee
			    ->addActivity("'Verification Email' Content's Layout Updated For Website Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$frontend_config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}

		/*Update Password Email Content*/
		$form=$this->add('Form',null,'updatepassword_view');
		$form->add('View')->setHtml('Use this values in your layout: {$username}, {$email_id}, {$click_here}, {$url}, {$hash}, {$status}')
				->addClass('alert alert-info');

		$form->setModel($frontend_config_m,['update_subject','update_body','update_sms_content']);
		$form->getElement('update_subject')->set($frontend_config_m['update_subject']);
		$form->getElement('update_body')->set($frontend_config_m['update_body'])->setFieldHint('{$username},{$email_id},{$password}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$frontend_config_m->app->employee
			    ->addActivity("'Update Password Email' Content's Layout Updated For Website Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$frontend_config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}

		/*Subscription Content*/
		$form=$this->add('Form',null,'subscription_view');
		$form->setModel($frontend_config_m,['subscription_subject','subscription_body']);
		$form->getElement('subscription_subject')->set($frontend_config_m['subscription_subject']);
		$form->getElement('subscription_body')->set($frontend_config_m['subscription_body'])->setFieldHint('{$username},{$email_id}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$frontend_config_m->app->employee
			    ->addActivity("'Subscription Email' Content's Layout Updated For Website Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_usertool")
				->notifyWhoCan(' ',' ',$frontend_config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}	
	}

	function defaultTemplate(){
		return ['page/usertool-email-content'];
	}
}