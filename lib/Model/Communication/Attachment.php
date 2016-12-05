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
		
		$this->hasOne('xepan\communication\Communication','communication_id');
		$this->add('xepan\filestore\Field_File','file_id');

		$this->addField('type')->enum(['inline','attach']);

		$this->addExpression('filename')->set(function($m,$q){
			return $m->refSQL('file_id')->fieldQuery('original_filename');
		});

		$this->addHook('beforeDelete',[$this,'deleteFiles']);
	}

	function deleteFiles(){
		$file = $this->add('xepan\filestore\Model_File');
		$file->addCondition('id',$this['file_id']);
		$file->tryLoadAny();
		if($file->loaded()){
			$this->ref('file_id')->delete();
		}
	}
}
