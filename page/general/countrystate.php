<?php 
namespace xepan\communication;
class page_general_countrystate extends \xepan\communication\page_sidebar{
	public $title="Countries & States";

	function init(){
		parent::init();


			$country_model = $this->add('xepan\base\Model_Country');
			$country_model->setOrder('name','asc');

			if(!$this->api->auth->model->isSuperUser()){
				$this->add('View_Error')->set('Sorry, you are not permitted to handle this section , Ask respective authority / SuperUser');
				return;
			}else{
				$crud = $this->add('xepan\hr\CRUD',null,null,['view/country-state/country']);
				$crud->setModel($country_model);
				$crud->grid->addQuickSearch(['name','iso_code']);
				$crud->grid->addPaginator(50);

				$crud->grid->add('VirtualPage')
					->addColumn('State')
					->set(function($page){
						$country_id = $_GET[$page->short_name.'_id'];
						
						$state_model = $page->add('xepan\base\Model_State')->addCondition('country_id',$country_id);
						$state_model->setOrder('name','asc');

						$crud = $page->add('xepan\hr\CRUD',null,null,['view/country-state/state']);
						$crud->setModel($state_model);
						$crud->grid->addQuickSearch(['name','abbreviation']);
						$crud->grid->addPaginator(50);

		 		});
			}
	}
}