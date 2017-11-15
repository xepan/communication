<?php
namespace xepan\communication;

class Model_Communication_Email_ContactEmail extends \xepan\communication\Model_Communication_Abstract_Email{
	function init(){
		parent::init();
			$this->addCondition('communication_type','Email');
			$this->addCondition(
					$this->dsql()->orExpr()
						->where('from_id','not',null)
						->where('to_id','not',null)
				);
	}
}