<link rel='stylesheet' href='<?php echo OASISWF_URL . "css/lib/modal/basic.css";?>' type='text/css' />
<link rel='stylesheet' href='<?php echo OASISWF_URL . "css/pages/subpages/reassign.css";?>' type='text/css' />
<script type='text/javascript' src='<?php echo OASISWF_URL . "js/lib/modal/jquery.simplemodal.js";?>' ></script>
<script type='text/javascript' src='<?php echo OASISWF_URL . "js/pages/subpages/reassign.js";?>' ></script>
<?php 
$action = $_POST["oasiswf"] ;
$current_userid = get_current_user_id() ;
$args['role'] = FCWorkflowInbox::get_current_user_role() ;
$role_users = get_users( $args ) ;
?>
<div id="reassgn-setting" class="popup-div">
	<div class="dialog-title"><strong><?php echo __("Reassign");?></strong></div>
	<br class="clear">	
	<p>
		<label style="float:left;width:100px;margin-top:5px;"><?php echo __("Select User : ");?></label>
		<select id="reassign_actors" style="width:150px;float:left;">
			<option value=""></option>
			<?php 
			foreach ($role_users as $user) {
				//$lblNm = ( $user->nicename ) ? $user->nicename : $user->user_login ;
				$lblNm = FCWorkflowInbox::get_user_name($user->ID) ;
				if( $current_userid != $user->ID )
					echo "<option value={$user->ID}>$lblNm</option>" ;
			}
			?>
		</select>		
	</p>
	<br class="clear">	
	<p class="reassign-set">		
		<input type="button" id="reassignSave" class="button-primary" value="<?php echo __("Save") ;?>"  />
		<span>&nbsp;</span>
		<a href="#" id="reassignCancel" style="color:blue;"><?php echo __("Cancel") ;?></a>						
	</p>
	<input type="hidden" id="action_history_id" name="action_history_id" value=<?php echo $action ;?> />
	<br class="clear">
</div>