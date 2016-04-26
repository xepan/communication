<?php
namespace xepan\communication;

class page_emails extends \Page{

	public $title="Emails";

	function init(){
		parent::init();
		$email_view=$this->add('xepan\communication\View_Lister_EmailsList',null,'email_lister');

		$mail = $email_view->recall('mail')?:'%';
		$mailbox = $email_view->recall('mailbox','_Received');
		// $mailbox = $this->app->stickyGET('mailbox')?:'_ContactReceivedEmail';

		$email_model=$this->add('xepan\communication\Model_Communication_Email'.$mailbox);

		$email_model->addCondition('mailbox','like',$mail.'%');
		$email_model->setOrder('created_at','desc');


		$header = $this->add('xepan\communication\View_EmailHeader',null,'email_header');
		$mailboxes_view = $this->add('xepan\communication\View_EmailNavigation',null,'email_navigation');
		$mailboxes_view->js(true)->find('[data-mailbox="'.$mailbox.'"]')->closest('li')->addClass('active');
		
		$email_view->setModel($email_model);
		$paginator = $email_view->add('Paginator',null,'paginator');
		$paginator->setRowsPerPage(50);
		
		$my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
		$label_view=$this->add('xepan\communication\View_Lister_EmailLabel',null,'email_labels');
		$label_view->setModel($my_email);
		$label_view->js(true)->find('[data-mail="'.$mail.'"]')->addClass('fa fa-check-square');

		// Populate links with js->on
		$url = $this->api->url(null,['cut_object'=>$email_view->name]);
		
		// $mailboxes_view->on('click','a',function($js,$data)use($mailboxes_view,$email_view,$url){
		// 	return [
		// 			$mailboxes_view->js()->find('li')->removeClass('active'),
		// 			$email_view->js()->reload(null,null,$this->app->url($url,['mailbox'=>$data['mailbox']])),
		// 			$js->closest('li')->addClass('active')
		// 	];

		// });
		$mailboxes_view->on('click','a',function($js,$data)use($mailboxes_view,$email_view,$url){
			$email_view->memorize('mailbox',$data['mailbox']);
			$email_view->memorize('filter-contacts',(int)$data['filterContacts']);
			$email_view->memorize('starred',(int) $data['starred']);
			$email_view->memorize('draft',(int) $data['draft']);
			$email_view->memorize('trashed',(int) $data['trashed']);
			$email_view->memorize('sent',(int) $data['sent']);

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
					$email_view->js()->reload(null,null,$url,['mail'=>$data['mail']]),
					$js->addClass('fa fa-check-square')
			];
			// return [
			// 		$label_view->js()->find('.fa.fa-check-square')->removeClass('fa fa-check-square'),
			// 		$email_view->js()->reload(null,null,$this->app->url($url,['mail'=>$data['mail']])),
			// 		$js->addClass('fa fa-check-square')
			// ];

		});

		$email_view->on('click','li.clickable-row  div:not(.chbox, .star)',function($js,$data){
			return $js->univ()->location($this->api->url('xepan_communication_emaildetail',['email_id'=>$data['id']]));
		});

		$email_view->on('click','li > .star > a',function($js,$data)use($email_model){
			// load data['id'] wala e,mail and mark starred or remove is_starred
			$email_model->load($data['id']);
			$js_array=[];
			if($data['starred']=='1'){
				$js_array[] = $js->removeClass('starred');
				$js_array[] = $js->data('starred','0');
				$email_model['is_starred']=false;
			}
			else{
				$js_array[] = $js->addClass('starred');
				$js_array[] = $js->data('starred','1');
				$email_model['is_starred']=true;
			}
			$email_model->saveAndUnload();

			$js_array[] = $js->univ()->successMessage("done");

			return $js_array;
		});

		$header->on('click','li > .all-select',function($js,$data)use($email_view){
			return $email_view->js()->find('.chbox input')->attr('checked',true);

		});
		$header->on('click','li > .select-none',function($js,$data)use($email_view){
			return $email_view->js()->find('.chbox input')->attr('checked',false);

		});
		$header->on('click','button.fetch-refresh',function($js,$data){
			return $js->univ()->location();
		});
	}
	
	function defaultTemplate(){
		return['page/emails'];
	}
}