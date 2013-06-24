<?php
if( isset($_POST['page_action']) && $_POST["page_action"] == "submit" ){

	$reminder_days = (isset($_POST["reminder_days"]) && $_POST["reminder_days"]) ? $_POST["reminder_days"] : "";
   update_site_option("oasiswf_reminder_days", $reminder_days) ;

	$reminder_days_after = (isset($_POST["reminder_days_after"]) && $_POST["reminder_days_after"]) ? $_POST["reminder_days_after"] : "";
   update_site_option("oasiswf_reminder_days_after", $reminder_days_after) ;

	$enable_workflow_process = (isset($_POST["activate_workflow_process"]) && $_POST["activate_workflow_process"]) ? $_POST["activate_workflow_process"] : "";
	update_site_option("oasiswf_activate_workflow", $enable_workflow_process) ;

	$skip_workflow_roles = array();
	if (isset($_POST["skip_workflow_roles"]) && count($_POST["skip_workflow_roles"]) > 0 )
	{
	   $selectedOptions = $_POST["skip_workflow_roles"];
	   foreach ($selectedOptions as $selectedOption)
	   {
         array_push($skip_workflow_roles, $selectedOption);
	   }
	}
	update_site_option("oasiswf_skip_workflow_roles", $skip_workflow_roles) ;

	$auto_submit_stati = array();
	if (isset($_POST["auto_submit_stati"]) && count($_POST["auto_submit_stati"]) > 0 )
	{
	   $selectedOptions = $_POST["auto_submit_stati"];
	   foreach ($selectedOptions as $selectedOption)
	   {
         array_push($auto_submit_stati, $selectedOption);
	   }
	}
	/*
   $auto_submit_due_days = (isset($_POST["auto_submit_due_days"]) && $_POST["auto_submit_due_days"]) ? $_POST["auto_submit_due_days"] : "";
   $auto_submit_comment = (isset($_POST["auto_submit_comment"]) && trim($_POST["auto_submit_comment"])) ? $_POST["auto_submit_comment"] : "";
   $auto_submit_post_count = (isset($_POST["auto_submit_post_count"]) && $_POST["auto_submit_post_count"]) ? $_POST["auto_submit_post_count"] : "";
   $auto_submit_settings = array(
      'auto_submit_stati' => $auto_submit_stati,
      'auto_submit_due_days' => $auto_submit_due_days,
      'auto_submit_comment' => stripcslashes( $auto_submit_comment ),
      'auto_submit_post_count' => $auto_submit_post_count
   );
	update_site_option("oasiswf_auto_submit_settings", $auto_submit_settings) ;
	*/

}
/*
if( isset($_POST['page_action']) && $_POST["page_action"] == "auto_submit" ){
   $submitted_posts_count = FCWorkflowActions::auto_submit_articles();
}
*/
$reminder_day = get_site_option('oasiswf_reminder_days') ;
$reminder_day_after = get_site_option('oasiswf_reminder_days_after') ;
$skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
$auto_submit_settings = get_site_option('oasiswf_auto_submit_settings');
$auto_submit_stati = $auto_submit_settings['auto_submit_stati'];
$auto_submit_due_days = $auto_submit_settings['auto_submit_due_days'];
$auto_submit_comment = $auto_submit_settings['auto_submit_comment'];
$auto_submit_post_count = $auto_submit_settings['auto_submit_post_count'];
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Settings", "oasisworkflow"); ?></h2>
	<?php if( isset($_POST['page_action']) && $_POST["page_action"] == "submit" ):?>
		<div class="message"><?php echo __("Settings saved successfully.", "oasisworkflow");?></div>
	<?php endif;?>
	<?php if( isset($_POST['page_action']) && $_POST["page_action"] == "auto_submit" ):?>
		<div class="message"><?php echo __("Auto submit completed successfully. " . $submitted_posts_count . " posts/page submitted.", "oasisworkflow");?></div>
	<?php endif;?>
	<form id="wf_settings_form" method="post">
		<div id="workflow-setting">
			<div id="settingstuff">
				<div class="select-info" style="padding: 10px;">
   				<?php
   				$str="" ;
   				if( get_site_option("oasiswf_activate_workflow") == "active" )$str = "checked=true" ;
   				?>
					<label><input type="checkbox" name="activate_workflow_process"
						value="active" <?php echo $str;?> />&nbsp;&nbsp;<?php echo __("Activate Workflow process ?", "oasisworkflow") ;?>
					</label>
				</div>
				<div class="select-info" style="padding: 10px;">
					<label>
						<input type="checkbox" id="chk_reminder_day"	<?php echo ($reminder_day) ? "checked" : "" ;?> />
						&nbsp;&nbsp;<?php echo __(" Send Reminder Email", "oasisworkflow") ;?>
					</label>
					<input type="text" id="reminder_days" name="reminder_days" size="4" class="reminder_days" value="<?php echo $reminder_day;?>" maxlength=2 />
					<?php echo __("day(s) before due date.", "oasisworkflow");?>
				</div>
				<div class="select-info" style="padding: 10px;">
					<label>
						<input type="checkbox" id="chk_reminder_day_after"	<?php echo ($reminder_day_after) ? "checked" : "" ;?> />
						&nbsp;&nbsp;<?php echo __(" Send Reminder Email", "oasisworkflow") ;?>
					</label>
					<input type="text" id="reminder_days_after" name="reminder_days_after" size="4" class="reminder_days" value="<?php echo $reminder_day_after;?>" maxlength=2 />
					<?php echo __("day(s) after due date.", "oasisworkflow");?>
				</div>
				<div class="select-info" style="padding: 10px;">
					<div>
						<label><?php echo __("Which role(s) can skip the workflow and use the out of the box options?", "oasisworkflow")?></label>
					</div>
    				<select name="skip_workflow_roles[]" id="skip_workflow_roles[]" size="6" multiple="multiple">
    				   <?php FCUtility::owf_dropdown_roles_multi($skip_workflow_roles); ?>
    				</select>
				</div>
				<!-- hide these settings -->
				<!--
				<fieldset class="owf_fieldset">
					<legend><?php echo __("Auto Submit Settings", "oasisworkflow")?></legend>
					<ol>
   					<li>
         				<div class="select-info" style="padding: 10px;">
         					<div>
         						<label><?php echo __("Post/Page status(es)", "oasisworkflow")?></label>
         					</div>
             				<select name="auto_submit_stati[]" id="auto_submit_stati[]" size="6" multiple="multiple">
             				   <?php FCUtility::owf_dropdown_post_status_multi($auto_submit_stati); ?>
             				</select>
         				</div>
      				</li>
      				<li>
         				<div class="select-info" style="padding: 10px;">
         					<label>
        						   <?php echo __("Set Due date as current date + ", "oasisworkflow") ;?>
         					</label>
         					<input type="text" id="auto_submit_due_days" name="auto_submit_due_days" size="4" class="auto_submit_due_days" value="<?php echo $auto_submit_due_days;?>" maxlength=2 />
         					<?php echo __("day(s).", "oasisworkflow");?>
         				</div>
      				</li>
      				<li>
         				<div class="select-info" style="padding: 10px;">
         					<div>
            					<label>
           						   <?php echo __("Auto submit comments", "oasisworkflow") ;?>
            					</label>
            				</div>
         					<textarea id="auto_submit_comment" name="auto_submit_comment" size="4" class="auto_submit_comment"
         					cols="80" rows="5"><?php echo $auto_submit_comment;?></textarea>
         				</div>
      				</li>
      				<li>
         				<div class="select-info" style="padding: 10px;">
         					<label>
        						   <?php echo __("Process ", "oasisworkflow") ;?>
         					</label>
         					<input type="text" id="auto_submit_post_count" name="auto_submit_post_count" size="8" class="auto_submit_post_count" value="<?php echo $auto_submit_post_count;?>" maxlength=4 />
         					<?php echo __("posts/pages at one time.", "oasisworkflow");?>
         					</br/>
         					<?php echo __("(Limit the number of posts/pages to be processed at one time for optimum server performance.)", "oasisworkflow");?>
         				</div>
      				</li>
   				</ol>
				</fieldset>
				-->
				<div id="owf_settings_button_bar">
					<input type="submit" id="settingSave"
						class="button button-primary button-large"
						value="<?php echo __("Save", "oasisworkflow") ;?>" />
				<!--
					<input type="button" id="autoSubmitBtn"
						class="button button-primary button-large"
						value="<?php echo __("Trigger Auto Submit", "oasisworkflow") ;?>" />
 				-->
					<input type="hidden"
						name="page_action" id="page_action" value="submit" />
				</div>
			</div>

	</form>
	<div id="poststuff">
		<div class="owf-sidebar">
			<div class="postbox" style="float: left;">
				<h3 style="cursor: default;">
					<span><?php _e("About this Plugin:", "oasisworkflow"); ?> </span>
				</h3>
				<div class="inside">
					<a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . '/img/nugget-solutions.png'; ?>);" target="_blank" href="http://www.nuggetsolutions.com/"><?php _e("Author's website", "oasisworkflow"); ?>
					</a> <a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . 'img/publish.gif'; ?>);" target="_blank" href="http://oasisworkflow.com/"><?php _e('Plugin webpage', "oasisworkflow"); ?>
					</a> <a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . '/img/faq-icon.png'; ?>);" target="_blank" href="http://oasisworkflow.com/faq/"><?php _e('FAQ', "oasisworkflow"); ?>
					</a>
					<hr />
					<div style="text-align: center;">
						<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="8YRMFYFEAEBQG">
							<input	type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
								border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt=""	border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type='text/javascript'>
