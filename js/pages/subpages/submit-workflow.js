jQuery(document).ready(function() {	
	var stepProcess = "";
	//------main function-------------
	function load_setting(){		
		var allowed_post_types = jQuery.parseJSON(owf_submit_workflow_vars.allowedPostTypes);
		var current_post_type = jQuery('#post_type').val();
		// If not allowed post/page type then do not show
		if(jQuery.inArray(current_post_type, allowed_post_types) != -1)
		{		
			jQuery("#publishing-action").append("<input type='button' id='workflow_submit' class='button button-primary button-large'" +
												" value='" + owf_submit_workflow_vars.submitToWorkflowButton + "' style='float:left;clear:both;' />").css({"width": "100%"});
			jQuery("#post").append(
								"<input type='hidden' id='hi_workflow_id' name='hi_workflow_id' />" +
								"<input type='hidden' id='hi_step_id' name='hi_step_id' />" +
								"<input type='hidden' id='hi_actor_ids' name='hi_actor_ids' />" +
								"<input type='hidden' id='hi_due_date' name='hi_due_date' />" +
								"<input type='hidden' id='hi_comment' name='hi_comment' />" +
								"<input type='hidden' id='save_action' name='save_action' />"
							);
			jQuery("#publishing-action").css({"margin-top": "10px"}) ;	
			
			jQuery('.inline-edit-status').hide() ;
			
			jQuery('.error').hide() ;
		}
	}
	
	function calendar_action(){
		jQuery("#due-date").attr("readonly", true);
		jQuery("#due-date").datepicker({ 
			autoSize: true,
			dateFormat: owf_submit_workflow_vars.dateFormat
		});
		
		if (jQuery('.misc-pub-curtime #timestamp b').html() !== 'immediately') {// future publish date
			// get the user set date/time
			var user_set_publish_date_arr = jQuery('.misc-pub-curtime #timestamp b').html().split('@');

			// date
			var parsedDate = '';
			if (user_set_publish_date_arr[0].trim().indexOf("-") > -1) {
				parsedDate = jQuery.datepicker.parseDate('mm-M dd, yy', user_set_publish_date_arr[0].trim());
				
			} else {
				parsedDate = jQuery.datepicker.parseDate('dd M yy', user_set_publish_date_arr[0].trim());
			}
			
			var publish_date_mm_dd_yyyy = jQuery.datepicker.formatDate(owf_submit_workflow_vars.dateFormat, parsedDate);
			jQuery("#publish-date").val(publish_date_mm_dd_yyyy);
			
			// time
			var user_set_publish_time_arr = user_set_publish_date_arr[1].split(":");
			jQuery("#publish-hour").val(user_set_publish_time_arr[0]);
			jQuery("#publish-min").val(user_set_publish_time_arr[1]);
			
			
		}
		
		// add jquery datepicker functionality to publish textbox
		jQuery("#publish-date").attr("readonly", true);
		jQuery("#publish-date").datepicker({
			autoSize: true,
			dateFormat: owf_submit_workflow_vars.dateFormat
		});	
		
	}	
	jQuery(".date-clear").click(function(){
		jQuery(this).parent().children(".date_input").val("");
	});
	
	jQuery( document ).on( "click", "#workflow_submit", function(){
		jQuery("#new-workflow-submit-div").owfmodal();
		calendar_action();
	});
		
	jQuery( document ).on( "click", "#submitCancel, .modalCloseImg", function(){
		modal_close();
	});
	
	modal_close = function(){
		stepProcess = "" ;
		jQuery.modal.close();
	}
	
	load_setting();
	
	//----------setting when selecte------------------
	action_setting = function(inx, frm){		
		if(inx == "wf"){
			if(frm == "pre"){
				stepProcess = "";
				jQuery("#step-select").find('option').remove() ;
				jQuery("#actor-one-select").find('option').remove() ;
				jQuery("#actors-list-select").find('option').remove();
				jQuery("#actors-set-select").find('option').remove();
				
				jQuery("#actor-one-select").attr("disabled", true);
				jQuery("#actors-list-select").attr("disabled", true);
				jQuery("#actors-set-select").attr("disabled", true);
				
				jQuery("#step-loading-span").addClass("loading");
			}
		}
		if(inx == "step"){
			stepProcess = "";
			if(frm == "pre"){
				jQuery("#actor-one-select").find('option').remove() ;
				jQuery("#actors-list-select").find('option').remove() ;
				jQuery("#actors-set-select").find('option').remove() ;
				
				jQuery("#actor-one-select").attr("disabled", true);
				jQuery("#actors-list-select").attr("disabled", true);
				jQuery("#actors-set-select").attr("disabled", true);
				
				jQuery(".assign-loading-span").addClass("loading");
			}else{
				jQuery(".assign-loading-span").removeClass("loading");
				
				jQuery("#actor-one-select").removeAttr("disabled");
				jQuery("#actors-list-select").removeAttr("disabled");
				jQuery("#actors-set-select").removeAttr("disabled");
			}
		}
	}
	
	//-------select function------------
	jQuery("#workflow-select").change(function(){
		action_setting("wf", "pre") ;
		if(!jQuery(this).val()){
			jQuery("#step-loading-span").removeClass("loading");
			return;
		}
		
		data = {
				action: 'get_first_step_in_wf' ,
				wf_id: jQuery(this).val()
			   };
		
		jQuery("#step-loading-span").addClass("loading");
		
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery("#step-loading-span").removeClass("loading");
			if(response == "nodefine"){
				alert(owf_submit_workflow_vars.allStepsNotDefined);
				jQuery("#workflow-select").val("");
				return;
			}
			if(response == "wrong"){
				alert(owf_submit_workflow_vars.notValidWorkflow);
				jQuery("#workflow-select").val("");
				return;
			}			
			var stepinfo = {} ;			
			if(response){
				stepinfo = JSON.parse(response) ;
				jQuery("#step-select").find('option').remove();				
				jQuery("#step-select").append("<option value='" + stepinfo["first"][0][0] + "'>" + stepinfo["first"][0][1] + "</option>") ;
				jQuery("#step-select").change();
			}
		});
	});
	
	jQuery("#step-select").change(function(){
		action_setting("step", "pre") ;
		data = {
				action: 'get_users_in_step' ,
				stepid: jQuery(this).val(),
				decision: 'complete' // it will always be success on submit 
			   };	
		
		
		jQuery.post(ajaxurl, data, function( response ) {
			action_setting("step", "after") ;
			
			var result={}, users = {} ;
			if(response){
				result = JSON.parse(response) ;
				if( typeof result["users"][0] == 'object') 
				{
					users = result["users"] ;					
				}
				else
				{
					alert(owf_submit_workflow_vars.noUsersDefined); // no users are defined 
				}
				stepProcess = result["process"] ;
			}
			// multiple actors applicable to both review and assignment step
			if(stepProcess == "review" || stepProcess == "assignment" || stepProcess == "publish"){
				jQuery("#one-actors-div").hide();
				jQuery("#multiple-actors-div").show();
				add_option_to_select("actors-list-select", users, 'name', 'ID') ;	
				
				// If there is only one assignee ound then directly select as asigned user
				if(numKeys(users)==1) {
					var v = jQuery('#actors-list-select option:selected').val();
					var t = jQuery('#actors-list-select option:selected').text();
					if(option_exist_chk(v)){
						if(jQuery("#actors-set-select option").length == 1){
							alert(owf_submit_workflow_vars.multipleUsers + " " + stepProcess + " " + owf_submit_workflow_vars.step);
							return;
						}
						jQuery('#actors-set-select').append('<option value=' + v + '>' + t + '</option>');
					}
					return false;
				}
			}else{
				jQuery("#multiple-actors-div").hide();
				jQuery("#one-actors-div").show();
				add_option_to_select("actor-one-select", users, 'name', 'ID') ;	
				
			}
					
		});
	});
	
	//---- point function -------
	jQuery("#assignee-set-point").click(function(){
		
		var v = jQuery('#actors-list-select option:selected').val();
		var t = jQuery('#actors-list-select option:selected').text();
		if(option_exist_chk(v)){
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
	//------------save-------------------
	jQuery("#submitSave").click(function(){	
		if(!jQuery("#workflow-select").val()){
			alert(owf_submit_workflow_vars.selectWorkflow) ;
			return false;
		}
		
		if(!jQuery("#step-select").val()){
			alert(owf_submit_workflow_vars.selectStep) ;
			return false;
		}
		
		if(jQuery("#step-select").val() == "nodefine"){
			alert(owf_submit_workflow_vars.stepNotDefined) ;
			return false;
		}
		
		/* 
		* get publish date value, if not null set as post publish date
		* if publish date is not set then proceess default.
		* Immediate post publish date
		*/
		if (jQuery("#publish-date").val() != '') 
		{
			var publish = jQuery('#publish-date').val();
			var parsedDate = jQuery.datepicker.parseDate(owf_submit_workflow_vars.dateFormat, publish);
			//split into array
			var publish_date_mm_dd_yyyy = jQuery.datepicker.formatDate('mm/dd/yy', parsedDate);
			var pdate = publish_date_mm_dd_yyyy.split('/');
			
			//set this mm/dd/yyyy value as wordpress publish date
			jQuery('#mm').val(pdate[0]);
			jQuery('#jj').val(pdate[1]);
			jQuery('#aa').val(pdate[2]);

			if(jQuery('#publish-hour').val() == '')
			{
				jQuery('#hh').val('12');
			}
			else
			{
				jQuery('#hh').val(parseInt(jQuery('#publish-hour').val(), 10));
			}	
			
			if(jQuery('#publish-min').val() == '')
			{
				jQuery('#mn').val('00');
			}
			else
			{			
				jQuery('#mn').val(parseInt(jQuery('#publish-min').val(), 10));
			}
						
		}		
		
		var actors = assign_actor_chk() ;
		if(!actors)return;
		/* This is for checking that reminder email checkbox is selected in workflow settings.
		If YES then Due Date is Required Else Not */
		if(owf_submit_workflow_vars.drdb != "" || owf_submit_workflow_vars.drda != "")
		{
			if (jQuery("#due-date").val() == '') {
				alert(owf_submit_workflow_vars.dueDateRequired);
				return false;
			}
			if(!chk_due_date("due-date", owf_submit_workflow_vars.dateFormat)){
				return false;
			}
		}		
		jQuery("#hi_workflow_id").val(jQuery("#workflow-select").val());
		jQuery("#hi_step_id").val(jQuery("#step-select").val());		
		jQuery("#hi_actor_ids").val(actors);
		jQuery("#hi_due_date").val(jQuery("#due-date").val());
		jQuery("#hi_comment").val(jQuery("#comments").val());
		jQuery("#save_action").val("submit_post_to_workflow");	
		
		jQuery("#save-post").click();
		modal_close();		
		return;		
	});
	
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
				alert(owf_submit_workflow_vars.noAssignedActors) ;
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
}) ;
