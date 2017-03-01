jQuery.widget("ui.xepan_emails",{
	
	options:{
	},

	_create : function(){
		alert('ram lala');
		this.setupTopBar();
	},

	setupTopBar : function(){

		var self = this;

		// select checkbox
		$('.all-select').click(function() {
	        $(':checkbox').each(function () { this.checked = true; });
	    });
	    $('.select-none').click(function() {
	        $(':checkbox').each(function () { this.checked = false; });
	    });
	    $('.select-read').click(function() {
	        $(':checkbox').each(function () { if(!$(this).closest('.clickable-row').hasClass('unread')) this.checked = true; else this.checked = false; });
	    });
	    $('.select-unread').click(function() {
	        $(':checkbox').each(function () { if($(this).closest('.clickable-row').hasClass('unread')) this.checked = true; else this.checked = false; });
	    });
	    $('.select-starred').click(function() {
	        $(':checkbox').each(function () { if($(this).closest('.clickable-row').find('.starred').length) this.checked = true; else this.checked = false; });
	    });
	    $('.select-unstarred').click(function() {
	    	$(':checkbox').each(function () { if(!$(this).closest('.clickable-row').find('.starred').length) this.checked = true; else this.checked = false; });
	    });

	    // fetch email and reload

	    $('.fetch-refresh').click(function(){
	    	$.ajax({
	    		url: 'index.php?page=xepan_communication_emails_fetchemail',
	    	})
	    	.success(function(result) {
	    		console.log(result);
	    		$(".xepan-communication-email-list").trigger('reload');
	    	})
	    	.error(function() {
	    		console.log("error");
	    		alert('OOPS');
	    	});
	    	
	    });

	    // Delete Seleted Emails In array

	     $('.do-delete').click(function(){
	     	var selected_emails=[];
			$("#email-list :checkbox").each(function () { 
				if(this.checked) {
					selected_emails.push($(this).data("id"));
					$(this).closest("li").hide();
				}
			});

			$.ajax({
				type: 'POST',
	    		url: 'index.php?page=xepan_communication_emails_deleteemail',
				data:{
					delete_emails:selected_emails
					
				}	
	    	})
	    	.success(function(result) {
	    		console.log(result);
	    		$(".xepan-communication-email-list").trigger('reload');
	    	})
	    	.error(function() {
	    		console.log("error");
	    		alert('OOPS');
	    	});

	     }); 

	},
	setupComposePanel : function(){
		alert('setupComposePanel');
	},

	setupNavigationBar : function(){
		alert('setupNavigationBar');
	},

	setupEmailLabelBar : function(){

	},

	setupEmailList : function(){

	},

});