<?php

namespace xepan\communication;


/**
* 
*/
class page_general_emailcontent_admin extends \xepan\communication\page_sidebar{
	public $title="Admin Email Content";
	function init(){
		parent::init();

		if(!$this->api->auth->model->isSuperUser()){
			$this->add('View_Error')->set('Sorry, you are not permitted to handle acl, Ask respective authority / SuperUser');
			return;
		}else{

			/*Reset Password Email Content*/
			$resetpass_config = $this->app->epan->config;
			$reset_subject = $resetpass_config->getConfig('RESET_PASSWORD_SUBJECT_FOR_ADMIN');
			$reset_body = $resetpass_config->getConfig('RESET_PASSWORD_BODY_FOR_ADMIN');
			$form=$this->add('Form',null,'reset_email');
			$form->addField('line','subject')->set($reset_subject);
			$form->addField('xepan\base\RichText','body')->set($reset_body)->setFieldHint('{$name},{$email_id},{$password},{$click_here_to_activate}');
			$form->addSubmit('Update');

			if($form->isSubmitted()){
				$resetpass_config->setConfig('RESET_PASSWORD_SUBJECT_FOR_ADMIN',$form['subject'],'base');

				$resetpass_config->setConfig('RESET_PASSWORD_BODY_FOR_ADMIN',$form['body'],'base');
				$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
			}

			/*Update Password Email Content*/
			$update_config = $this->app->epan->config;
			$update_subject = $update_config->getConfig('UPDATE_PASSWORD_SUBJECT_FOR_ADMIN');
			$update_body = $update_config->getConfig('UPDATE_PASSWORD_BODY_FOR_ADMIN');
			$form=$this->add('Form',null,'updatepassword_view');
			$form->addField('line','subject')->set($update_subject);
			$form->addField('xepan\base\RichText','body')->set($update_body)->setFieldHint('{$name},{$email_id},{$password}');
			$form->addSubmit('Update');

			if($form->isSubmitted()){
				$update_config->setConfig('UPDATE_PASSWORD_SUBJECT_FOR_ADMIN',$form['subject'],'base');

				$update_config->setConfig('UPDATE_PASSWORD_BODY_FOR_ADMIN',$form['body'],'base');
				$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
			}
		}
	}
	
	function defaultTemplate(){
		return ['page/admin-user-email-content'];
	}
}