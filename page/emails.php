<?php
namespace xepan\communication;

class page_emails extends \xepan\base\Page{

	public $title="Emails";

	function init(){
		parent::init();
		$fetch_emails = $this->app->stickyGET('fetch_emails');
		$search_value = $this->app->stickyGET('search');
		
		if($fetch_emails){
			$this->add('xepan\communication\Controller_Cron',['debug'=>false]);
		}
		$compose_view = $this->add('xepan\communication\View_ComposeEmailPopup',['communication_id'=>$_GET['communication_id'],'mode'=>$_GET['mode']],'compose_view');
		
		if($_GET['delete_emails']){
			foreach ($_GET['delete_emails'] as $delete_email) {
				$this->add('xepan\communication\Model_Communication_Abstract_Email')
				->load($delete_email)
				->delete();
			}
		}
		if($_GET['mark_read']){
			foreach ($_GET['mark_read'] as $mark_read_email) {
				$read_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
				$read_email->addCondition('communication_id',$mark_read_email);
				$read_email->addCondition('contact_id',$this->app->employee->id);
				$read_email->addCondition('is_read',true);
				$read_email->tryLoadAny();
				if(!$read_email->loaded()){
					$read_email->save();
				}
			}
		}
		if($_GET['mark_unread']){
			foreach ($_GET['mark_unread'] as $mark_unread_email) {
				$unread_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
				$unread_email->addCondition('communication_id',$mark_unread_email);
				$unread_email->addCondition('contact_id',$this->app->employee->id);
				$unread_email->addCondition('is_read',true);
				$unread_email->tryLoadAny();
				if($unread_email->loaded()){
					$unread_email->delete();
				}	
			}
		}

		if($_GET['mark_spam_emails']){
			foreach ($_GET['mark_spam_emails'] as $spam_email) {
				$spam_m = $this->add('xepan\communication\Model_Communication_Abstract_Email')
					->load($spam_email);
				$spam_m['status'] = "Junk";
				$spam_m->save();	
			}
		}

		if($_GET['mark_not_spam_emails']){
			foreach ($_GET['mark_not_spam_emails'] as $unSpam_email) {
				$spam_m = $this->add('xepan\communication\Model_Communication_Abstract_Email')
					->load($unSpam_email);
				$spam_m['status'] = "Received";
				$spam_m->save();	
			}
		}

		$my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');

		$email_view=$this->add('xepan\communication\View_Lister_EmailsList',null,'email_lister');
		
		$mail = $email_view->recall('mail')?:'%';
		$mailbox = $email_view->recall('mailbox','_Received');
		
		if($_GET['mailbox']){
			$mailbox = $this->app->stickyGET('mailbox');
		}

		$email_model=$this->add('xepan\communication\Model_Communication_Email'.$mailbox);

		// throw new \Exception($mail);
		$my_email->addExpression('post_email')->set(function($m,$q){
			return $q->getField('email_username');
		});

		// $mail = "management@xavoc.com";

		$or = $email_model->dsql()->orExpr();
		if($mail === "%"){
			$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$or->where($email_model->dsql()->andExpr()
									->where('created_by_id',$this->app->employee->id)
									->where('status','Draft'));
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);
		}else
			$or->where('mailbox','like',$mail.'%');

		$email_model->addCondition($or);
		if($_GET['show_unread_emails']){
			$this->app->stickyGET('show_unread_emails');
			$email_view->memorize('mailbox',$_GET['mailbox']);
			// $email_model->addCondition('extra_info','not like','%'.$this->app->employee->id.'%');
			$email_model->addCondition('is_read',false);
		}

		// $email_model->addCondition('mailbox','like',$mail.'%');
		$email_model->setOrder('created_at','desc');

		$form = $email_view->add('Form',null,'search_form',['form\empty']);
		$search_field = $form->addField('search');
		$search_field->setAttr('placeholder','Search')->set($search_value);

		if($form->isSubmitted()){
			$email_view->js()->reload(['search'=>$form['search']])->execute();
		}

		if($_GET['search']){
			$email_model->addExpression('Relevance')->set('MATCH(title,description,communication_type) AGAINST ("'.$_GET["search"].'")');
			$email_model->addCondition('Relevance','>',0);
 			$email_model->setOrder('Relevance','Desc');
		}

		if(!$email_model->count()->getOne()){
			$email_view->template->trySet('message','No E-mail History Found');
		}

