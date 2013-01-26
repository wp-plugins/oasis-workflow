jQuery(document).ready(function() {	
	var stepProcess = "";
	//------main function-------------
	function load_setting(){		
		jQuery("#publishing-action").append("<input type='button' id='workflow_submit' class='button button-primary button-large'" +
											" value='Submit to Workflow' style='float:left;' />").css({"width": "100%"});
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
	
	function calendar_action(){
		jQuery("#due-date").attr("readonly", true);
		jQuery("#due-date").datepicker();
	}	
	jQuery(".date-clear").click(function(){
		jQuery(this).parent().children(".date_input").val("");
	});
	
	jQuery("#workflow_submit").live('click', function(){
		jQuery("#new-workflow-submit-div").modal();
		calendar_action();
	});
		
	jQuery("#submitCancel, .modalCloseImg").live("click", function(){
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
				wf_id: jQuery(this).val(),
			   };
		
		jQuery("#step-loading-span").addClass("loading");
		
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery("#step-loading-span").removeClass("loading");
			if(response == "nodefine"){
				alert("All steps are not defined.\n Please check the workflow.");
				jQuery("#workflow-select").val("");
				return;
			}
			if(response == "wrong"){
				alert("The selected workflow is not valid.\n Please check this workflow.");
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
				action: 'get_user_in_step' ,
				stepid: jQuery(this).val(),
			   };	
		
		
		jQuery.post(ajaxurl, data, function( response ) {
			action_setting("step", "after") ;
			
			var result={}, users = {} ;
			if(response){
				result = JSON.parse(response) ;
				users = result["users"] ;
				stepProcess = result["process"] ;
			}

			if(stepProcess == "review"){
				jQuery("#one-actors-div").hide();
				jQuery("#multipule-actors-div").show();
				add_option_to_select("actors-list-select", users, 'name', 'ID') ;	
			}else{
				jQuery("#multipule-actors-div").hide();
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
			if(jQuery("#actors-set-select option").length ==1 && stepProcess != "review" ){
				alert("You can select multiple users only for review step.\n Selected step is " + stepProcess + " step.");
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
	//------------save-------------------
	jQuery("#submitSave").click(function(){	
		if(!jQuery("#workflow-select").val()){
			alert("Please select a workflow.") ;
			return false;
		}
		
		if(!jQuery("#step-select").val()){
			alert("Please select a step.") ;
			return false;
		}
		
		if(jQuery("#step-select").val() == "nodefine"){
			alert("This step is not defined.") ;
			return false;
		}
		
		var actors = assign_actor_chk() ;
		if(!actors)return;
		if(!chk_due_date("due-date")){
			alert("Please enter a due date.");
			return false;
		}
		
		jQuery("#hi_workflow_id").val(jQuery("#workflow-select").val());
		jQuery("#hi_step_id").val(jQuery("#step-select").val());		
		jQuery("#hi_actor_ids").val(actors);
		jQuery("#hi_due_date").val(jQuery("#due-date").val());
		jQuery("#hi_comment").val(jQuery("#comments").val());
		jQuery("#save_action").val("submit_post_to_workflow");	
		
		jQuery("#post").submit();
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
}) ;
