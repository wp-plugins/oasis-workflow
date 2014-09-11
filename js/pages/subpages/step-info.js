jQuery(document).ready(function() {	
	var step_dbid = "" ;
	var befor_focus = "" ;
	//-----------tab control--------------
	jQuery( document ).on("mouseover", "#step-setting a", function(){
		jQuery(this).css({"color":"red"});
	});
	jQuery( document ).on("mouseout", "#step-setting a", function(){
		jQuery(this).css({"color":"blue"});
	});

	jQuery( document ).on( "click", "#more-show", function(){
		if(jQuery("#wf-email-define").css("display")=="block"){
			jQuery("#wf-email-define").hide();
			jQuery(this).css({'backgroundPosition': '105px 0px'});			
		}else{
			jQuery("#wf-email-define").show();			
			jQuery(this).css({'backgroundPosition': '105px -35px'});
		}
		return false;
	});	
	
	jQuery( document ).on( "click", "#step-assignee-set-point", function(){
		var v = jQuery('#step-role-list option:selected').val();
		var t = jQuery('#step-role-list option:selected').text();
		if(option_exist_chk(v))
			jQuery('#step-role-set-list').append('<option value=' + v + '>' + t + '</option>');
	});
	
	jQuery( document ).on( "click", "#step-assignee-unset-point", function(){
		var v = jQuery('#step-role-set-list option:selected').val();
		jQuery("#step-role-set-list option[value='" + v + "']").remove();
	});
	
	jQuery( document ).on( "click", "#stepCancel", function(){
		jQuery.modal.close(); 
	});
	
	jQuery( document ).on( "click", "#stepSave", function(){
		if(!check_save_data())return;		
		save_step_data();
		var step_gpid = jQuery("#step_gpid-hi").val() ;
		jQuery(document).find("#" + step_gpid + " label").html(jQuery("#step-name").val()) ;
		if(jQuery("#first_step_check").attr("checked")){
			jQuery(document).find("#" + step_gpid).attr("first_step", "yes") ;
			jQuery(document).find("#" + step_gpid).css("background-color", "#99CCFF");
			jQuery(document).find("#" + step_gpid).children("label").css("color", "#000");			
		}else{
			jQuery(document).find("#" + step_gpid).attr("first_step", "no") ;
			jQuery(document).find("#" + step_gpid).css("background-color", "#FFFFFF");
			jQuery(document).find("#" + step_gpid).children("label").css("color", "#444444");			
		}
		jQuery.modal.close();
		
	});
	//--------placeholders-------------------
	// assignment subject
	jQuery( document ).on( "click", "#addPlaceholderAssignmentSubj", function(){
		if (jQuery(this).parent().children("select").val() == '') {
			alert(owf_workflow_step_info_vars.selectPlaceholder);
			return false;
		}
		var v = jQuery(this).parent().children("select").val() + " "; //add a space after the placeholder
		jQuery("#assignment-email-subject").insertAtCaret(v);
	});
	
	// assignment message
	jQuery( document ).on( "click", "#addPlaceholderAssignmentMsg", function(){
		if (jQuery(this).parent().children("select").val() == '') {
			alert(owf_workflow_step_info_vars.selectPlaceholder);
			return false;
		}		
		var v = jQuery(this).parent().children("select").val() + " "; //add a space after the placeholder
		setCurrentWhizzy('assignment-email-content');
		insHTML(v);
		jQuery("#addPlaceholderAssignmentMsg").focus();
	});	
	
	// reminder subject
	jQuery( document ).on( "click", "#addPlaceholderReminderSubj", function(){
		if (jQuery(this).parent().children("select").val() == '') {
			alert(owf_workflow_step_info_vars.selectPlaceholder);
			return false;
		}
		var v = jQuery(this).parent().children("select").val() + " "; //add a space after the placeholder
		jQuery("#reminder-email-subject").insertAtCaret(v);
	});
	
	// reminder message
	jQuery( document ).on( "click", "#addPlaceholderReminderMsg", function(){
		if (jQuery(this).parent().children("select").val() == '') {
			alert(owf_workflow_step_info_vars.selectPlaceholder);
			return false;
		}		
		var v = jQuery(this).parent().children("select").val() + " "; //add a space after the placeholder
		setCurrentWhizzy('reminder-email-content');
		insHTML(v);
		jQuery("#addPlaceholderReminderMsg").focus();
	});
	
	jQuery.fn.extend({
		insertAtCaret: function(myValue){
		  return this.each(function(i) {
		    if (document.selection) {
		      //For browsers like Internet Explorer
		      this.focus();
		      sel = document.selection.createRange();
		      sel.text = myValue;
		      this.focus();
		    }
		    else if (this.selectionStart || this.selectionStart == '0') {
		      //For browsers like Firefox and Webkit based
		      var startPos = this.selectionStart;
		      var endPos = this.selectionEnd;
		      var scrollTop = this.scrollTop;
		      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
		      this.focus();
		      this.selectionStart = startPos + myValue.length;
		      this.selectionEnd = startPos + myValue.length;
		      this.scrollTop = scrollTop;
		    } else {
		      this.value += myValue;
		      this.focus();
		    }
		  })
		}
	});
		
	//--------------functions--------------------
	
	var option_exist_chk = function(val){
		if(jQuery('#step-role-set-list option[value=' + val + ']').length>0){
			return false;
		}else{
			return true;
		}
	}
	
	var popup_remove = function(){
		parent.tb_remove();
	}	
	
	var check_save_data = function(){
		
		if(!jQuery("#step-name").val()){
			alert(owf_workflow_step_info_vars.stepNameRequired);
			return false;
		}
		
		var chk = true;
		var step_gpid = jQuery("#step_gpid-hi").val() ;
		jQuery(document).find(".fc_action .w").each(function(){
			var lbl = jQuery(this).children("label").html() ;
			if(jQuery(this).attr("id") != step_gpid){
				var step_name = jQuery("#step-name").val();
				if(jQuery.trim(lbl) == jQuery.trim(step_name))chk=false;
			}		
		}) ;
			
		if(!chk){
			alert(owf_workflow_step_info_vars.stepNameAlreadyExists) ;
			return false ;
		}
		
		var optionNum = jQuery("#step-role-set-list option").length ;
		if(!optionNum){
			alert(owf_workflow_step_info_vars.selectAssignees) ;
			return false ;
		}
		
		if(!jQuery("#step-status-select").val() && !jQuery("#step_status_publish").is(':visible')){
			alert(owf_workflow_step_info_vars.statusOnSuccess) ;
			return false ;
		}
		
		if(!jQuery("#step-failure-status-select").val()){
			alert(owf_workflow_step_info_vars.statusOnFailure) ;
			return false ;
		}
		
		return true;
	}
	
	var get_step_data = function(){
		var stepinfo = {} ;
		var processinfo = {} ;
		var assignee = {} ;
		//-------step info--------
		var step_gpid = jQuery("#step_gpid-hi").val() ;
		stepinfo["process"] = jQuery(document).find("#" + step_gpid ).attr("process-name") ;
		stepinfo["step_name"] = jQuery("#step-name").val() ;
		jQuery('select#step-role-set-list').find('option').each(function() {
			assignee[jQuery(this).val()] = jQuery(this).text() ;
		});
		stepinfo["assignee"] = assignee ;
		if (jQuery("#step_status_publish").is(':visible'))
		{
			stepinfo["status"] = jQuery("#step_status_publish").html() ;
		}
		else
		{
			stepinfo["status"] = jQuery("#step-status-select").val() ;
		}
		stepinfo["failure_status"] = jQuery("#step-failure-status-select").val() ;
		//stepinfo["decision"] = jQuery("input[name=review-opt]:checked").val();
		//-------process info--------
		syncTextarea();
		processinfo["assign_subject"] = jQuery("#assignment-email-subject").val() ;
		processinfo["assign_content"] = jQuery("#assignment-email-content").val() ;
		processinfo["reminder_subject"] = jQuery("#reminder-email-subject").val() ;
		processinfo["reminder_content"] = jQuery("#reminder-email-content").val() ;
				
		step_info = jQuery.toJSON(stepinfo) ;
		process_info = jQuery.toJSON(processinfo) ;
		var saveData = new Array( step_info, process_info ) ;
		return saveData;
	}
	
	var save_step_data = function(){
		var savedata = new Array() ;
		var step_gpid = jQuery("#step_gpid-hi").val() ;
		savedata = get_step_data();
		jQuery(".step-set span").addClass("loading");
		data = {
				action: 'step_save' ,
				wfid: jQuery(document).find("#wf_id").val(), 
				stepgpid: jQuery(document).find( "#" + step_gpid ).attr("id"),
				stepid: jQuery(document).find( "#" + step_gpid ).attr("db-id"),
				stepname: jQuery("#step-name").val(), 
				act: jQuery(document).find( "#" + step_gpid ).attr("real"),
				stepinfo : savedata[0],
				processinfo : savedata[1]
			   };
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery(".step-set span").removeClass("loading");
			jQuery(document).find("#" + step_gpid ).attr({"db-id": response}) ;			
		});
	}
});