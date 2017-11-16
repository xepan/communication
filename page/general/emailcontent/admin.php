<?php

namespace xepan\communication;


/**
* 
*/
class page_general_emailcontent_admin extends \xepan\communication\page_sidebar{
	public $title="Admin Email Content";
	function init(){
		parent::init();

		/*Reset Password Email Content*/
		$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
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
		$config_m->add('xepan\hr\Controller_ACL');
		$config_m->tryLoadAny();
		// $resetpass_config = $this->app->epan->config;
		// $reset_subject = $resetpass_config->getConfig('RESET_PASSWORD_SUBJECT_FOR_ADMIN');
		// $reset_body = $resetpass_config->getConfig('RESET_PASSWORD_BODY_FOR_ADMIN');
		$form=$this->add('Form',null,'reset_email');
		$form->setModel($config_m,['reset_subject','reset_body']);
		$form->getElement('reset_subject')->set($config_m['reset_subject']);
		$form->getElement('reset_body')->set($config_m['reset_body'])->setFieldHint('{$username},{$click_here}');
		// $form->addField('line','subject')->set($reset_subject);
		// $form->addField('xepan\base\RichText','body')->set($reset_body);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			// $resetpass_config->setConfig('RESET_PASSWORD_SUBJECT_FOR_ADMIN',$form['subject'],'base');

			// $resetpass_config->setConfig('RESET_PASSWORD_BODY_FOR_ADMIN',$form['body'],'base');
			$config_m->app->employee
			    ->addActivity("'Reset Password Email' Content's Layout Updated For ERP Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_admin")
				->notifyWhoCan(' ',' ',$config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}

		/*Update Password Email Content*/
		// $update_config = $this->app->epan->config;
		// $update_subject = $update_config->getConfig('UPDATE_PASSWORD_SUBJECT_FOR_ADMIN');
		// $update_body = $update_config->getConfig('UPDATE_PASSWORD_BODY_FOR_ADMIN');
		$form=$this->add('Form',null,'updatepassword_view');
		$form->setModel($config_m,['update_subject','update_body']);
		$form->getElement('update_subject')->set($config_m['update_subject']);
		$form->getElement('update_body')->set($config_m['update_body'])->setFieldHint('{$username},{$email_id},{$password},{$organization},{$post}');
		// $form->addField('line','subject')->set($update_subject);
		// $form->addField('xepan\base\RichText','body')->set($update_body);
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			// $update_config->setConfig('UPDATE_PASSWORD_SUBJECT_FOR_ADMIN',$form['subject'],'base');

			// $update_config->setConfig('UPDATE_PASSWORD_BODY_FOR_ADMIN',$form['body'],'base');
			$config_m->app->employee
			    ->addActivity("'Update Password Email' Content's Layout Updated For ERP Users", null/* Related Document ID*/, null /*Related Contact ID*/,null,null,"xepan_communication_general_emailcontent_admin")
				->notifyWhoCan(' ',' ',$config_m);
			$form->js(null,$form->js()->reload())->univ()->successMessage('Layout Successfully Updated')->execute();
		}
	}
	
	function defaultTemplate(){
		return ['page/admin-user-email-content'];
	}
}