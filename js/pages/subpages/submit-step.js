jQuery(document).ready(function() {	
	var wfpath = "" ;
	var stepProcess = "" ; // process of selected step
	function calendar_action(){
		jQuery( "#due-date" ).datepicker();
	}
	
	// When page is colled from post edit page 
	function load_setting(){
		if(jQuery("#hi_editable").val()){
			jQuery("#publishing-action").append("<input type='button' id='step_submit' class='button button-primary button-large'" +
												" value='" + owf_submit_step_vars.signOffButton + "' style='float:left;margin-top:10px;' />" + 
												"<input type='hidden' name='hi_process_info' id='hi_process_info' />").css({"width":"100%"});
		}else{
			jQuery("#publish").hide();
			jQuery("#publishing-action").append("<input type='button' id='step_submit' class='button button-primary button-large' " +
												"style='float:left;margin-top:10px;' value='" + owf_submit_step_vars.signOffButton + "' />");
		}
		jQuery("#publishing-action").append("<a style='float:right;margin-top:10px;' href='admin.php?page=oasiswf-inbox'>" + 
											owf_submit_step_vars.inboxButton + "</a>") ;		

		jQuery('.inline-edit-status').hide() ;
		jQuery('.error').hide() ;
	}
		
	jQuery(".date-clear").click(function(){
		jQuery(this).parent().children(".date_input").val("");
	});
			
	jQuery( document ).on("click", "#submitCancel, .modalCloseImg", function(){
		modal_close();
	});
	
	modal_close = function(){
		wfpath = "";
		stepProcess = "";
		jQuery.modal.close();
		if( jQuery("#hi_parrent_page").val() == "inbox" )
			jQuery(document).find("#step_submit_content").html("") ;
	}
	
	// When page is loaded, this function is processed
	if(jQuery("#hi_parrent_page").val() == "post_edit") {
		load_setting();
	}
	
	jQuery( document ).on("click", "#step_submit", function(){
		jQuery("#new-step-submit-div").modal({
		    onShow: function (dlg) {
		        jQuery(dlg.container).css('height', 'auto');
		        jQuery(dlg.wrap).css('overflow', 'auto'); // or try ;
		        jQuery.modal.update();
		    }	
		});
		wfpath = "";
		stepProcess = "";
		calendar_action();
		return false;
	});	
	
	//-----------function-------------------
	
	
	first_last_step_error = function(path){
		if(path=="failure"){
			var msg = owf_submit_step_vars.firstStepMessage;
			jQuery("#message_div").html(msg).css({"background-color":"#fbd7f0", "border":"1px solid #f989d8"}).show();
			
			jQuery("#cancelSave").show();
			jQuery("#submitSave").hide();			
			jQuery("#completeSave").hide();
			
			jQuery("#sum_step_info").hide() ;
			
		}
		
		if(path=="success"){
			var msg = owf_submit_step_vars.lastStepMessage ;
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
	
	jQuery( document ).on("change", "#decision-select", function(){
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
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function( response ){	
				jQuery("#step-loading-span").removeClass("loading");
				if(response){
					var steps = JSON.parse(response) ;
					if(steps[wfpath]){
						jQuery("#step-select").removeAttr("disabled"); 
						add_option_to_select("step-select", steps[wfpath]);
						var count = 0;
					    for(var steps in steps[wfpath])
					    {
					        count++;
					    }
					    if (count == 1){
					    	jQuery("#step-select").change();
					    }
					}else{					
						first_last_step_error(wfpath);
					}
				}			
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert("error while loading the steps list");
			}			
		});
	});
	
	jQuery( document ).on("change", "#step-select", function(){
		stepProcess = "" ;
		data = {
				action: 'get_users_in_step' ,
				stepid: jQuery(this).val(),
				postid: jQuery("#hi_post_id").val(),
				decision: jQuery("#decision-select").val() 
			   };
		
		jQuery("#actors-list-select").find('option').remove() ;
		jQuery("#actors-set-select").find('option').remove() ;
		jQuery("#actors-list-select").attr("disabled", true);		
		jQuery("#actors-set-select").attr("disabled", true);
		
		jQuery(".assign-loading-span").addClass("loading");
		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data: data,
			success: function( response ){
				if(response=="nodefine" && response=="No dbdata")return;
				jQuery(".assign-loading-span").removeClass("loading");
				
				jQuery("#actor-one-select").removeAttr("disabled");	
				jQuery("#actors-list-select").removeAttr("disabled");	
				jQuery("#actors-set-select").removeAttr("disabled");
				var result={}, users = {} ;
				if(response){
					result = JSON.parse(response) ;
					if( typeof result["users"][0] == 'object') // no users are defined 
					{
						users = result["users"] ;					
					}
					else
					{
						alert(owf_submit_step_vars.noUsersFound);
					}
					stepProcess = result["process"] ;
				}
				// multiple actors applicable to both review and assignment step
				if(stepProcess == "review" || stepProcess == "assignment" || stepProcess == "publish"){
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
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				alert("error while loading the user list");
			}
		});
	});
	//--------------------------------	
	jQuery( document ).on( "click", "#assignee-set-point", function(){
		
		var v = jQuery('#actors-list-select option:selected').val();
		var t = jQuery('#actors-list-select option:selected').text();
		if(option_exist_chk(v)){
			jQuery('#actors-set-select').append('<option value=' + v + '>' + t + '</option>');
		}
		return false;
	});
	
	jQuery( document ).on( "click", "#assignee-unset-point" , function(){
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
	jQuery( document ).on( "click", "#submitSave", function(){
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
				if(jQuery("#hi_parrent_page").val()=="inbox"){					
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
			alert(owf_submit_step_vars.decisionSelectMessage);
			return false;
		}
		
		if(!jQuery("#step-select").val()){
			alert(owf_submit_step_vars.selectStep);
			return false;
		}
		
		/* This is for checking that reminder email checkbox is selected in workflow settings.
		If YES then Due Date is Required Else Not */
		if(owf_submit_step_vars.drdb != "" || owf_submit_step_vars.drda != "")
		{
			if (jQuery("#due-date").val() == '') {
				alert(owf_submit_step_vars.dueDateRequired);
				return false;
			}
			if(!chk_due_date("due-date")){
				return false;
			}
		}
		
		return true;
	}
	
	assign_actor_chk = function(){
		if(jQuery("#one-actors-div").css("display") == "block"){
			if(!jQuery("#actor-one-select").val()){
				alert(owf_submit_step_vars.noAssignedActors) ;
				return false;
			}
			return jQuery("#actor-one-select").val() ;
		}else{
			var optionNum = jQuery("#actors-set-select option").length ;
			if(!optionNum){
				alert(owf_submit_step_vars.noAssignedActors) ;
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
	jQuery( document ).on( "click", "#immediately-chk", function(){
		if(jQuery(this).attr("checked") == "checked"){
			jQuery("#immediately-span").hide() ;
		}else{
			jQuery("#immediately-span").show() ;
		}
	}) ;
	
	jQuery( document ).on( "click", "#completeSave", function(){
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
			if(jQuery("#hi_parrent_page").val()=="inbox"){
				location.reload();
			}
			else {
				modal_close() ;
				jQuery("#publish").click();
			}
		});		
	});	
	
	jQuery(".immediately").keydown(function(){
		
		jQuery(this).css("background-color", "#ffffff");
	});
	
	//--------complate------------
	jQuery( document ).on( "click", "#cancelSave", function(){
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
			if(jQuery("#hi_parrent_page").val()=="inbox"){
				location.reload();
			}
			else {
				modal_close() ;
				location.reload();
			}
		});		
	});	
}) ;
