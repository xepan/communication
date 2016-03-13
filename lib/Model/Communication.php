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

class Model_Communication extends \xepan\base\Model_Document{

	function init(){
		parent::init();
		
		$comm_j = $this->join('communication.document_id');
		$comm_j->hasOne('xepan\base\Contact','from_id');
		$comm_j->hasOne('xepan\base\Contact','to_id');
		$comm_j->hasOne('xepan\base\Document','related_document_id');
		
		$comm_j->addField('uid');
		$comm_j->addField('uuid');
		$comm_j->addField('reply_to');
		
		$comm_j->addField('from_raw');
		$comm_j->addField('to_raw');
		$comm_j->addField('flags');
		$comm_j->addField('cc_raw');
		$comm_j->addField('bcc_raw');

		$comm_j->addField('title');
		$comm_j->addField('description');

		$comm_j->addField('tags');
		$comm_j->addField('direction');
		$comm_j->addField('communication_type');
		
		$comm_j->addField('mailbox');

		$this->addCondition('type','Communication');
	}
}
