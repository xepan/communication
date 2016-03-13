<?php
namespace xepan\communication;

class page_composeemail extends \Page{
	function init(){
		parent::init();

		$action= 'add';
		$form = $this->add('Form');
		$form->setLayout(['view/composeemail']);

		$to_field = $form->addField('email_to');

		// if($_GET[$to_field->name]){
		// 	echo json_encode(
		// 		[
		// 			['text'=>'aaaaa','id'=>'aaaaa'],
		// 			['text'=>'bbbbb','id'=>'bbbbb'],
		// 			['text'=>'ccccccc','id'=>'ccccccc']
		// 		]
		// 		);
		// 	exit;
		// }

		// $to_field->select_menu_options = 
		// 	[	
		// 		'width'=>'100%',
		// 		'tags'=>true,
		// 		'tokenSeparators'=>[',']
		// 		// 'ajax'=>[
		// 		// 	'url' => $this->api->url(null,[$to_field->name=>true]),
		// 		// 	'dataType'=>'json',
		// 		// 	'data'=>['term'=>$to_field->js()->val()]
		// 		// ]
		// 	];

		// $to_field->setAttr('multiple','multiple');
		// $to_field->setModel('xepan\base\Contact');


		$form->addField('email_cc');
		$form->addField('email_subject');
		$form->addField('xepan\base\RichText','email_body');

		$form->onSubmit(function($f){

			$email_settings = $this->add('xepan\base\Model_Epan_EmailSetting')->tryLoadAny();
			$mail = $this->add('xepan\communication\Model_Communication_Email');
			$mail->setfrom($email_settings['from_email'],$email_settings['from_name']);
			$mail->addTo($f['email_to']);
			$mail->setSubject($f['email_subject']);
			$mail->setBody($f['email_body']);
			$mail->send($email_settings);
			return $f->js()->univ()->successMessage('OK');
		});
	}

}