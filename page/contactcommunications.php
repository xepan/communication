<?php

namespace xepan\communication;

/**
* 
*/
class page_contactcommunications extends \xepan\base\Page{
	public $contact_id;
	function init(){
		parent::init();
			$this->contact_id = $this->app->stickyGET('contact_id');
			
			$f = $this->add('Form',null,null,['form/empty'])->addClass('all-communication');
		$f->setLayout('view/communication/send-all-communication');	
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');
		$f->addField('line','subject')->validate('required');
		$f->addField('xepan\base\RichText','body')->validate('required');
		$f->addField('line','to')->validate('required');
		$f->addField('line','cc');
		$f->addField('line','bcc');
		$from_email=$f->addField('dropdown','from_email')->setEmptyText('Please Select From Email');
		$from_email->setModel('xepan\hr\Model_Post_Email_MyEmails');
	
		$email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');
		if($_GET['all_communication_from_email'])
			$email_setting->tryLoad($_GET['all_communication_from_email']);
		
		$view=$f->layout->add('View',null,'signature')->setHTML($email_setting['signature']);
		$from_email->js('change',$view->js()->reload(['all_communication_from_email'=>$from_email->js()->val()]));
		

		$f->addSubmit('Send')->addClass('btn btn-primary');
		$communication = $this->add('xepan\communication\Model_Communication');

		$communication->addCondition(
					$communication->dsql()->orExpr()
					->where('from_id',$this->contact_id)
					->where('to_id',$this->contact_id)
				);

		$from_date=$this->app->stickyGET('from_date');
		$to_date=$this->app->stickyGET('to_date');
		if($_GET['filter']){
			if($_GET['from_date'])
				$communication->addCondition('created_at','>=',$_GET['from_date']);
			if($_GET['to_date'])
				$communication->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
		}
		
		
		if($f->isSubmitted()){
			$v=$this->add('xepan\communication\View_Lister_ContactCommunications',['contact_id'=>$this->contact_id]);
			$v->setModel($communication);
			$js=[
				$f->js()->reload(
								[
									'from_date'=>$f['from_date']?:0,
									'to_date'=>$f['to_date']?:0,
									'filter'=>1
					]),
				$f->js()->closest('.dialog')->dialog('close')
				];

			$v->generatePDF('return',$v);
			$v->send($f['from_email'],$f['to'],$f['cc'],$f['bcc'],$f['subject'],$f['body'],$v);
			return $f->js(null,$js)->univ()->successMessage('Email Send SuccessFully');
		}

			
	}
	
}