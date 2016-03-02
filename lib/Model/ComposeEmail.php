// <?php

// namespace xepan\communication;

class Model_ComposeEmail extends \xepan\base\Model_Document{
	function init(){
		parent::init();
		$st_j=$this->join('composeemail.contact_id');
		// $st_j->addField('customer');
		// $st_j->addField('name');

	}
 }
