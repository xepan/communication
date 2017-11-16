<?php

namespace xepan\communication;

class View_Lister_Attachment extends \CompleteLister{
	function init(){
		parent::init();

	}

	function defaultTemplate(){
		return ['view/emails/attachment'];
	}
}