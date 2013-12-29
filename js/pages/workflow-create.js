var jQueryCgmp = jQuery.noConflict();
(function (jQuery) {
	jQuery(document).ready(function() {	
		var changed_step_chk = false ;
		var deleted_step = new Array(); // IDs of Deleted step
		
		//------------changed workflow check----------
		set_step_chaned_status = function(){
	    	changed_step_chk = true;
	    }
			//----window colse-----
		window.onbeforeunload = function(){
	    	if( changed_step_chk ){
	    		return owf_workflow_create_vars.unsavedChanges ; 
	    	}
	    }
		//------------workflow save-------------	
		jQuery(".handlediv").click(function(){
	    	var obj = jQuery(this).parent().children('.move-div');
	    	if(obj.css('display') == 'block')
	    		obj.hide();
	    	else
	    		obj.show();
	    });
	    jQuery("#define-workflow-title, #new-workflow-title").keydown(function(){
	    	jQuery(this).css({"background-color": "white"});
	    });
		jQuery(".workflow-save-bt").click(function(){
			jQuery("#wf_graphic_data_hi").val("");
			if(!jQuery("#define-workflow-title").val()){
	    		jQuery("#workflow-define-div").show();
	    		jQuery("#define-workflow-title").css({"background-color": "#fbf3f3"}).focus();
	    		return;
	    	}
			
			var wfInfo = _get_workflow_info();
	    	if(!wfInfo)return false;
	    	jQuery("#wf_graphic_data_hi").val(wfInfo);
	    	
	    	if(!chk_date_input())return;		
	    	
	    	if( deleted_step.length > 0 ){
	    		var delIdStr = "" ; 
	    		for(var i = 0; i < deleted_step.length; i++){
	    			delIdStr += deleted_step[i] + "@" ;
	    		}
	    		jQuery("#deleted_step_ids").val(delIdStr) ;
	    	}
	    	changed_step_chk = false;
	    	
	    	var action_url = jQuery("#wf-form").attr("action") ;
	    	jQuery("#wf-form").attr("action", action_url + "&wf_id=" + jQuery("#wf_id").val()) ;
	    	jQuery("#wf-form").submit();
			
	    	return ;
	    });
		
		var chk_date_input = function(){
			if(!jQuery("#start-date").val()){
				jQuery("#start-date").css({"background-color": "#fbf3f3"}).focus();		
				return false;
			}
			
			if(!jQuery("#end-date").val()){
				jQuery("#end-date").css({"background-color": "#fbf3f3"}).focus();		
				return false;
			}
			
			return true;
		}
	    //------------modal-------------------------	 
	    showConnectionDialog = function(linkObj, connset){
	    	call_modal("connection-setting");
	    	jQuery("#source_name_lbl").html(jQuery("#" + linkObj.sourceId + " label").html()); 
	    	jQuery("#target_name_lbl").html(jQuery("#" + linkObj.targetId + " label").html());    	
	    	jQuery("#path-opt-"+connset["path"]).attr('checked', true);
	    	jQuery("#link-rdo-"+connset["connector"]).attr('checked', true);
	    	
	    }
	    
	    //----------calculator-----------
	    jQuery(".date-clear").click(function(){
			jQuery(this).parent().children(".date_input").val("");
			return false;
		});
		
		//----------------Menu------------------
		jQuery(".wrap").click(function(){jQuery(".contextMenu").hide();});
		jQuery(".contextMenu li a").mouseover(function(){
	    	var obj = this;
	    	jQuery(".contextMenu li a").each(function(){
	    		jQuery(this).removeClass('menu_hover').addClass('menu_out');
	    	});
	    	jQuery(obj).addClass('menu_hover');
	    });
		
		jQuery("#connQuit, #stepQuit").click(function(){
			jQuery(".contextMenu").hide();
	    })  
	    
		//------------Saving stepid after deleting step ----------------
	    
		set_deleted_step = function(stepdbid){
			deleted_step[deleted_step.length] = stepdbid ;
		}
		//-------------as save----------------
		jQuery("#save_as_link, .workflow-assave-bt").click(function(){
			jQuery("#save_action").val("workflow_as_save"); 
			jQuery("#wf-form").submit();
		});
		
	});
}(jQueryCgmp));