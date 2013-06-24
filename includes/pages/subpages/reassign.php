<?php
$history_id = $_POST["oasiswf"] ;
$task_user = ( isset($_POST["task_user"]) && $_POST["task_user"] ) ? $_POST["task_user"] : get_current_user_id();
$args['role'] = FCWorkflowInbox::get_user_role( $task_user ) ;
$role_users = get_users( $args ) ;
?>
<div id="reassgn-setting" class="popup-div">
	<div class="dialog-title"><strong><?php echo __("Reassign", "oasisworkflow");?></strong></div>
	<br class="clear">
	<p>
		<label style="float:left;width:100px;margin-top:5px;"><?php echo __("Select User : ", "oasisworkflow");?></label>
		<select id="reassign_actors" style="width:150px;float:left;">
			<option value=""></option>
			<?php
			foreach ($role_users as $user) {
				//$lblNm = ( $user->nicename ) ? $user->nicename : $user->user_login ;
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
	<br class="clear">
</div>
<script type='text/javascript'>
jQuery(document).ready(function() {
	var select_id = jQuery("#reassign_actors").val() ;

	modal_close = function(){
		jQuery(document).find("#reassign-div").html("") ;
		jQuery.modal.close();
	}

	jQuery("#reassignCancel, .modalCloseImg").live("click", function(){
		modal_close() ;
	});

	jQuery("#reassignSave").live("click", function(){
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
</script>