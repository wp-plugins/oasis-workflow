jQuery(document).ready(function() {	
	var step_gpid = "" ;
	var step_dbid = "" ;
	var befor_focus = "" ;
	//-----------tab control--------------
	jQuery("#step-setting a").mouseover(function(){
		jQuery(this).css({"color":"red"});
	});
	jQuery("#step-setting a").mouseout(function(){
		jQuery(this).css({"color":"blue"});
	});

	jQuery("#more-show").click(function(){
		if(jQuery("#wf-email-define").css("display")=="block"){
			jQuery("#wf-email-define").hide();
			jQuery(this).css({'backgroundPosition': '105px 0px'});			
		}else{
			jQuery("#wf-email-define").show();			
			jQuery(this).css({'backgroundPosition': '105px -35px'});
		}
		return false;
	});	
	
	jQuery("#step-assignee-set-point").click(function(){
		var v = jQuery('#step-role-list option:selected').val();
		var t = jQuery('#step-role-list option:selected').text();
		if(option_exist_chk(v))
			jQuery('#step-role-set-list').append('<option value=' + v + '>' + t + '</option>');
	});
	
	jQuery("#step-assignee-unset-point").click(function(){
		var v = jQuery('#step-role-set-list option:selected').val();
		jQuery("#step-role-set-list option[value='" + v + "']").remove();
	});
	
	jQuery("#stepCancel").click(function(){
		popup_remove();
	});
	
	jQuery("#stepSave").click(function(){
		if(!check_save_data())return;		
		save_step_data();
		jQuery(document).find("#" + step_gpid + " label").html(jQuery("#step-name").val()) ;
		if(jQuery("#first_step_check").attr("checked")){
			jQuery(document).find("#" + step_gpid).attr("first_step", "yes") ;
		}else{
			jQuery(document).find("#" + step_gpid).attr("first_step", "no") ;
		}
		popup_remove() ;
		
	});
	//--------placeholders-------------------
	currently_focus_even  = function(v){
		befor_focus = v ;
	}
	
	jQuery("#assignment-email-subject, #reminder-email-subject").click(function(){
		befor_focus = jQuery(this).attr("id");
	});
		
	jQuery(".placeholder-add-bt").click(function(){
		var v = jQuery(this).parent().children("select").val();
		if(!v) v = "" ;
		if(befor_focus == "assignment-email-subject" || befor_focus == "reminder-email-subject" ){
			jQuery("#"+befor_focus).insertAtCaret(v);
		}
		
		if(befor_focus == "assignment-email-content" || befor_focus == "reminder-email-content" ){
			c(befor_focus);
			insHTML(v);	
		}
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
		
	//--------------function--------------------
	
	var first_setting = function(){
		//----function setting------
		step_gpid = jQuery("#step_gpid-hi").val() ;
		var lbl = jQuery(document).find( "#" + step_gpid + " label" ).html() ;
		jQuery("#step-name").val(lbl) ;
		var process_name = jQuery(document).find( "#" + step_gpid ).attr("process-name") ;
		if(process_name != "review"){
			jQuery("#step-setting-content").css("height","420px");
		}else{
			jQuery("#step-setting-content").css("height","420px");
		}
		//jQuery(".step-review").hide();	
		//setting_db_step_data();
		if(jQuery("#" + step_gpid).attr("first_step") == "yes")jQuery("#first_step_check").attr("checked", true);
	}
	
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
			alert("Step name is required.");
			return false;
		}
		
		var chk = true;
		jQuery(document).find(".fc_action .w").each(function(){
			var lbl = jQuery(this).children("label").html() ;
			if(jQuery(this).attr("id") != step_gpid){
				var step_name = jQuery("#step-name").val();
				if(jQuery.trim(lbl) == jQuery.trim(step_name))chk=false;
			}		
		}) ;
			
		if(!chk){
			alert("Step name already exists. Please use a different step name.") ;
			return false ;
		}
		
		var optionNum = jQuery("#step-role-set-list option").length ;
		if(!optionNum){
			alert("Please select assignee(s).") ;
			return false ;
		}
		
		if(!jQuery("#step-status-select").val()){
			alert("Please select status on success.") ;
			return false ;
		}
		
		if(!jQuery("#step-failure-status-select").val()){
			alert("Please select status on failure.") ;
			return false ;
		}
		
		return true;
	}
	
	var get_step_data = function(){
		var stepinfo = {} ;
		var processinfo = {} ;
		var assignee = {} ;
		//-------step info--------
		stepinfo["process"] = jQuery(document).find("#" + step_gpid ).attr("process-name") ;
		stepinfo["step_name"] = jQuery("#step-name").val() ;
		jQuery('select#step-role-set-list').find('option').each(function() {
			assignee[jQuery(this).val()] = jQuery(this).text() ;
		});
		stepinfo["assignee"] = assignee ;
		stepinfo["status"] = jQuery("#step-status-select").val() ;
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
				processinfo : savedata[1],
			   };
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery(".step-set span").removeClass("loading");
			jQuery(document).find("#" + step_gpid ).attr({"db-id": response}) ;			
		});
	}
	
	//-------------loading-----------------
	first_setting();	
	//make_editor = function(){		
		makeWhizzyWig("assignment-email-content","all");
		makeWhizzyWig("reminder-email-content","all");
	//}
	//setTimeout("make_editor()", 500);
	jQuery("#TB_window").css({"top":"53%"});
});