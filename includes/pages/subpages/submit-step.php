<?php
global $chkResult;
$oasiswf = ( isset($_GET["oasiswf"]) && $_GET["oasiswf"]) ? $_GET["oasiswf"] : $chkResult ;
$editable = current_user_can('edit_posts') ;
$parent_page = ( isset($_GET["parent_page"]) && $_GET["parent_page"] ) ? $_GET["parent_page"] : "post_edit" ; //check to be called from which page
if ( isset($_GET["task_user"]) && $_GET["task_user"] ) {
	$task_user = $_GET["task_user"];
} else if ( isset($_GET["user"]) && $_GET["user"] ) {
	$task_user = $_GET["user"];
} else {
	$task_user = "";
}
$post_id = null;
if( $oasiswf ){
	$current_action = FCProcessFlow::get_action_history_by_id( $oasiswf ) ;
	$current_step = FCProcessFlow::get_step_by_id( $current_action->step_id );
	$process = FCProcessFlow::get_gpid_dbid($current_step->workflow_id, $current_action->step_id, "process" );
	$success_status = json_decode($current_step->step_info) ;
	$success_status = $success_status->status ;
	$post_id = $current_action->post_id;
}
$default_due_days = get_site_option('oasiswf_default_due_days') ;
$default_date = '';
if ( !empty( $default_due_days )) {
	$default_date = date(OASISWF_EDIT_DATE_FORMAT, current_time('timestamp') + DAY_IN_SECONDS * $default_due_days);
}
$reminder_days = get_site_option('oasiswf_reminder_days');
$reminder_days_after = get_site_option('oasiswf_reminder_days_after');
?>
<div class="info-setting" id="new-step-submit-div" style="display:none;">
	<div class="dialog-title"><strong><?php echo __("Sign Off", "oasisworkflow") ;?></strong></div>
	<div id="message_div"></div>
	<div>
		<div class="select-part">
			<label><?php echo __("Action : ", "oasisworkflow") ;?></label>
			<select id="decision-select" style="width:200px;">
				<option></option>
				<option value="complete"><?php echo ( $process == "review" ) ? __("Approved", "oasisworkflow") :  __("Complete", "oasisworkflow") ;?></option>
				<option value="unable"><?php echo ( $process == "review" ) ? __("Reject", "oasisworkflow") :  __("Unable to Complete", "oasisworkflow") ;?></option>
			</select>
			<br class="clear">
		</div>

		<div id="immediately-div">
			<?php if($success_status == "publish"):?>
				<label><?php echo __("Publish", "oasisworkflow");?> : </label>
				<?php
					$pdata = get_post($post_id);
					$publish_date = strtotime( get_gmt_from_date(get_the_date('Y-m-d H:i:s', $post_id )));
               $current_gmt_time = current_time('timestamp', 1);
					if($current_gmt_time < $publish_date) :
					   $is_future_date = true;
					else:
					   $is_future_date = false;
					endif;
				?>
				<input type="checkbox" id="immediately-chk" <?php echo  $is_future_date ? '' : 'checked="checked"';  ?> />&nbsp;&nbsp;<?php echo __("Immediately", "oasisworkflow") ;?>&nbsp;&nbsp;
				<span id="immediately-span" style="display:none;">
					<?php FCProcessFlow::get_immediately_content($post_id, $success_status, $is_future_date);?>
				</span>
				<br class="clear">
			<?php endif;?>
		</div>

		<div id="sum_step_info">
			<div class="select-info">
				<label><?php echo __("Step : ", "oasisworkflow") ;?></label>
				<select id="step-select" name="step-select" style="width:150px;">
					<option></option>
				</select><span id="step-loading-span"></span>
				<br class="clear">
			</div>
			<div id="one-actors-div" class="select-info">
				<label><?php echo __("Assign actor : ", "oasisworkflow") ;?></label>
				<select id="actor-one-select" name="actor-one-select" style="width:150px;" real="assign-loading-span"></select>
				<span class="assign-loading-span">&nbsp;</span>
				<br class="clear">
			</div>
			<div id="multi-actors-div" class="select-info" style="height:120px;">
				<label><?php echo __("Assign actor(s) :", "oasisworkflow") ;?></label>
				<div class="select-actors-div">
					<div class="select-actors-list" >
						<label><?php echo __("Available", "oasisworkflow") ;?></label>
						<span class="assign-loading-span" style="float:right;margin-top:-18px;">&nbsp;</span>
						<br class="clear">
						<p>
							<select id="actors-list-select" name="actors-list-select" size=10></select>
						</p>
					</div>
					<div class="select-actors-div-point">
						<a href="#" id="assignee-set-point"><img src="<?php echo OASISWF_URL . "img/role-set.png";?>" style="border:0px;" /></a><br><br>
						<a href="#" id="assignee-unset-point"><img src="<?php echo OASISWF_URL . "img/role-unset.png";?>" style="border:0px;" /></a>
					</div>
					<div class="select-actors-list">
						<label><?php echo __("Assigned", "oasisworkflow") ;?></label><br class="clear">
						<p>
							<select id="actors-set-select" name="actors-set-select" size=10></select>
						</p>
					</div>
				</div>
				<br class="clear">
			</div>
			<?php if ($default_due_days != '' || $reminder_days != '' || $reminder_days_after != ''):?>
			<div class="text-info left">
				<div class="left">
					<label><?php echo __("Due Date : ", "oasisworkflow") ;?></label>
				</div>
				<div class="left">
					<input class="date_input" id="due-date" value="<?php echo $default_date;?>"/>
			      <button class="date-clear"><?php echo __("clear", "oasisworkflow") ;?></button>
				</div>
				<br class="clear">
			</div>
			<?php endif;?>
		</div>
			<div class="text-info left" id="comments-div">
				<div class="left">
					<label><?php echo __("Comments : ", "oasisworkflow") ;?></label>
				</div>
				<div class="left">
					<textarea id="comments" style="height:100px;width:400px;margin-top:10px;" ></textarea>
				</div>
				<br class="clear">
			</div>

		<div class="changed-data-set">
			<input type="button" id="submitSave" class="button-primary" value="<?php echo __("Sign Off", "oasisworkflow") ;?>" />
			<input type="button" id="cancelSave" class="button-primary" value="<?php echo __("Sign Off", "oasisworkflow") ;?>" />
			<input type="button" id="completeSave" class="button-primary" value="<?php echo __("Sign Off", "oasisworkflow") ;?>" />
			<span>&nbsp;</span>
			<a href="#" id="submitCancel"><?php echo __("Cancel", "oasisworkflow") ;?></a>
		</div>
		<br class="clear">
	</div>
	<input type="hidden" id="hi_post_id"  value="<?php echo $_GET["post"] ;?>" />
	<input type="hidden" id="hi_oasiswf_id" name="hi_oasiswf_id" value="<?php echo $oasiswf ;?>" />
	<input type="hidden" id="hi_editable" value="<?php echo $editable ;?>" />
	<input type="hidden" id="hi_parrent_page" value="<?php echo $parent_page ;?>" />
	<input type="hidden" id="hi_current_process" value="<?php echo $process ;?>" />
	<input type="hidden" id="hi_task_user" value="<?php echo $task_user ;?>" />
</div>