		$mailboxes_view = $this->add('xepan\communication\View_EmailNavigation',null,'email_navigation');
		$mailboxes_view->js(true)->find('[data-mailbox="'.$mailbox.'"]')->closest('li')->addClass('active');
		
		
		$email_view->setModel($email_model);
		$paginator = $email_view->add('xepan\base\Paginator',null,'paginator');
		$paginator->setRowsPerPage(50);
		
		$label_view=$this->add('xepan\communication\View_Lister_EmailLabel',null,'email_labels');
		$label_view->setModel($my_email);
		$label_view->js(true)->find('[data-mail="'.$mail.'"]')->addClass('fa fa-check-square');

		// Populate links with js->on
		$url = $this->api->url(null,['cut_object'=>$email_view->name]);
		
		
		$email_detail=$this->add('xepan\communication\View_EmailDetail',null,'email_detail');
		$mailboxes_view->on('click','a',function($js,$data)use($mailboxes_view,$email_view,$url,$email_detail){
			$email_view->memorize('mailbox',$data['mailbox']);
			$email_view->memorize('filter-contacts',(int)$data['filterContacts']);
			$email_view->memorize('starred',(int) $data['starred']);
			$email_view->memorize('draft',(int) $data['draft']);
			$email_view->memorize('trashed',(int) $data['trashed']);
			$email_view->memorize('sent',(int) $data['sent']);

			return [
					$mailboxes_view->js()->find('li')->removeClass('active'),
					$email_view->js()->reload(null,null,$url),
					$js->closest('li')->addClass('active'),
					$email_detail->js()->hide()

			];

		});

		$mailboxes_view->js('click',
			[
				$email_view->js()->reload(
					[
						'mailbox'=>$this->js()->_selectorThis()->closest('a')->data('mailbox'),
						'show_unread_emails'=>true,

					]
				),
				$mailboxes_view->js()->find('li')->removeClass('active'),
				$mailboxes_view->js()->_selectorThis()->closest('li')->addClass('active'),
				$email_detail->js()->hide()

			]
			)
		->_selector('#email-nav-items li a span.unread-email-view');

		$label_view->on('click','a',function($js,$data)use($label_view,$email_view,$url,$email_detail){
			$email_view->memorize('mail',$data['mail']);
						
			return [
					$label_view->js()->find('.fa.fa-check-square')->removeClass('fa fa-check-square'),
					$email_view->js()->reload(null,null,$url,['mail'=>$data['mail']]),
					$js->addClass('fa fa-check-square'),
					$email_detail->js()->hide()
			];

		});
		

