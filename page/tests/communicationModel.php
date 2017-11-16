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

class page_tests_communicationModel extends \xepan\base\Page_Tester {
	public $title='Communication Model Testing';

	public $proper_responses=[
			'test_findContactTo1'=>['Buddy Trade','','','Prime Scan','Indian Krishi Mandi'],
			'test_findContactFrom1'=>['','Prime Scan'],
        ];

	function test_findContactTo1(){
		$result =[];
		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['to_raw']='[{"name":"Buddy Trade","email":"jashwant.k.salvi@gmail.com"},{"name":"Buddy Trade","email":"mediafarm.udaipur@gmail.com"}]';
		$email->findContact('to');
		$result [] = $email->ref('to_id')->get('name');

		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['to_raw']='[]';
		$email->findContact('to');
		$result [] = $email->ref('to_id')->get('name');

		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['to_raw']='[{"name":null,"email":"management@xavoc.com"}]';
		$email->findContact('to');
		$result [] = $email->ref('to_id')->get('name');

		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['to_raw']='[{"name":"Prime Scan","email":"prime_scan@yahoo.com"},{"name":"Gowrav Vishwakarma","email":"gowravvishwakarma@gmail.com"},{"name":"Rakesh Sinha","email":"rksinha.btech@gmail.com"}]';
		$email->findContact('to');
		$result [] = $email->ref('to_id')->get('name');

		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['to_raw']='[{"name":"Indian Krishi Mandi","email":"netplusindia14@gmail.com"}]';
		$email->findContact('to');
		$result [] = $email->ref('to_id')->get('name');

		return $result;

	}


	function test_findContactFrom1(){
		$result = [];

		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['from_raw']='{"name":"Somya Martand","email":"somyamartand26@gmail.com"}';
		$email->findContact('from');
		$result [] = $email->ref('to_id')->get('name');

		$email = $this->add('xepan\communication\Model_Communication_Abstract_Email');
		$email['from_raw']='{"name":"prime_scan","email":"prime_scan@yahoo.com"}';
		$email->findContact('from');
		$result [] = $email->ref('from_id')->get('name');

		return $result;
	}
}
