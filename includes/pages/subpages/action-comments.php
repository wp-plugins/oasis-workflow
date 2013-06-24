<?php
	$action = FCWorkflowInbox::get_action_history_by_id( $_POST["actionid"] ) ;
	$signoffdate = $action->create_datetime ;
	$comments = json_decode($action->comment) ;

	if( isset($_POST["page_action"]) && $_POST["page_action"] == "history" )
	{
		$action = FCWorkflowInbox::get_action_history_by_from_id( $_POST["actionid"] ) ;
		if( $action ){
			$comments = json_decode($action->comment) ;
		}
		if(!$comments)$comments = array();
	}

	if( isset($_POST["page_action"]) && $_POST["page_action"] =="review" )
	{
		$signoffdate = "" ;
		$action = FCWorkflowInbox::get_review_action_by_id( $_POST["actionid"] ) ;
		if( $action ){
			$comments = json_decode($action->comments) ;
			$signoffdate = $action->update_datetime ;
		}
		if(!$comments)$comments = array();
	}
?>
<div class="info-setting" id="stepcomment-setting" style="display:none;">
	<div class="dialog-title"><strong><?php echo __("Comment(s)", "oasisworkflow") ;?></strong></div>
	<div>
		<?php
		foreach ($comments as $comment) {
			if($comment->send_id == "System"){
				$lbl = "System" ;
			}else{
				//$user = get_userdata( $comment->send_id ) ;
				//$lbl = $user->data->user_nicename ;
				$lbl = FCWorkflowInbox::get_user_name($comment->send_id) ;
			}
		?>
			<div class="comment-part">
				<label><strong><?php echo __("User : ", "oasisworkflow") ;?></strong> <?php echo $lbl ;?></label>
				<label id="signoffDate"><strong><?php echo __("Sign off date : ", "oasisworkflow") ;?></strong>
				   <?php echo FCWorkflowBase::format_date_for_display($signoffdate, "-", "datetime");?>
				</label>
				<br class="clear">
			</div>
			<p><?php echo nl2br($comment->comment);?></p>
			<div class="comment-split-line"></div>
		<?php }?>
		<div class="changed-data-set">
			<a href="#" id="commentCancel"><?php echo __("Cancel", "oasisworkflow") ;?></a>
		</div>
		<br class="clear">
	</div>
</div>
<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery("#commentCancel, .modalCloseImg").live("click", function(){
			jQuery(document).find("#post_com_count_content").html("");
			jQuery(document).find(".post-com-count").show();
			jQuery.modal.close() ;
		});
	});
</script>