<?php

namespace xepan\communication;

class page_general_emailcontent_usertool extends \xepan\communication\page_sidebar{
	public $title="User Panel Setting";
	function init(){
		parent::init();

		/*Frontend User Configuration*/
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
		$frontend_config_m->add('xepan\hr\Controller_ACL');
		$frontend_config_m->tryLoadAny();

		$f=$this->add('Form',null,'frontend_user_config');
		$f->setModel($frontend_config_m,['user_registration_type']);
		$user_registration_type = $f->getElement('user_registration_type')->set($frontend_config_m['user_registration_type']);
		$user_registration_type->setValueList(['self_activated'=>'Self Activation Via Email','admin_activated'=>'Admin Activated',"default_activated"=>'Default Activated'])->validate('required');
		$f->addSubmit('Update')->addClass('btn btn-primary');
		
		if($f->isSubmitted()){
			$f->save();
			$f->js(null,$f->js()->reload())->univ()->successMessage('Update Information')->execute();
		}
		/*Reset Password Email Content*/
		$form=$this->add('Form',null,'reset_email');
		$form->setModel($frontend_config_m,['reset_subject','reset_body']);
		$form->getElement('reset_subject')->set($frontend_config_m['reset_subject']);
		$form->getElement('reset_body')->set($frontend_config_m['reset_body'])->setFieldHint('{$name},{$email_id},{$password},{$click_here_to_activate}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		/*Registration Email Content*/
		$form=$this->add('Form',null,'registration_view');
		$form->setModel($frontend_config_m,['registration_subject','registration_body']);
		$form->getElement('registration_subject')->set($frontend_config_m['registration_subject']);
		$form->getElement('registration_body')->set($frontend_config_m['registration_body'])->setFieldHint('{$name},{$email_id},{$password},{$click_here_to_activate}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}
		/*Verification Email Content*/
		$form=$this->add('Form',null,'verification_view');
		$form->setModel($frontend_config_m,['verification_subject','verification_body']);
		$form->getElement('verification_subject')->set($frontend_config_m['verify_subject']);
		$form->getElement('verification_body')->set($frontend_config_m['verify_body']);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		/*Update Password Email Content*/
		$form=$this->add('Form',null,'updatepassword_view');
		$form->setModel($frontend_config_m,['update_subject','update_body']);
		$form->getElement('update_subject')->set($frontend_config_m['update_subject']);
		$form->getElement('update_body')->set($frontend_config_m['update_body'])->setFieldHint('{$name},{$email_id},{$password}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		/*Subscription Content*/
		$form=$this->add('Form',null,'subscription_view');
		$form->setModel($frontend_config_m,['subscription_subject','subscription_body']);
		$form->getElement('subscription_subject')->set($frontend_config_m['subscription_subject']);
		$form->getElement('subscription_body')->set($frontend_config_m['subscription_body'])->setFieldHint('{$name},{$email_id}');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}	
	}

	function defaultTemplate(){
		return ['page/usertool-email-content'];
	}
}