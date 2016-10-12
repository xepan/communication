<?php 
namespace xepan\communication;
class page_general_email extends \xepan\base\Page{
	public $title="Email Settings";
	public $breadcrumb=['Home'=>'index','Emails Setting'=>'xepan_communication_generalsetting','Detail'=>'#'];

	function init(){
		parent::init();
		$action = $this->api->stickyGET('action')?:'view';

		$email_setting= $this->add('xepan\communication\Model_Communication_EmailSetting')->tryLoadBy('id',$this->api->stickyGET('emailsetting_id'));
		
		$email_view=$this->add('xepan\hr\View_Document',['action'=>$action,'submit_button'=>''],null,['view/setting/email-setting']);

		$email_view->setIdField('emailsetting_id');
		$email_view->setModel($email_setting,[
												'email_transport','name','is_active','is_support_email','encryption',
												'email_host','email_port',
												'email_username','email_password',
												'email_reply_to','email_reply_to_name',
												'from_email','from_name','sender_email',
												'sender_name','imap_email_host',
												'imap_email_port','imap_email_username',
												'imap_email_password','is_imap_enabled','imap_flags',
												'smtp_auto_reconnect','email_threshold','email_threshold_per_month',
												'emails_in_BCC','mass_mail','bounce_imap_email_host',
												'bounce_imap_email_port','return_path',
												'bounce_imap_email_password',
												'bounce_imap_flags','auto_reply','email_subject',
												'email_body','signature'
											],
											[
												'email_transport','name','is_active','is_support_email','encryption',
												'email_host','email_port',
												'email_username','email_password',
												'email_reply_to','email_reply_to_name',
												'from_email','from_name','sender_email',
												'sender_name','imap_email_host',
												'imap_email_port','imap_email_username',
												'imap_email_password','is_imap_enabled','imap_flags',
												'smtp_auto_reconnect','email_threshold','email_threshold_per_month',
												'emails_in_BCC','mass_mail','bounce_imap_email_host',
												'bounce_imap_email_port','return_path',
												'bounce_imap_email_password',
												'bounce_imap_flags','auto_reply','email_subject',
												'email_body','signature'
											]
							);
		$this->form = $email_view->form;
		// $is_support=$this->form->getElement('email_transport');		
		// $is_support->js(true)->univ()->bindConditionalShow([
		// 	'SmtpTransport'=>['name'],
		// 	'a'=>['email_subject']
		// 	],'div.atk-form-row');
	}

	function render(){
		$this->js(true)->_load('wizard')
			->_Selector('.wizard')
			->wizard()
			;
		
		$this->js('finished',$this->form->js()->submit())->_selector('.wizard');

		$this->app->jui->addStylesheet('compiled/wizard');
		parent::render();
	}
}