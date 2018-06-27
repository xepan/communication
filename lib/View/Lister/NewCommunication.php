<?php

namespace xepan\communication;

class View_Lister_NewCommunication extends \CompleteLister{
	public $contact_id;
	
	function init(){
		$this->template->loadTemplatefromString($this->myTemplate());
		parent::init();

		if(!$this->contact_id)
			return;			
		$this->addClass('xepan-communication-lister');
		$this->js('reload')->reload();
		
		// $this->js('click',$this->js()->univ()->frameURL("SEND ALL COMMUNICATION",$this->api->url('xepan_communication_contactcommunications',['contact_id'=>$this->contact_id])))->_selector('.inform');
		// $this->js('click',$this->js()->univ()->dialogURL("Edit  COMMUNICATION",
		// 			[
		// 				$this->api->url($vp->getURL(),['contact_id'=>$this->contact_id]),
		// 					'edit_communication_id'=>$this->js()->_selectorThis()->data('id')
		// 			])
		// )->_selector('.do-view-edit-communication');
		
		/*=========Delete Communication================*/
		if($do_delete_id = $this->app->stickyGET('do_delete_communication_id')){
			$del_model = $this->add('xepan\communication\Model_Communication')
				->addCondition('id',$do_delete_id)
				->tryLoadAny();
			if($del_model->loaded()){
				$del_model->delete();
			}	

			$this->app->page_action_result = $this->js(null,$this->js()->univ()->successMessage('Deleted Successfully'))->_selector('.xepan-communication-lister')->trigger('reload');
		}

		$this->on('click','.do-view-delete-communication')->univ()->confirm('Are you sure?')
			->ajaxec(array(
            	$this->app->url(),
            	'do_delete_communication_id'=>$this->js()->_selectorThis()->data('id')

        ));
	}

	function formatRow(){
		
		$to_mail = json_decode($this->model['to_raw'],true);
		
		$to_lister = $this->app->add('CompleteLister',null,null,['view/communication1','to_lister']);
		$to_lister->setSource($to_mail);
			
		$cc_raw = json_decode($this->model['cc_raw'],true);
		$cc_lister = $this->app->add('CompleteLister',null,null,['view/communication1','cc_lister']);
		$cc_lister->setSource($cc_raw);

		$from_mail = json_decode($this->model['from_raw'],true);
		$from_lister = $this->app->add('CompleteLister',null,null,['view/communication1','from_lister']);
		$from_lister->setSource($from_mail);

		$attach=$this->app->add('CompleteLister',null,null,['view/communication1','Attachments']);
		$attach->setModel('xepan\communication\Communication_Attachment')->addCondition('communication_id',$this->model->id);

		$this->current_row_html['description'] = $this->current_row['description'];
		
		if($this->model['attachment_count'])
			$this->current_row_html['attachment'] = '<span><i style="color:green" class="fa fa-paperclip"></i></span>';
		else
			$this->current_row_html['attachment']='';

		if($this->model['status'] == 'Called')
			$this->current_row_html['to'] = $this->model['to'];
		else
			$this->current_row_html['to']=' ';

		$this->current_row_html['to_lister'] = $to_lister->getHtml();
		if($this->model['communication_type']==='Email'){
			$this->current_row_html['cc_lister'] = $cc_lister->getHtml();
		}else{
			$this->current_row_html['cc_lister'] = "";
		}
		$this->current_row_html['from_lister'] = $from_lister->getHtml();
		$this->current_row_html['Attachments'] = $attach->getHtml();

		if($this->model['communication_type'] == "Personal"){
			$rel_emp = $this->model->getCommunicationRelatedEmployee('employee_name');
			$rel_emp_lister = $this->app->add('CompleteLister',null,null,['view/communication1','communication_related_employee']);
			$rel_emp_lister->setSource($rel_emp);

			$this->current_row_html['communication_related_employee'] = $rel_emp_lister->getHtml();
		}else{
			$this->current_row_html['communication_related_employee'] = "";
		}

		// Communication icon
		$icon = [
			'AbstractMessage'=>'fa fa-info',
			'AbstractMessageIn'=>'fa fa-info',
			'AbstractMessageOut'=>'fa fa-info',
			'Email'=>'glyphicon glyphicon-envelope',
			'EmailIn'=>'glyphicon glyphicon-inbox',
			'EmailOut'=>'fa fa-paper-plane-o',
			'Call'=>'fa fa-phone-square',
			'CallIn'=>'fa fa-phone-square',
			'CallOut'=>'fa fa-phone-square',
			'Comment'=>'fa fa-comments-o',
			'CommentIn'=>'fa fa-comments-o',
			'CommentOut'=>'fa fa-comments-o',
			'Newsletter'=>'fa fa-newspaper-o',
			'NewsletterIn'=>'fa fa-newspaper-o',
			'NewsletterOut'=>'fa fa-newspaper-o',
			'Personal'=>'fa fa-male',
			'PersonalIn'=>'fa fa-male',
			'PersonalOut'=>'fa fa-male',
			'ReminderEmail'=>'fa fa-bell-o',
			'ReminderEmailIn'=>'fa fa-bell-o',
			'ReminderEmailOut'=>'fa fa-bell-o',
			'TeleMarketing'=>'fa fa-info',
			'TeleMarketingIn'=>'fa fa-info',
			'TeleMarketingOut'=>'fa fa-info'
		];
		
		$this->current_row_html['communication_icon'] = $icon[$this->model['communication_type'].$this->model['direction']]?:($this->model['communication_type'].$this->model['direction']);
				
		$this->current_row_html['created_at'] = date('F jS, Y h:i:s',strtotime($this->model['created_at']));
		
		if($this->model['communication_type'] == "Email")
			$this->current_row_html['edit_action'] = "";
		else{
			$clone_region = '<a data-id="'.$this->model->id.'" class="do-view-edit-communication pull-right xepan-communication-action">
								<i class="fa fa-edit">&nbsp;</i>
							</a>';
			$this->current_row_html['edit_action'] = $clone_region;
		}

		return parent::formatRow();
	}

