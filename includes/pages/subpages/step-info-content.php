<?php
global $wp_roles, $wpdb ;
$process_info = "";
$step_info = "";
if( isset($_GET['step_dbid']) && $_GET["step_dbid"] != "nodefine" )
{
   $step_dbid = $_GET["step_dbid"];
	$step_row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . FCUtility::get_workflow_steps_table_name() . " WHERE ID = %d" , $step_dbid ) );
	$step_info = json_decode($step_row->step_info);
	$process_info = json_decode($step_row->process_info);
}
?>
<div id="step-setting" class="popup-div">
	<div class="dialog-title"><strong><?php echo __("Step Information");?></strong></div>
	<div id="step-setting-content" style="overflow:auto;" >
		<p class="step-name">
			<label><?php echo __("Step Name :")?> </label><input type="text" id="step-name" name="step-name" /></span>
		</p>
		<div class="step-assignee" style="height:120px;">
			<div style="margin-left:0px;">
				<label><?php echo __("Assignee(s) :") ;?> </label>
			</div>
			<div class="step-assignee-list" >
				<label>Available</label><br class="clear">
				<p>
					<select id="step-role-list" name="step-role-list" size=10>
						<?php
						foreach ( $wp_roles->role_names as $role => $name ) {
							echo "<option value='$role'>$name</option>";
						}
						?>
					</select>
				</p>
			</div>
			<div class="step-assignee-point">
				<a href="#" id="step-assignee-set-point"><img src="<?php echo OASISWF_URL . "img/role-set.png";?>" style="border:0px;" /></a><br><br>
				<a href="#" id="step-assignee-unset-point"><img src="<?php echo OASISWF_URL . "img/role-unset.png";?>" style="border:0px;" /></a>
			</div>
			<div class="step-assignee-list">
				<label><?php echo __("Assigned") ;?></label><br class="clear">
				<p>
					<select id="step-role-set-list" name="step-role-set-list" size=10>
						<?php
						if( is_object( $step_info ) && $step_info->assignee ){
							foreach ( $step_info->assignee as $role => $name ) {
								echo "<option value='$role'>$name</option>";
							}
						}
						?>
					</select>
				</p>
			</div>
		</div>
		<br class="clear">
		<p class="step-status">
			<label><?php echo __("Status :") ;?></label>
			<div style="float:left;margin-top:-15px;">
				<div style="float:left;text-align:center;">
					<label>On Success</label><br><br>
					<?php
					if (isset($_GET['process_name']) && $_GET['process_name'] != "publish")
					{
					?>
   					<select id="step-status-select" style="width:150px;margin-top:-5px;">
   						<option value=""></option>
   						<?php
   						foreach ( get_post_stati(array('show_in_admin_status_list' => true)) as $status ) {
   							$chk = "" ;
   							if( is_object( $step_info ) && ($status == $step_info->status) ){
   								$chk = "selected" ;
   							}
   							echo "<option value='{$status}' $chk>{$status}</option>" ;
   						}
   						?>
   					</select>
   				<?php
					}
   				else // if step is publish, then success step has to be "publish"
   				{
   				?>
						<div id="step_status_publish"><?php echo __("publish"); ?></div>
					<?php
   				}
					?>
				</div>
				<div style="float:left;margin-left:50px;text-align:center;">
					<label>On Failure</label><br><br>
					<select id="step-failure-status-select" style="width:150px;margin-top:-5px;">
						<option value=""></option>
						<?php
						foreach ( get_post_stati(array('show_in_admin_status_list' => true)) as $status ) {
							$chk = "" ;
							if( is_object( $step_info ) && ($status == $step_info->failure_status )){
								$chk = "selected" ;
							}
							echo "<option value='{$status}' $chk>{$status}</option>" ;
						}
						?>
					</select>
				</div>
			</div>
			<br class="clear">
		</p>
		<div class="step-review" style="display:none;">
			<div class="step-review-lb">
				<label><?php echo __("Review Decision : ") ;?></label>
			</div>
			<div class="step-review-chk">
				<?php
					$oasiswf_review_decision = get_site_option( "oasiswf_review" ) ;
					if( $oasiswf_review_decision ) {
						$stl = "style='margin-top:5px;'" ;
						foreach ( $oasiswf_review_decision as $k => $v ) {
							//$chk = "" ;
							//$chk = ($step_info->decision == $k) ? "checked=true" : "" ;
							$chk = ("everyone" == $k) ? "checked=true" : "" ;
							echo "<div $stl><input type='radio' id='rdo-{$k}' name='review-opt' value='{$k}' $chk /> $v</div>" ;
							$stl = "";
						}
					}
				?>
			</div>
			<br class="clear">
		</div>
		<div class="first_step" >
			<label><?php echo __("Is first step? : ") ;?></label>
			<span><input type="checkbox" id="first_step_check" /></span>
			<br class="clear">
		</div>
		<a href="#" class="more-show" id="more-show" style="color:blue;"><?php echo __("Advanced details");?></a>
		<form>
			<div id="wf-email-define" style="display:none;margin-top:40px;">
				<h3 style="margin:10px 0 20px 0;"><?php echo __("Assignment Email") ;?></h3>
				<div>
					<div style="float:left;width:130px;"><label><?php echo __("Placeholder : ") ;?></label></div>
					<div style="float:left;">
						<select id="assign-placeholder" style="width:150px;">
							<option value=" "></option>
							<?php
							$placeholders = get_site_option( "oasiswf_placeholders" ) ;
							if( $placeholders ){
								foreach ($placeholders as $k => $v ) {
									echo "<option value='$k'>{$v}</option>" ;
								}
							}
							?>
						</select>
						<input type="button" class="button-primary placeholder-add-bt" value="Add" style="margin-left:20px;" />
					</div>
					<br class="clear">
				</div>
				<p>
					<label >Email subject : </label>
					<?php
					$assignment_subject = "";
					$assignment_content = "";
					$reminder_subject = "";
					$reminder_content = "";
					if (is_object($process_info) )
					{
					   $assignment_subject = $process_info->assign_subject;
					   $assignment_content = $process_info->assign_content;
					   $reminder_subject = $process_info->reminder_subject;
					   $reminder_content = $process_info->reminder_content;
					}
					?>
					<input type="text" id="assignment-email-subject" name="assignment-email-subject" value="<?php echo $assignment_subject;?>" />
				</p>
				<div style="width:100%;height:250px;">
					<div style="float:left;"><label><?php echo __("Email message : ") ;?></label></div>
					<div style="float:left;" id="assignment-email-content-div">
						<textarea id="assignment-email-content" name="assignment-email-content"
							style="width:500px;height:200px;"><?php echo $assignment_content;?></textarea>
					</div>
					<br class="clear">
				</div>
				<br class="clear">
				<div style="margin:30px 0 20px 0;">
					<h3><?php echo __("Reminder Email") ;?></h3>
				</div>
				<div>
					<div style="float:left;width:130px;"><label><?php echo __("Placeholder : ") ;?></label></div>
					<div style="float:left;">
						<select id="reminder-placeholder" style="width:150px;">
							<option value=" "></option>
							<?php
							$placeholders = get_site_option( "oasiswf_placeholders" ) ;
							if( $placeholders ){
								foreach ($placeholders as $k => $v ) {
									echo "<option value='$k'>{$v}</option>" ;
								}
							}
							?>
						</select>
						<input type="button" class="button-primary placeholder-add-bt" value="Add" style="margin-left:20px;" />
					</div>
					<br class="clear">
				</div>
				<p>
					<label><?php echo __("Email subject : ") ;?></label>
					<input type="text" id="reminder-email-subject" name="reminder-email-subject" value="<?php echo $reminder_subject?>" />
				</p>
				<div style="width:100%;height:250px;">
					<div style="float:left;"><label><?php echo __("Email message : ") ;?></label></div>
					<div style="float:left;">
						<textarea id="reminder-email-content" name="reminder-email-content"
							style="width:500px;height:200px;"><?php echo $reminder_content;?></textarea>
					</div>
					<br class="clear">
				</div>
			</div>
		</form>
		<br class="clear">
		<input type="hidden" id="step_gpid-hi" value="<?php echo $_GET["step_gpid"] ;?>" />
		<input type="hidden" id="step_dbid-hi" value="<?php echo $_GET["step_dbid"] ;?>" />
	</div>
	<div class="dialog-title" style="padding-bottom:0.5em"></div>
	<br class="clear">
	<p class="step-set">
		<?php if( $_GET["editable"] ):?>
			<input type="button" id="stepSave" class="button-primary" value="<?php echo __("Save") ;?>"  />
			<span>&nbsp;</span>
		<?php endif;?>
			<a href="#" id="stepCancel" style="color:blue;margin-top:5px;"><?php echo __("Cancel") ;?></a>
	</p>
	<br class="clear">
</div>