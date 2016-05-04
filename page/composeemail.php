<?php
namespace xepan\communication;

class page_composeemail extends \Page{
	public $breadcrumb=['Home'=>'index','Inbox'=>'xepan\communication_emails'];
	public $title="Compose Email";
	function init(){
		parent::init();

		$to_email=$this->api->stickyGET('to_email_array');

		$action= 'add';
		$form = $this->add('Form');;
		$form->setLayout(['view/composeemail']);

		$mymail = $form->addField('Dropdown','email_username')->setEmptyText('Please Select From Email')->validate('required');
		$mymail->setModel('xepan\hr\Model_Post_Email_MyEmails');		
		
		// $mymail->on('change',function($js,$data)use($form){
		// 	return $form->js()->reload(['email'=>$data['shortname']]);
		// });

		$to_field = $form->addField('xepan\base\DropDown','email_to');
		$to_field->validate_values = false;
		$cc_field = $form->addField('xepan\base\Dropdown','email_cc');
		$cc_field->validate_values = false;
		$bcc_field = $form->addField('xepan\base\Dropdown','email_bcc');
		$bcc_field->validate_values = false;


		if($_GET[$this->name.'_src_email']){

			$results = [];
			$contact_info = $this->add('xepan\base\Model_Contact_Email');
			$contact_info->addCondition(
				$contact_info->dsql()->orExpr()
				->where('value','like','%'.$_GET['q'].'%')
				->where($contact_info->dsql()->expr('[0] like "%[1]%"',[$contact_info->refSQL('contact_id')->fieldQuery('name'),$_GET['q']]))
				);
			$contact_info->setLimit(20);
			
			foreach ($contact_info as $cont) {
				$results[] = ['id'=>$cont->id,'text'=>$cont['contact'].' <'.$cont['value'].'>'];
			}

			echo json_encode(
				[
					"results" => $results,
					"more"=>false	
				]
				);
			exit;
		}

		$to_field->select_menu_options = 
		$cc_field->select_menu_options = 
		$bcc_field->select_menu_options = 
			[	
				'width'=>'100%',
				'tags'=>true,
				'tokenSeparators'=>[',','\n\r'],
				'ajax'=>[
					'url' => $this->api->url(null,[$this->name.'_src_email'=>true])->getURL(),
					'dataType'=>'json'
				]
			];

		$to_field->setAttr('multiple','multiple');
		$cc_field->setAttr('multiple','multiple');
		$bcc_field->setAttr('multiple','multiple');

		
		$email_username_model=$this->add('xepan\base\Model_Epan_EmailSetting');
		if($_GET['email_username']){
			$email_username_model->tryLoad($_GET['email_username']);
		}
		$subject="";
		if($this->app->recall('subject'))
			$subject="Fwd .".$this->app->recall('subject');
		$message=$this->app->recall('message');

		$form->addField('email_subject')->set($subject);
		$form->addField('xepan\base\RichText','email_body')->set($message);
		$view=$form->add('View')->setHTML($email_username_model['signature']);
		$mymail->js('change',$view->js()->reload(['email_username'=>$mymail->js()->val()]));

		$this->app->forget('subject');
		$this->app->forget('message');

		// $mymail->js('change',$form->js()->atk4_form('reloadField','email_signature',[$this->app->url(),'email_username'=>$mymail->js()->val()]));
		
		$multi_upload_field = $form->addField('xepan\base\Form_Field_Upload','attachment',"")
									->allowMultiple()->addClass('xepan-padding');
									// ->display(['form'=>'xepan\base\Upload'])
										// ->setFormatFilesTemplate('xepan\base\Upload');

		$multi_upload_field->setAttr('accept','.jpeg,.png,.jpg');
		$multi_upload_field->setModel('filestore/Image');

		$form->addSubmit('Send Email')->addClass('btn btn-success  fa fa-send xepan-padding-small');
		$form->onSubmit(function($f){
			
			$email_settings = $this->add('xepan\base\Model_Epan_EmailSetting')->load($f['email_username']);
			$mail = $this->add('xepan\communication\Model_Communication_Email');
			$mail->setfrom($email_settings['from_email'],$email_settings['from_name']);
			
			foreach (explode(",",$f['email_to']) as $e2) {
				if(is_numeric(trim($e2))){
					$contact_info = $this->add('xepan\base\Model_Contact_Info');
					$contact_info->tryLoad($e2);
					if(!$contact_info->loaded())
						return $f->error('email_to','Value '.$e2.' is not acceptable...');
					$mail->addTo($contact_info['value'],$contact_info['contact']);
				}else{
					if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
						return $f->error('email_to','Value '.$e2.' is not acceptable');
					$mail->addTo($e2);
				}
			}

			foreach (explode(",",$f['email_cc']) as $e2) {
				if($f['email_cc']){
					if(is_numeric(trim($e2))){
						$contact_info = $this->add('xepan\base\Model_Contact_Info');
						$contact_info->tryLoad($e2);
							if($contact_info->id != $e2)
								return $f->error('email_cc','Value '.$e2.' is not acceptable...');
						$mail->addCC($contact_info['value'],$contact_info['contact']);
					}else{
						if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
							return $f->error('email_cc','Value '.$e2.' is not acceptable');
						$mail->addCC($e2);
					}
				}
			}

			foreach (explode(",",$f['email_bcc']) as $e2) {
				if($f['email_bcc']){
					if(is_numeric(trim($e2))){
						$contact_info = $this->add('xepan\base\Model_Contact_Info');
						$contact_info->tryLoad($e2);
						if($contact_info->id != $e2)
							return $f->error('email_bcc','Value '.$e2.' is not acceptable...');
						$mail->addBcc($contact_info['value'],$contact_info['contact']);
					}else{
						if(!filter_var($e2, FILTER_VALIDATE_EMAIL))
							return $f->error('email_bcc','Value '.$e2.' is not acceptable');
						$mail->addBcc($e2);
					}
				}
			}



			$upload_images_array = explode(",",$f['attachment']);
			$mail->setSubject($f['email_subject']);
			$mail->setBody($f['email_body']);
			
			$mail->findContact('to');

			$mail->save();

			foreach ($upload_images_array as $file_id) {
				$mail->addAttachment($file_id);
			}

			$mail->send($email_settings);
			return $f->js(null,$f->js()->univ()->successMessage('EMAIL SENT'))->reload();
		});
	}

}