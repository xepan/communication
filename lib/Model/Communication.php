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

	function quickSearch($app,$search_string,$view){
		$this->addExpression('Relevance')->set('MATCH(title, description, communication_type) AGAINST ("'.$search_string.'" IN NATURAL LANGUAGE MODE)');
		$this->addCondition('Relevance','>',0);
 		$this->setOrder('Relevance','Desc');
 		if($this->count()->getOne()){
 			$cc = $view->add('Completelister',null,null,['grid/quicksearch-communication-grid']);
 			$cc->setModel($this);
    		$cc->addHook('formatRow',function($g){
    			$g->current_row_html['url'] = $this->app->url('xepan_communication_emaildetail',['email_id'=>$g->model->id]);	
     		});	
		}
	}	

	function set_old_communication_info($app,$contact_info){	
		$communication1 = $this->add('xepan\communication\Model_Communication'); 
		$communication2 = $this->add('xepan\communication\Model_Communication');

		$contact_email = $contact_info['value'];
	    $from_communications = $communication1->addCondition('from_raw', 'like', '%'.$contact_email.'%');
		
		foreach ($from_communications as $previous_communication) {
			$previous_communication['from_id']  = $contact_info['contact_id'];
			$previous_communication->save(); 
		}

       $to_communications = $communication2->addCondition(
            $this->dsql()->orExpr()
               ->where('to_raw', 'like', '%'.$contact_email.'%')
               ->where('cc_raw', 'like', '%'.$contact_email.'%')
               ->where('bcc_raw', 'like', '%'.$contact_email.'%')
       );
            
	   foreach ($to_communications as $previous_communication) {
		   if(!$previous_communication['to_id'])
			   $previous_communication['to_id']  = $contact_info['contact_id'];
			   $previous_communication->save(); 
	   }
	}
}
