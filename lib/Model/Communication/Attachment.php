<?php

/**
* description: Model Documet Attachment
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;


class Model_Communication_Attachment extends \xepan\base\Model_Table{
	
	public $table='communication_attachment';
	public $acl = false;

	function init(){
		parent::init();
		
		$this->hasOne('xepan\communication\Communication','communication_email_id');
		$this->add('filestore\Field_File','file_id');
	}
}