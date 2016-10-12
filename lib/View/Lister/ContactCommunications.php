<?php

namespace xepan\communication;

class View_Lister_ContactCommunications extends \CompleteLister{
	public $contact_id;
	function init(){
		parent::init();
		// throw new \Exception($contact['name'], 1);
	}
	function setModel($model){
		$contact=$this->add('xepan\base\Model_Contact')
		->load($this->contact_id);

		$this->template->trySet('contact',$contact['name'] ." Communications");
		
		parent::setModel($model);
	}

	function formatRow(){
		$this->current_row_html['body'] = $this->model['description'];
		
		$to_mail = json_decode($this->model['to_raw'],true);
		$to_lister = $this->app->add('CompleteLister',null,null,['view\communication\all-communication-viewer','to_lister']);
		$to_lister->setSource($to_mail);
			
		$cc_raw = json_decode($this->model['cc_raw'],true);
		$cc_lister = $this->app->add('CompleteLister',null,null,['view\communication\all-communication-viewer','cc_lister']);
		$cc_lister->setSource($cc_raw);

		$from_mail = json_decode($this->model['from_raw'],true);
		$from_lister = $this->app->add('CompleteLister',null,null,['view\communication\all-communication-viewer','from_lister']);
		$from_lister->setSource($from_mail);
		
		$this->current_row_html['from_lister'] =$from_lister->getHtml() ;
		$this->current_row_html['to_lister'] = $to_lister->getHtml();
		$this->current_row_html['cc_lister'] = $cc_lister->getHtml();

		return parent::formatRow();
	}

	function send($from_email=null,$to_emails=null,$cc_emails=null,$bcc_emails=null,$subject=null,$body=null,$view){
		$email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$email_setting->tryLoad($from_email?:-1);

		$communication = $this->add('xepan\communication\Model_Communication_Abstract_Email');					
		$communication->getElement('status')->defaultValue('Draft');
		$communication['direction']='Out';


		$communication->setfrom($email_setting['from_email'],$email_setting['from_name']);
		$communication->addCondition('communication_type','Email');
		
		$to_emails=explode(',', trim($to_emails));
		foreach ($to_emails as $to_mail) {
			$communication->addTo($to_mail);
		}
		if($cc_emails){
			$cc_emails=explode(',', trim($cc_emails));
			foreach ($cc_emails as $cc_mail) {
					$communication->addCc($cc_mail);
			}
		}
		if($bcc_emails){
			$bcc_emails=explode(',', trim($bcc_emails));
			foreach ($bcc_emails as $bcc_mail) {
					$communication->addBcc($bcc_mail);
			}
		}
		$communication->setSubject($subject);
		$communication->setBody($body);
		$communication->save();

		// Attach Invoice
		$file =	$this->add('xepan/filestore/Model_File',array('policy_add_new_type'=>true,'import_mode'=>'string','import_source'=>$this->generatePDF('return',$view)));
		$file['filestore_volume_id'] = $file->getAvailableVolumeID();
		$file['original_filename'] =  $this->app->employee['name'].'_'.$view->model->id.'.pdf';
		$file->save();
		$communication->addAttachment($file->id);
		
		$communication->findContact('to');

		$communication->send($email_setting);
	}

	function generatePDF($action ='return',$view){

		if(!in_array($action, ['return','dump']))
			throw $this->exception('Please provide action as result or dump');

		$pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('xEpan ERP');
		// $pdf->SetTitle($this['type']. ' '. $this['document_no']);
		// $pdf->SetSubject($this['type']. ' '. $this['document_no']);
		// $pdf->SetKeywords($this['type']. ' '. $this['document_no']);

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set font
		$pdf->SetFont('dejavusans', '', 10);
		//remove header or footer hr lines
		$pdf->SetPrintHeader(false);
		$pdf->SetPrintFooter(false);
		// add a page
		$pdf->AddPage();

		$html = $view->getHTML();

		// echo $html;
		// exit;

		// output the HTML content
		$pdf->writeHTML($html, false, false, true, false, '');
		// set default form properties
		$pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 255, 200), 'strokeColor'=>array(255, 128, 128)));
		// reset pointer to the last page
		$pdf->lastPage();
		//Close and output PDF document
		switch ($action) {
			case 'return':
				return $pdf->Output(null, 'S');
				break;
			case 'dump':
				return $pdf->Output(null, 'I');
				exit;
			break;
		}
	}

	function defaultTemplate(){
		return ['view\communication\all-communication-viewer'];
	}

}