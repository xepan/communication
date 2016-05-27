<?php 
namespace xepan\communication;
class page_general_countrystate extends \xepan\communication\page_sidebar{
	public $title="Country\ State";

	function init(){
		parent::init();

		$country_model = $this->add('xepan\base\Model_Country');
		$country_model->setOrder('name','asc');

		$crud = $this->add('xepan\hr\CRUD');
		$crud->setModel($country_model);
		$crud->grid->addQuickSearch(['name','iso_code']);
		$crud->grid->addPaginator(50);

		$crud->grid->add('VirtualPage')
			->addColumn('State')
			->set(function($page){
				$country_id = $_GET[$page->short_name.'_id'];
				
				$state_model = $page->add('xepan\base\Model_State')->addCondition('country_id',$country_id);
				$state_model->setOrder('name','asc');

				$crud = $page->add('xepan\hr\CRUD');
				$crud->setModel($state_model);
				$crud->grid->addQuickSearch(['name','abbreviation']);
				$crud->grid->addPaginator(50);

 		});

	
	}
}