jQuery(document).ready(function($) {
	jQuery("#chk_reminder_day").click(function(){
		if(jQuery(this).attr("checked") == "checked"){
			jQuery("#reminder_days").attr("disabled", false) ;
		}else{
			jQuery("#reminder_days").val('');
			jQuery("#reminder_days").attr("disabled", true) ;
		}
	}) ;

	jQuery("#chk_reminder_day_after").click(function(){
		if(jQuery(this).attr("checked") == "checked"){
			jQuery("#reminder_days_after").attr("disabled", false) ;
		}else{
			jQuery("#reminder_days_after").val('');
			jQuery("#reminder_days_after").attr("disabled", true) ;
		}
	}) ;

	jQuery("#settingSave").click(function(){
		if( jQuery("#chk_reminder_day").attr("checked") == "checked" ){
			if( !jQuery("#reminder_days").val() ){
				alert("Please enter the number of days.") ;
				return false;
			}
			if(isNaN(jQuery("#reminder_days").val())){
				alert("Please enter a numeric value.") ;
				return false;
			}
		}

		if( jQuery("#chk_reminder_day_after").attr("checked") == "checked" ){
			if( !jQuery("#reminder_days_after").val() ){
				alert("Please enter the number of days.") ;
				return false;
			}
			if(isNaN(jQuery("#reminder_days_after").val())){
				alert("Please enter a numeric value.") ;
				return false;
			}
		}
		/*
		if( !jQuery("#auto_submit_due_days").val() ){
			alert("Please enter the number of days.") ;
			return false;
		}
		if(isNaN(jQuery("#auto_submit_due_days").val())){
			alert("Please enter a numeric value.") ;
			return false;
		}

		if( !jQuery("#auto_submit_post_count").val() ){
			alert("Please enter the number of posts/pages to be processed at one time.") ;
			return false;
		}
		if(isNaN(jQuery("#auto_submit_post_count").val())){
			alert("Please enter a numeric value.") ;
			return false;
		}
		*/

	});

	/*
	jQuery("#autoSubmitBtn").click(function(){
		jQuery("#page_action").val("auto_submit");
		jQuery("#wf_settings_form").submit();
	});
	*/
});
</script>
