<?php
namespace xepan\communication;

class View_EmailNavigation extends \View{
	function init(){
		parent::init();
		
		$this->js('reload')->reload();
		$my_email=$this->add('xepan\hr\Model_Post_Email_MyEmails');
		$my_email->addExpression('post_email')->set(function($m,$q){
			return $q->getField('email_username');
		});



		$contact_inbox=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');
		$or = $contact_inbox->dsql()->orExpr();
		$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);		
		
		
		$contact_inbox->addCondition($or);
		$contact_inbox->addCondition('status','Received');
		$contact_inbox->addCondition('is_read',false);
		$contact_count=$contact_inbox->count()->getOne();
		$this->template->trySet('contat_inbox_count',$contact_count);

		// $contact_inbox->debug()->count()->getOne();
		$inbox=$this->add('xepan\communication\Model_Communication_Email_Received');
		$or = $inbox->dsql()->orExpr();
		$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);		
		
		
		$inbox->addCondition($or);
		$inbox->addCondition('is_read',false);
		$inbox_count=$inbox->count()->getOne();
		$this->template->trySet('inbox_count',$inbox_count);

		$sent=$this->add('xepan\communication\Model_Communication_Email_Sent');
		$or = $sent->dsql()->orExpr();
		$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);		
		
		
		$sent->addCondition($or);
		// $sent->addCondition('created_by_id',$this->app->employee->id);
		$sent_count=$sent->count()->getOne();
		$this->template->trySet('sent_email_count',$sent_count);
		
		$draft=$this->add('xepan\communication\Model_Communication_Email_Draft');
		
		$draft->addCondition('created_by_id',$this->app->employee->id);
		$draft_count=$draft->count()->getOne();
		$this->template->trySet('draft_count',$draft_count);
		
		$starred=$this->add('xepan\communication\Model_Communication_Email_Starred');
		$or = $starred->dsql()->orExpr();
		$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);		
		
		
		$starred->addCondition($or);
		$starred->addCondition('created_by_id',$this->app->employee->id);
		$starred_count=$starred->count()->getOne();
		$this->template->trySet('starred_count',$starred_count);
		
		$junk=$this->add('xepan\communication\Model_Communication_Email_Junk');
		$or = $junk->dsql()->orExpr();
		$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);		
		
		
		$junk->addCondition($or);
		$junk_count=$junk->count()->getOne();
		$this->template->trySet('junk_count',$junk_count);


		$trashed=$this->add('xepan\communication\Model_Communication_Email_Trashed');
		$or = $trashed->dsql()->orExpr();
		$i=0;
			foreach ($my_email as $email) {
				$or->where('mailbox','like',$email['post_email'].'%');
				$i++;
			}
			if($i == 0) $or->where('mailbox',-1);		
		
		
		$trashed->addCondition($or);
		$trashed_count=$trashed->count()->getOne();
		$this->template->trySet('trash_count',$trashed_count);


	}
	function getJSID(){
		return 'email-nav-items';
	}

	function defaultTemplate(){
		return ['view/emails/navigation'];
	}
}