	function myTemplate(){
		$template = '
			<div id="'.$this->name.'" class="xepan-communication-lister {$class}">
			  <div class="timeline">
    			<div class="line text-muted"></div>
			  	{rows}{row}
			  		<article class="panel panel-default accordion">
			  			<div class="panel-heading icon" title="{$communication_type} {$direction}">
            				<i class="{$communication_icon} {$communication_type}"></i>
        				</div>
			  			<div class="panel-heading ">
	                    	<h4 class="panel-title row">
	                    		<a href="#details{$id}" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed" style="">
			                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-bold">
			                          {title}title of communication should come here{/}
			                          {$attachment}
			                        </div>
	                        		<div class="col-md-2 col-lg-2 col-xs-12 col-sm-12">
	                        			<span class="pull-left">{$created_at}</span>
	                        		</div>
	                        		<div class="col-md-2 col-lg-2 col-xs-12 col-sm-12">
	                        			<span class="pull-right">{$communication_type} ({$status} {$to})</span>
	                        		</div>
	                        		<div class="col-md-2 col-lg-2 col-xs-12 col-sm-12">
	                        			{delete_action}
	                        			<a data-id="{$id}" class="do-view-delete-communication pull-right xepan-communication-action">
	                        				<i class="fa fa-trash"> </i>
	                        			</a>
	                        			{/delete_action}
	                        			{edit_action}
	                        			{/edit_action}
	                        		</div>
	                        </a></h4>
	                  </div>
	                <div style="height: 2px;" id="details{$id}" class="panel-collapse collapse">
                    <div class="panel-body">
                      <div class="row">{from_lister}<span class="small">From: </span>{rows}{row} <span class="small">{$name} &nbsp;&nbsp;</span>{/}{/}
                        {/}
                      </div>
                      <div class="row">{to_lister}<span class="small">To:</span>{rows}{row} <span class="small">{$name}{$email}&nbsp;{$number} &nbsp; &nbsp;</span>{/}{/}
                        {/}
                      </div>
                      <div class="row">{cc_lister}<span class="small">CC: </span>{rows}{row} <span class="small">{$name}{$email}&nbsp;{$number} &nbsp;&nbsp;</span>{/}{/}
                        {/}
                      </div>
                      <div class="row">{communication_related_employee}<span class="small">Related Employee: </span>{rows}{row} <span class="small">{$name}&nbsp;</span>{/}{/}
                        {/}
                      </div>
                      <div class="row xepan-push-large">
                        <hr/>
                      </div>
                      <div class="row xepan-push-large">
                           
                        {description}  
                        Description should come here simpley for now
                        {/}
                      </div>
                      <div class="row">
                           
                        {Attachments}
                        {rows}{row}<span>    
                          <div class="img"><a href="{$file}"><img alt="" src="{$file}"/></a></div><span class="name"></span><a href="{$file}" download="download">Download Attachment</a></span>{/}{/}
                        {/}
                      </div>
                    </div>
                  </div>
			  		</article>
			  	{/}{/}
			  </div>
			  {$Paginator}
			</div>
			<style>
			  .accordion .panel-title > a::after{
			    margin-top:0px!important;
			  }  
			  
			  .xepan-communication-lister h4{
			    font-size:13px !important;
			  }
			</style>';

			return $template;
	}
}