		if($this->app->stickyGET('email_id')){
			$email_model=$this->add('xepan\communication\Model_Communication_Email');
			$email_model->load($_GET['email_id']);
			/*Mark Read When Click to open Email Details*/
			$read_email = $this->add('xepan\base\Model_Contact_CommunicationReadEmail');
			$read_email->addCondition('communication_id',$email_model->id);
			$read_email->addCondition('contact_id',$this->app->employee->id);
			$read_email->addCondition('is_read',true);
			$read_email->tryLoadAny();
			if(!$read_email->loaded()){
				$read_email->save();
			}

			$email_detail->setModel($email_model);
			$this->app->stickyGET('communication_id');
			$email_detail->js('click',$compose_view->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')->reload(['communication_id'=>$email_model->id,'mode'=>'reply_email']))->_selector('.reply');	
			$email_detail->js('click',$compose_view->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')->reload(['communication_id'=>$email_model->id,'mode'=>'reply_email_all']))->_selector('li.reply-all');
			$email_detail->js('click',$compose_view->js()->html('<div style="width:100%"><img style="display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')->reload(['communication_id'=>$email_model->id,'mode'=>'fwd_email']))->_selector('li.forward');				
		}

		$email_view->js('click',
			[
			$email_detail->js()
				->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
				->reload(['email_id'=>$this->js()->_selectorThis()->data('id')]),
			$email_view->js()->_selectorThis()->closest("li")->removeClass("unread")	
			])
		->_selector('li.clickable-row  div:not(.chbox, .star,.checkbox-nice)');
		
		$email_view->js('click',
			[
				$compose_view->js()->show()->_selector('.compose-email-view-popup'),
				$compose_view->js()->html('<div style="width:100%"><img style="width:20%;display:block;margin:auto auto 50%;" src="vendor\xepan\communication\templates\images\email-loader.gif"/></div>')
				->reload(['communication_id'=>$this->js()->_selectorThis()->data('id'),'mode'=>'DraftMessage'])])
		->_selector('li.draft-message  div:not(.chbox, .star,.checkbox-nice)');

		$email_view->js('click',[$email_view->js()->show(),$email_detail->js()->hide()])->_selector('button.back-inbox');
		
		$email_view->on('click','li > .star > a',function($js,$data)use($email_model){
			// load data['id'] wala e,mail and mark starred or remove is_starred
			$email_model->load($data['id']);
			$js_array=[];
			if($data['starred']=='1'){
				$js_array[] = $js->removeClass('starred');
				$js_array[] = $js->data('starred','0');
				$email_model['is_starred']=false;
				$email_model['created_by_id']=$this->app->employee->id;
			}
			else{
				$js_array[] = $js->addClass('starred');
				$js_array[] = $js->data('starred','1');
				$email_model['is_starred']=true;
				$email_model['created_by_id']=$this->app->employee->id;
			}
			$email_model->saveAndUnload();

			$js_array[] = $js->univ()->successMessage("done");

			return $js_array;
		});


		$email_view->js('click',"$(':checkbox').each(function () { this.checked = true; });")->_selector('.all-select');
		$email_view->js('click',"$(':checkbox').each(function () { this.checked = false; });")->_selector('.select-none');
		$email_view->js('click',"$(':checkbox').each(function () { if(!$(this).closest('.clickable-row').hasClass('unread')) this.checked = true; else this.checked = false; });")->_selector('.select-read');
		$email_view->js('click',"$(':checkbox').each(function () { if($(this).closest('.clickable-row').hasClass('unread')) this.checked = true; else this.checked = false; });")->_selector('.select-unread');
		$email_view->js('click',"$(':checkbox').each(function () { if($(this).closest('.clickable-row').find('.starred').length) this.checked = true; else this.checked = false; });")->_selector('.select-starred');
		$email_view->js('click',"$(':checkbox').each(function () { if(!$(this).closest('.clickable-row').find('.starred').length) this.checked = true; else this.checked = false; });")->_selector('.select-unstarred');
		$email_view->js('click','
			var selected_emails=[];
			$("#email-list :checkbox").each(function () { 
				if(this.checked) {
					selected_emails.push($(this).data("id"));
					$(this).closest("li").hide();
				}
			});
			$.ajax("",{data: {delete_emails:selected_emails}});
			')->_selector('.do-delete');

		/*---------------Mark As Read Email------------ */

		$email_view->js('click','
			var selected_mark_emails=[];
			$("#email-list :checkbox").each(function (){
				if(this.checked){
					selected_mark_emails.push($(this).data("id"));
					$(this).closest("li").removeClass("unread");
				}
			});
			$.ajax("",{data: {mark_read: selected_mark_emails}});
			')->_selector('.mark-allread');
		
		/*----------------Mark As UnRead Emails-----------*/

		$email_view->js('click','
			var selected_mark_unread_emails=[];
			$("#email-list :checkbox").each(function (){
				if(this.checked){
					selected_mark_unread_emails.push($(this).data("id"));
					$(this).closest("li").addClass("unread");
				}
			});
			$.ajax("",{data: {mark_unread: selected_mark_unread_emails}});
			')->_selector('.do-unread');

		/*------------Mark As Spam / Junk Emails----------*/

		$email_view->js('click','
			var selected_mark_spam_emails=[];
			$("#email-list :checkbox").each(function () { 
				if(this.checked) {
					selected_mark_spam_emails.push($(this).data("id"));
					$(this).closest("li").hide();
				}
			});
			$.ajax("",{data: {mark_spam_emails:selected_mark_spam_emails}});
			')->_selector('.do-spam');
		

		/*------------Mark As Not Spam / Junk Emails----------*/

		$email_view->js('click','
			var selected_mark_not_spam_emails=[];
			$("#email-list :checkbox").each(function () { 
				if(this.checked) {
					selected_mark_not_spam_emails.push($(this).data("id"));
					$(this).closest("li").hide();
				}
			});
			$.ajax("",{data: {mark_not_spam_emails:selected_mark_not_spam_emails}});
			')->_selector('.do-notspam');


		$email_view->js('click')->_selector('button.fetch-refresh')->redirect($this->app->url(null,['fetch_emails'=>true]));
		
	}
	
	function defaultTemplate(){
		return['page/emails'];
	}
}