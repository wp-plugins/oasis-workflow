jQuery(document).ready(function() {
	if( exit_wfid ){
		jQuery("#publishing-action").append("<a href='#' id='exit_link' style='float:right;margin-top:10px;'>" + owf_abort_workflow_vars.abortWorkflow + "</a><span class='loading' style='display:none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>").css({"width":"100%"});
		jQuery('.error').hide() ;
	}
	jQuery( document ).on("click", "#exit_link", function(){
		if(!confirm(owf_abort_workflow_vars.abortWorkflowConfirm))return ;
		data = {
				action: 'exit_post_from_workflow' ,
				exitId: exit_wfid,
			   };
		jQuery(this).hide();
		jQuery(".loading").show();
		jQuery.post(ajaxurl, data, function( response ) {
			if(response){
				jQuery(".loading").hide();
				location.reload();
			}
		});
	})
});