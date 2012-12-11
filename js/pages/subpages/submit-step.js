jQuery(document).ready(function() {	
	var wfpath = "" ;
	var page_action = "" ; //this page is called by which page 
	var stepProcess = "" ; // process of selected step
	function calendar_action(){
		jQuery( "#due-date" ).datepicker();
	}
	
	// When page is colled from post edit page 
	function load_setting(){
		if(jQuery("#hi_editable").val()){
			jQuery("#publishing-action").append("<input type='button' id='step_submit' class='button-primary'" +
												" value='Sign Off' style='float:left;' /><input type='hidden' name='hi_process_info' id='hi_process_info' />").css({"width":"100%"});
		}else{
			jQuery("#publish").hide();
			jQuery("#publishing-action").append("<input type='button' id='step_submit' class='button-primary' value='Sign Off' />");
		}
		jQuery("#publishing-action").append("<div style='width:100%;margin-top:15px;text-align:left;'><a href='admin.php?page=oasiswf-inbox'>Go to Workflow Inbox</a></div>") ;		
		
		jQuery("#step_submit").live('click', function(){
			jQuery("#new-step-submit-div").modal();
			wfpath = "";
			stepProcess = "";
			calendar_action();
			return false;
		});
		
		jQuery('.inline-edit-status').hide() ;
		
		jQuery('.error').hide() ;
	}
		
	jQuery(".date-clear").click(function(){
		jQuery(this).parent().children(".date_input").val("");
	});
			
	jQuery("#submitCancel, .modalCloseImg").live("click", function(){
		modal_close();
	});
	
	modal_close = function(){
		wfpath = "";
		stepProcess = "";
		jQuery.modal.close();
		if( page_action = "inbox" )
			jQuery(document).find("#step_submit_content").html("") ;
	}
	
	page_action = jQuery("#hi_parrent_page").val();
	
	// When page is loaded, this function is processed	
	if(page_action == "post_edit")load_setting();
	
	//-----------function-------------------
	
	
	first_last_step_error = function(path){
		if(path=="failure"){
			var msg = "This is the first step in the workflow.</br> Do you really want to cancel the post/page from the workflow?"
			jQuery("#message_div").html(msg).css({"background-color":"#fbd7f0", "border":"1px solid #f989d8"}).show();
			
			jQuery("#cancelSave").show();
			jQuery("#submitSave").hide();			
			jQuery("#completeSave").hide();
			
			jQuery("#sum_step_info").hide() ;
			
		}
		
		if(path=="success"){
			var msg = "This is the last step in the workflow. Are you sure to complete the workflow?" ;
			jQuery("#message_div").html(msg).css({"background-color":"#dcddfa", "border":"1px solid #b0b4fa"}).show();
			
			jQuery("#submitSave").hide();
			jQuery("#cancelSave").hide();
			jQuery("#comments-div").hide();
			
			jQuery("#immediately-div").show();
			jQuery("#completeSave").show();
			
			jQuery("#sum_step_info").hide() ;
		}
		
		set_position() ;		
	}
	
	set_position = function(){
		jQuery("#simplemodal-container").css("height","auto");
		jQuery("#simplemodal-overlay").css("vertical-align", "middle").hide().show();
		jQuery.modal.setPosition();
	}
	
	action_setting = function(v){
		if(v == "complete"){
			wfpath = "success" ;
		}
		if(v == "unable"){
			wfpath = "failure" ;
		}
		
		jQuery("#message_div").hide().html("");
		
		jQuery("#submitSave").show();
		jQuery("#comments-div").show();
		
		jQuery("#cancelSave").hide();
		jQuery("#completeSave").hide();
		jQuery("#immediately-div").hide();
		
		jQuery("#sum_step_info").show();
		
		jQuery("#step-select").find('option').remove() ;		
		jQuery("#actor-one-select").find('option').remove() ;
		jQuery("#actors-list-select").find('option').remove() ;
		jQuery("#actors-set-select").find('option').remove() ;
		
		jQuery("#step-select").attr("disabled", true); 
		jQuery("#actor-one-select").attr("disabled", true); 
		jQuery("#actors-list-select").attr("disabled", true);		
		jQuery("#actors-set-select").attr("disabled", true);
		
		set_position() ;		
	}
	
	jQuery("#decision-select").change(function(){
		var get_action = "" ;
		var v = jQuery(this).val() ;
		action_setting(v);
		if(!v)return;
		data = {
				action: 'get_pre_next_steps',
				oasiswfId: jQuery("#hi_oasiswf_id").val()	
			   };
		jQuery("#sum_step_info").css("opacity", 1) ;	
		jQuery("#step-loading-span").addClass("loading");
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery("#step-loading-span").removeClass("loading");
			if(response){
				var steps = JSON.parse(response) ;
				if(steps[wfpath]){
					jQuery("#step-select").removeAttr("disabled"); 
					add_option_to_select("step-select", steps[wfpath]);
				}else{					
					first_last_step_error(wfpath);
				}
			}			
		});	
	});
	
	jQuery("#step-select").change(function(){
		stepProcess = "" ;
		data = {
				action: 'get_user_in_step' ,
				stepid: jQuery(this).val(),
			   };
		
		jQuery("#actors-list-select").find('option').remove() ;
		jQuery("#actors-set-select").find('option').remove() ;
		jQuery("#actors-list-select").attr("disabled", true);		
		jQuery("#actors-set-select").attr("disabled", true);
		
		jQuery(".assign-loading-span").addClass("loading");
		jQuery.post(ajaxurl, data, function( response ) {
			if(response=="nodefine" && response=="No dbdata")return;
			jQuery(".assign-loading-span").removeClass("loading");
			
			jQuery("#actor-one-select").removeAttr("disabled");	
			jQuery("#actors-list-select").removeAttr("disabled");	
			jQuery("#actors-set-select").removeAttr("disabled");
			var result={}, users = {} ;
			if(response){
				result = JSON.parse(response) ;
				users = result["users"] ;
				stepProcess = result["process"] ;
			}
			
			if(stepProcess == "review"){
			//if(jQuery("#hi_current_process").val() != "review" && stepProcess == "review"){
				jQuery("#one-actors-div").hide();
				jQuery("#multi-actors-div").show();
				add_option_to_select("actors-list-select", users, 'name', 'ID') ;
			}else{
				//console.log(users);
				jQuery("#multi-actors-div").hide();
				jQuery("#one-actors-div").show();				
				add_option_to_select("actor-one-select", users, 'name', 'ID') ;
			}
		});
	});	
	//--------------------------------	
	jQuery("#assignee-set-point").click(function(){
		
		var v = jQuery('#actors-list-select option:selected').val();
		var t = jQuery('#actors-list-select option:selected').text();
		if(option_exist_chk(v)){
			if(jQuery("#actors-set-select option").length ==1 && stepProcess != "review" ){
				alert("You can select multiple users only on review step.\n Selected step is " + stepProcess + " step.");
				return;
			}
			jQuery('#actors-set-select').append('<option value=' + v + '>' + t + '</option>');
		}
		return false;
	});
	
	jQuery("#assignee-unset-point").click(function(){
		var v = jQuery('#actors-set-select option:selected').val();
		jQuery("#actors-set-select option[value='" + v + "']").remove();
		return false;
	});
	
	var option_exist_chk = function(val){
		if(jQuery('#actors-set-select option[value=' + val + ']').length>0){
			return false;
		}else{
			return true;
		}
	}	
	//-----------save -------------------	
	jQuery("#submitSave").click(function(){
		var obj =this;
		if(!datacheck())return false;
		var actors = assign_actor_chk() ;
		if(!actors)return false;
		data = {
				action: 'submit_post_to_step',
				oasiswf: jQuery("#hi_oasiswf_id").val(),
				hi_step_id: jQuery("#step-select").val(),
				hi_actor_ids: actors,
				post_ID: jQuery("#hi_post_id").val(),
				hi_due_date:jQuery("#due-date").val(),
				hi_comment: jQuery("#comments").val(),
				review_result: jQuery("#decision-select").val(),
				hi_task_user: jQuery("#hi_task_user").val()
			   };
		jQuery(".changed-data-set span").addClass("loading");
		jQuery(this).hide();
		jQuery(document).find("#step_submit").remove();
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery(".changed-data-set span").removeClass("loading");
			if(response){
				if(page_action=="inbox"){					
					location.reload();
				}else{
					jQuery("#hi_process_info").val(jQuery("#hi_oasiswf_id").val()+"@#@"+jQuery("#decision-select").val()) ;
					jQuery("#publish").click();
				}				
			}
			return false;
		});
		
	});
	
	datacheck = function(){
		if(!jQuery("#decision-select").val()){
			alert("Please select an action.");
			return false;
		}
		
		if(!jQuery("#step-select").val()){
			alert("Please select a step.");
			return false;
		}
		
		if(!chk_due_date("due-date")){
			alert("Please enter a due date.");
			return false;
		}
		
		return true;
	}
	
	assign_actor_chk = function(){
		if(jQuery("#one-actors-div").css("display") == "block"){
			if(!jQuery("#actor-one-select").val()){
				alert("No assigned actor.") ;
				return false;
			}
			return jQuery("#actor-one-select").val() ;
		}else{
			var optionNum = jQuery("#actors-set-select option").length ;
			if(!optionNum){
				alert("No assigned actor(s).") ;
				return false;
			}
			var multi_actors = "", i = 1;
			jQuery("#actors-set-select option").each(function(){
				if(i == optionNum)
					multi_actors += jQuery(this).val();
				else
					multi_actors += jQuery(this).val() + "@" ;
				i++;
			});
			if(multi_actors)return multi_actors;
			else return false;
		}
		return false;
	}
	
	//--------complate------------
	jQuery("#immediately-chk").click(function(){
		if(jQuery(this).attr("checked") == "checked"){
			jQuery("#immediately-span").hide() ;
		}else{
			jQuery("#immediately-span").show() ;
		}
	}) ;
	
	jQuery("#completeSave").click(function(){
		var im_date = "" ;
		if(jQuery("#immediately-span").length > 0 && jQuery("#immediately-span").css("display") != "none")
		{
			if(isNaN(jQuery("#im-year").val())){
				jQuery("#im-year").css("background-color", "#fadede") ;
				return ;
			}
			if(isNaN(jQuery("#im-day").val())){
				jQuery("#im-day").css("background-color", "#fadede") ;
				return ;
			}
			if(isNaN(jQuery("#im-hh").val())){
				jQuery("#im-hh").css("background-color", "#fadede") ;
				return ;
			}
			if(isNaN(jQuery("#im-mn").val())){
				jQuery("#im-mn").css("background-color", "#fadede") ;
				return ;
			}
			
			im_date = jQuery("#im-year").val() + "-" + jQuery("#im-mon").val() + "-" + jQuery("#im-day").val() + " " + jQuery("#im-hh").val() + ":" + jQuery("#im-mn").val() + ":00" ;
		}
		
		data = {
				action: 'change_workflow_status_to_complete',
				oasiswf_id: jQuery("#hi_oasiswf_id").val(),
				post_id: jQuery("#hi_post_id").val(),
				immediately: im_date
			   };
		
		jQuery(".changed-data-set span").addClass("loading");
		jQuery(this).hide();
		jQuery.post(ajaxurl, data, function( response ) {
			if(!response)return;
			jQuery(".changed-data-set span").removeClass("loading");			
			jQuery(document).find("#step_submit").remove();
			modal_close() ;	
			if(page_action=="inbox"){
				location.reload();
			}
		});		
	});	
	
	jQuery(".immediately").keydown(function(){
		
		jQuery(this).css("background-color", "#ffffff");
	});
	
	//--------complate------------
	jQuery("#cancelSave").click(function(){
		var obj =this;
		data = {
				action: 'change_workflow_status_to_cancelled',
				oasiswf_id: jQuery("#hi_oasiswf_id").val(),
				post_id: jQuery("#hi_post_id").val(),
				hi_comment: jQuery("#comments").val(),
				review_result: jQuery("#decision-select").val()
			   };
		
		jQuery(".changed-data-set span").addClass("loading");
		jQuery(this).hide();
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery(".changed-data-set span").removeClass("loading");
			jQuery(document).find("#step_submit").remove();
			modal_close() ;	
			if(page_action=="inbox"){
				location.reload();
			}
		});		
	});	
}) ;
