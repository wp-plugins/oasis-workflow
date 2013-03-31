jQuery(document).ready(function() {
	//jQuery.modal.close();
	var select_id = jQuery("#reassign_actors").val() ;
	
	modal_close = function(){
		jQuery(document).find("#reassign-div").html("") ;
		jQuery.modal.close();
	}
	
	jQuery("#reassignCancel, .modalCloseImg").live("click", function(){
		modal_close() ;
	});
	
	jQuery("#reassignSave").click(function(){
		if(!jQuery("#reassign_actors").val())modal_close();
		var obj = this ;
		jQuery(this).parent().children("span").addClass("loading") ;
		if( select_id == jQuery("#reassign_actors").val() ){
			modal_close() ;
		}else{
			data = {
					action: 'reset_assign_actor' ,
					oasiswf: jQuery("#action_history_id").val(),
					reassign_id: jQuery("#reassign_actors").val()
				   };
			jQuery.post(ajaxurl, data, function( response ) {
				if( response && isNaN(response)){
					alert(response) ;
					jQuery(obj).parent().children("span").removeClass("loading") ;
					return false;
				}
				if(response){
					modal_close();
					location.reload();
				}
			});
		}
	});
	
	
});