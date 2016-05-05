<?php

/**
* description: ATK Model
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;

class Model_Communication extends \xepan\base\Model_Table{
	public $table="communication";
	public $acl=false;
	function init(){
		parent::init();
		
		$this->hasOne('xepan\base\Contact','from_id');
		$this->hasOne('xepan\base\Contact','to_id');
		$this->hasOne('xepan\base\Document','related_document_id');
		
		$this->addField('uid');
		$this->addField('uuid');
		$this->addField('reply_to');
		
		$this->addField('from_raw')->defaultValue([]);
		$this->addField('to_raw')->defaultValue([]);
		$this->addField('flags');
		$this->addField('cc_raw')->defaultValue([]);
		$this->addField('bcc_raw')->defaultValue([]);

		$this->addField('title');
		$this->addField('description')->type('text');

		$this->addField('tags');
		$this->addField('direction');
		$this->addField('communication_type');

		$this->addField('related_id'); // Can be used anywhere as per requirement
		$this->addField('sent_on')->type('date'); // Can be used anywhere as per requirement
		
		$this->addField('created_at')->type('datetime')->defaultValue($this->app->now);
		$this->addField('status');
		$this->addField('mailbox');
		$this->addField('is_starred')->type('boolean')->defaultValue(false);

		$this->addField('detailed')->type('boolean')->defaultValue(false);
		$this->addField('extra_info');
		$this->hasMany('xepan\communication\Communication_Attachment','communication_id',null,'EmailAttachments');
		$this->hasMany('xepan\crm\Ticket_Comments','communication_id',null,'Comments');
		$this->hasMany('xepan\crm\SupportTicket','communication_id',null,'SupportTicket');
		
		$this->addExpression('image')->set($this->refSQL('from_id')->fieldQuery('image'));
		$this->addExpression('attachment_count')->set($this->refSQL('EmailAttachments')->count());

		$this->addHook('beforeDelete',[$this,'deleteAttachments']);
	}

	function deleteAttachments(){
		$this->ref('EmailAttachments')->each(function($o){
			$o->delete();
		});
	}
}
