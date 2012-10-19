<link rel='stylesheet' href='<?php echo OASISWF_URL . "css/lib/modal/basic.css";?>' type='text/css' />
<link rel='stylesheet' href='<?php echo OASISWF_URL . "css/pages/subpages/actioncomment.css";?>' type='text/css' />
<script type='text/javascript' src='<?php echo OASISWF_URL . "js/lib/modal/jquery.simplemodal.js";?>' ></script>
<?php 
	$action = FCWorkflowInbox::get_action( array("ID" => $_POST["actionid"] ) ) ;
	$signoffdate = $action->create_datetime ;
	$comments = json_decode($action->comment) ;
	
	if( $_POST["page_action"] =="history" )
	{
		$action = FCWorkflowInbox::get_action( array("from_id" => $_POST["actionid"] ), "row" ) ;
		if( $action ){
			$comments = json_decode($action->comment) ;
		}
		if(!$comments)$comments = array();
	}
	
	if( $_POST["page_action"] =="review" )
	{
		$signoffdate = "" ;
		$action = FCWorkflowInbox::get_review_action( array("ID" => $_POST["actionid"] ), "row" ) ;
		if( $action ){
			$comments = json_decode($action->comments) ;
			$signoffdate = $action->update_datetime ;
		}
		if(!$comments)$comments = array();
	}
?>
<div class="info-setting" id="stepcomment-setting" style="display:none;">
	<div class="dialog-title"><strong><?php echo __("Comment(s)") ;?></strong></div>
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
				<label><strong><?php echo __("User : ") ;?></strong> <?php echo $lbl ;?></label>
				<label style="float:right;margin-right:15px;"><strong><?php echo __("Sign off date : ") ;?></strong> <?php echo $signoffdate;?></label>
				<br class="clear">
			</div>
			<p><?php echo nl2br($comment->comment);?></p>
			<div class="comment-split-line"></div>
		<?php }?>		
		<div class="changed-data-set">
			<a href="#" id="commentCancel"><?php echo __("Cancel") ;?></a>			
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