<?php
namespace xepan\communication;

class page_emails extends \Page{

	public $title="Emails";

	function init(){
		parent::init();
		$email_view=$this->add('xepan\communication\View_Lister_EmailsList',null,'email_lister');

		$mail = $email_view->recall('mail','%');
		$mailbox = $email_view->recall('mailbox','Received');


		$email_model=$this->add('xepan\communication\Model_Communication_Email_'.$mailbox);

		$email_model->add('misc/Field_Callback','callback_date')->set(function($m){
			if(date('Y-m-d',strtotime($m['created_at']))==date('Y-m-d',strtotime($this->app->now))){
				return $m['created_at']=date('h:i:a',strtotime($m['created_at']));	
			}
			return $m['created_at']=date('M d',strtotime($m['created_at']));
		});


		$email_model->addCondition('mailbox','like',$mail.'%');
		if(($filter_contacts = $email_view->recall('filter-contacts',false))){			
			$email_model->addCondition(
					$email_model->dsql()->orExpr()
						->where('from_id','not',null)
						->where('to_id','not',null)
				);
		}


		$header = $this->add('xepan\communication\View_EmailHeader',null,'email_header');
		$mailboxes_view = $this->add('xepan\communication\View_EmailNavigation',null,'email_navigation');
		

		
		$email_view->setModel($email_model);
		$my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
		$label_view=$this->add('xepan\communication\View_Lister_EmailLabel',null,'email_labels');
		$label_view->setModel($my_email);

		// Populate links with js->on
		$url = $this->api->url(null,['cut_object'=>$email_view->name]);
		
		$mailboxes_view->on('click','a',function($js,$data)use($mailboxes_view,$email_view,$url){
			$email_view->memorize('mailbox',$data['mailbox']);
			$email_view->memorize('filter-contacts',(int)$data['filterContacts']);
			$email_view->memorize('starred',(int) $data['starred']);

			return [
					$mailboxes_view->js()->find('li')->removeClass('active'),
					$email_view->js()->reload(null,null,$url),
					$js->closest('li')->addClass('active')
			];

		});

		$label_view->on('click','a',function($js,$data)use($label_view,$email_view,$url){
			$email_view->memorize('mail',$data['mail']);
						
			return [
					$label_view->js()->find('.fa.fa-check-square')->removeClass('fa fa-check-square'),
					$email_view->js()->reload(null,null,$url),
					$js->addClass('fa fa-check-square')
			];

		});

		$email_view->on('click','li.clickable-row',function($js,$data){
			return $js->univ()->location($this->api->url('xepan_communication_emaildetail',['email_id'=>$data['id']]));
		});
	}
	
	function defaultTemplate(){
		return['page/emails'];
	}
}