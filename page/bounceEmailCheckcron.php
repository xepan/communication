<?php

/**
* description: ATK Page
* 
* @author : Gowrav Vishwakarma
* @email : gowravvishwakarma@gmail.com, info@xavoc.com
* @website : http://xepan.org
* 
*/

namespace xepan\communication;

class page_bounceEmailCheckcron extends \Page {
	public $title='Page Title';

	function init(){
		parent::init();
		
		$this->add('xepan\communication\Controller_BounceEmailCheck');

	}
}
