<?php
$history_id = intval( sanitize_text_field( $_POST["oasiswf"] )) ;
$task_user = ( isset($_POST["task_user"]) && $_POST["task_user"] ) ? intval( sanitize_text_field( $_POST["task_user"] )) : get_current_user_id();
$users = array();
$history_details = FCProcessFlow::get_action_history_by_id( $history_id );
$user_info = FCProcessFlow::get_users_in_step_internal( $history_details->step_id );
$users = $user_info["users"];
?>
<div id="reassgn-setting" class="popup-div">
	<div class="dialog-title"><strong><?php echo __("Reassign", "oasisworkflow");?></strong></div>
	<br class="clear">
	<p>
		<label style="float:left;width:100px;margin-top:5px;"><?php echo __("Select User : ", "oasisworkflow");?></label>
		<select id="reassign_actors" style="width:150px;float:left;">
			<option value=""></option>
			<?php
				foreach ($users as $user) {
					$lblNm = FCWorkflowInbox::get_user_name($user->ID) ;
					if( $task_user != $user->ID )
						echo "<option value={$user->ID}>$lblNm</option>" ;
				}
			?>
		</select>
	</p>
	<br class="clear">
	<p class="reassign-set">
		<input type="button" id="reassignSave" class="button-primary" value="<?php echo __("Save", "oasisworkflow") ;?>"  />
		<span>&nbsp;</span>
		<a href="#" id="reassignCancel" style="color:blue;"><?php echo __("Cancel", "oasisworkflow") ;?></a>
	</p>
	<input type="hidden" id="action_history_id" name="action_history_id" value=<?php echo $history_id ;?> />
	<input type="hidden" id="task_user_inbox" name="task_user_inbox" value=<?php echo $task_user ;?> />
	<br class="clear">
</div>
<script type='text/javascript'>
jQuery(document).ready(function() {
	var select_id = jQuery("#reassign_actors").val() ;

	modal_close = function(){
		jQuery(document).find("#reassign-div").html("") ;
		jQuery.modal.close();
	}

	jQuery( document ).on( "click", "#reassignCancel, .modalCloseImg", function() {
		modal_close() ;
	});

	jQuery( document ).on( "click", "#reassignSave", function(){
		if(!jQuery("#reassign_actors").val())modal_close();
		var obj = this ;
		jQuery(this).parent().children("span").addClass("loading") ;
		if( select_id == jQuery("#reassign_actors").val() ){
			modal_close() ;
		}else{
			data = {
					action: 'reset_assign_actor' ,
					oasiswf: jQuery("#action_history_id").val(),
					reassign_id: jQuery("#reassign_actors").val(),
					task_user: jQuery('#task_user_inbox').val()
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
</script>