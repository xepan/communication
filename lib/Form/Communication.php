<?php


namespace xepan\communication;

class Form_Communication extends \Form {

	public $contact = null;

	function init(){
		parent::init();

		$this->setLayout('view\communicationform');
		$type_field = $this->addField('dropdown','type')
			->setValueList(['Email'=>'Email','Phone'=>'Call','Comment'=>'Personal','SMS'=>'SMS']);
		
		$this->addField('dropdown','status')
			->setValueList(['Called'=>'Called','Received'=>'Received'])->setEmptyText('Please Select');

		$this->addField('title')->validate('required');
		$this->addField('xepan\base\RichText','body')->validate('required');
		$from_email=$this->addField('dropdown','from_email')->setEmptyText('Please Select From Email');
		$from_email->setModel('xepan\hr\Model_Post_Email_MyEmails');
		
		$email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');
		if($_GET['from_email'])
			$email_setting->tryLoad($_GET['from_email']);
		$view=$this->layout->add('View',null,'signature')->setHTML($email_setting['signature']);
		$from_email->js('change',$view->js()->reload(['from_email'=>$from_email->js()->val()]));

		$notify_email = $this->addField('Checkbox','notify_email','');
		$notify_email_to = $this->addField('line','notify_email_to');
		$this->addField('line','email_to');
		$this->addField('line','cc_mails');
		$this->addField('line','bcc_mails');
		$this->addField('line','from_phone');
		$emp_field = $this->addField('DropDown','from_person');
		$emp_field->setModel('xepan\hr\Employee');
		$emp_field->set($this->app->employee['name']);
		$this->addField('line','called_to');
		$this->addField('line','from_number');
		$this->addField('line','sms_to');

		$type_field->js(true)->univ()->bindConditionalShow([
			'Email'=>['from_email','email_to','cc_mails','bcc_mails'],
			'Phone'=>['from_email','from_phone','from_person','called_to','notify_email','notify_email_to','status'],
			'Personal'=>[],
			'SMS'=>['from_number','sms_to']
		],'div.atk-form-row');

		// $notify_email->js(true)->univ()->bindConditionalShow([
		// 	'Phone'=>['notify_email_to'],
		// ],'div.atk-form-row');

		$this->addHook('validate',[$this,'validateFields']);

	}

	function validateFields(){
        $commtype = $this['type'];
		$communication = $this->add('xepan\communication\Model_Communication_'.$commtype);

        switch ($commtype) {
			case 'Email':
				foreach (explode(',', $this['email_to']) as $value) {
					if( ! filter_var(trim($value), FILTER_VALIDATE_EMAIL))
						$this->displayError('email_to',$value.' is not a valid email');
				}
				$_to_field='email_to';

				if(!$communication->verifyTo($this[$_to_field], $this->contact->id)){
					throw new \Exception($commtype." of customer not present");	
				}
		
				break;
			case 'Phone':
				if(!$this['from_phone'])
					$this->displayError('from_phone','from_phone is required');
				if(!$this['called_to'])
					$this->displayError('called_to','called_to is required');
				if(!$this['status'])
					$this->displayError('status','Status is required');
				
				if($this['notify_email']){
					if(!$this['notify_email_to'])
						$this->displayError('notify_email_to','Notify Email is required');
					if(!$this['from_email'])
						$this->displayError('from_email','From  Email is required to send Email');
					
					foreach (explode(',', $this['notify_email_to']) as $value) {
						if( ! filter_var(trim($value), FILTER_VALIDATE_EMAIL))
							$this->displayError('notify_email_to',$value.' is not a valid email');
					}
					$_to_field='notify_email_to';	
				}

				$_to_field='called_to';
				break;
			case 'SMS':
				if(!$this['from_number'])
					$this->displayError('from_number','from_number is required');
				if(!$this['sms_to'])
					$this->displayError('sms_to','sms_to is required');
				$_to_field='sms_to';
				break;
			case 'Comment':
				$_to_field=null;
				break;
			default:
				break;
		}
    }

    function setContact($contact){
    	$this->contact = $contact;
    }

    function process(){

    	if(!$this->contact || ! $this->contact instanceof \xepan\base\Model_Contact)
    		throw $this->exception('Please setContact() on this form first');

    	$commtype = $this['type'];
					
		$communication = $this->add('xepan\communication\Model_Communication_'.$commtype);
		$communication['from_id']=$this['from_person'];
		$communication['to_id']=$this->contact->id;

		switch ($commtype) {
			case 'Email':
				$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
				$send_settings->tryLoad($this['from_email']?:-1);
				$_from = $send_settings['from_email'];
				$_from_name = $send_settings['from_name'];
				$_to_field='email_to';
				$communication->setFrom($_from,$_from_name);
				break;
			case 'Phone':
				$send_settings = $this['from_phone'];
				if($this['status']=='Received'){
					$communication['from_id']=$this->contact->id;
					$communication['to_id']=$this['from_person']; // actually this is to person this time
					$communication->setFrom($this['from_phone'],$this->contact['name']);
				}else{
					$communication['from_id']=$this['from_person']; // actually this is to person this time
					$communication['to_id']=$this->contact->id;
					$employee_name=$this->add('xepan\hr\Model_Employee')->load($this['from_person'])->get('name');
					$communication->setFrom($this['from_phone'],$employee_name);
				}

				if($this['notify_email']){
					if(!$this['notify_email_to'])
						$this->displayError('notify_email_to','Notify Email is required');
					
					$send_settings = $this->add('xepan\communication\Model_Communication_EmailSetting');
					$send_settings->tryLoad($this['from_email']?:-1);
				}

				$communication['status']=$this['status'];
				$_to_field='called_to';

				break;

			case 'SMS':
				if(!$this['from_number'])
					$this->displayError('from_number','from_number is required');
				if(!$this['sms_to'])
					$this->displayError('sms_to','sms_to is required');
				$send_settings = $this->add('xepan\communication\Model_Epan_SMSSetting');
				$send_settings->load($this['from_sms']);
				$_from = $email_settings['from_number'];
				$_from_name = $email_settings['from_sms_code'];
				$_to_field='sms_to';
				$communication->setFrom($_from,$_from_name);
				break;
			case 'Comment':
				$_from = $this->app->employee->id;
				$_from_name = $this->app->employee['name'];
				$_to = $model_contact->id;
				$_to_name = $model_contact['name'];
				$_to_field=null;
				$communication->addTo($_to, $_to_name);
				$communication->setFrom($_from,$_from_name);
				break;
			default:
				break;
		}
		
		$communication->setSubject($this['title']);
		$communication->setBody($this['body']);

		if($_to_field){	
			foreach (explode(',',$this[$_to_field]) as $to) {
				$communication->addTo(trim($to));
			}			
		}
		
		if($this['bcc_mails']){
			foreach (explode(',',$this['bcc_mails']) as $bcc) {
					if( ! filter_var(trim($bcc), FILTER_VALIDATE_EMAIL))
						$this->displayError('bcc_mails',$bcc.' is not a valid email');
				$communication->addBcc($bcc);
			}
		}

		if($this['cc_mails']){
			foreach (explode(',',$this['cc_mails']) as $cc) {
					if( ! filter_var(trim($cc), FILTER_VALIDATE_EMAIL))
						$this->displayError('cc_mails',$cc.' is not a valid email');
				$communication->addCc($cc);
			}
		}

		$communication->send(
				$send_settings,
				$this['notify_email']?$this['notify_email_to']:''
				);

		return $communication;
    }
}