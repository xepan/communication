<?php
namespace xepan\communication;

class View_EmailNavigation extends \View{
	function init(){
		parent::init();
		$contact_inbox=$this->add('xepan\communication\Model_Communication_Email_ContactReceivedEmail');
		$contact_inbox->addCondition('extra_info','not like','%seen_by%');
		$contact_count=$contact_inbox->count()->getOne();
		// throw new \Exception($contact_count, 1);
		$this->template->trySet('contat_inbox_count',$contact_count);
		// $contact_inbox->debug()->count()->getOne();
		$inbox=$this->add('xepan\communication\Model_Communication_Email_Received');
		$inbox->addCondition('extra_info','not like','%seen_by%');
		$inbox_count=$inbox->count()->getOne();
		$this->template->trySet('inbox_count',$inbox_count);

		$sent=$this->add('xepan\communication\Model_Communication_Email_Sent');
		$sent_count=$sent->count()->getOne();
		$this->template->trySet('sent_email_count',$sent_count);
		
		$draft=$this->add('xepan\communication\Model_Communication_Email_Draft');
		$draft_count=$draft->count()->getOne();
		$this->template->trySet('draft_count',$draft_count);
		
		$starred=$this->add('xepan\communication\Model_Communication_Email_Starred');
		$starred_count=$starred->count()->getOne();
		$this->template->trySet('starred_count',$starred_count);
		
		$trashed=$this->add('xepan\communication\Model_Communication_Email_Trashed');
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