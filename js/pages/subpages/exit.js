jQuery(document).ready(function() {
	if( exit_wfid ){
		jQuery("#publishing-action").append("<a href='#' id='exit_link' style='float:left;margin-top:10px;'>Abort workflow</a><span class='loading' style='display:none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>").css({"width":"100%"});
		jQuery('.error').hide() ;
	}
	jQuery("#exit_link").live("click", function(){
		if(!confirm("Are you sure to abort the workflow?"))return ;
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