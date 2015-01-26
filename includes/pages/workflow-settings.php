<?php
if( isset($_POST['page_action']) && $_POST["page_action"] == "submit" ){
	
	$default_due_days = (isset($_POST["reminder_days"]) && $_POST["default_due_days"]) ? $_POST["default_due_days"] : "";
	update_site_option("oasiswf_default_due_days", $default_due_days) ;	

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

}
$default_due_days = get_site_option('oasiswf_default_due_days') ;
$reminder_day = get_site_option('oasiswf_reminder_days') ;
$reminder_day_after = get_site_option('oasiswf_reminder_days_after') ;
$skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
$auto_submit_settings = get_site_option('oasiswf_auto_submit_settings');
$auto_submit_stati = $auto_submit_settings['auto_submit_stati'];
$auto_submit_due_days = $auto_submit_settings['auto_submit_due_days'];
$auto_submit_comment = $auto_submit_settings['auto_submit_comment'];
$auto_submit_post_count = $auto_submit_settings['auto_submit_post_count'];
FCUtility::owf_pro_features();
?>
<div class="wrap">
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
				<div class="select-info">
   				<?php
   				$str="" ;
   				if( get_site_option("oasiswf_activate_workflow") == "active" )$str = "checked=true" ;
   				?>
					<label><input type="checkbox" name="activate_workflow_process"
						value="active" <?php echo $str;?> />&nbsp;&nbsp;<?php echo __("Activate Workflow process ?", "oasisworkflow") ;?>
					</label>
				</div>
				<div class="select-info">
					<label class="settings-title">
						<input type="checkbox" id="chk_default_due_days"	<?php echo ($default_due_days) ? "checked" : "" ;?> />
					   <?php echo __("Set default Due date as CURRENT DATE + ", "oasisworkflow") ;?>
					</label>
					<input type="text" id="default_due_days" name="default_due_days" size="4" class="default_due_days" value="<?php echo $default_due_days;?>" maxlength=2 />
					<label class="settings-title"><?php echo __("day(s).", "oasisworkflow");?></label>
				</div>				
				<div class="select-info">
					<label>
						<input type="checkbox" id="chk_reminder_day"	<?php echo ($reminder_day) ? "checked" : "" ;?> />
						<?php echo __(" Send Reminder Email", "oasisworkflow") ;?>
					</label>
					<input type="text" id="reminder_days" name="reminder_days" size="4" class="reminder_days" value="<?php echo $reminder_day;?>" maxlength=2 />
					<?php echo __("day(s) before due date.", "oasisworkflow");?>
				</div>
				<div class="select-info">
					<label>
						<input type="checkbox" id="chk_reminder_day_after"	<?php echo ($reminder_day_after) ? "checked" : "" ;?> />
						<?php echo __(" Send Reminder Email", "oasisworkflow") ;?>
					</label>
					<input type="text" id="reminder_days_after" name="reminder_days_after" size="4" class="reminder_days" value="<?php echo $reminder_day_after;?>" maxlength=2 />
					<?php echo __("day(s) after due date.", "oasisworkflow");?>
				</div>
				<div class="select-info">
					<div>
						<label><?php echo __("Which role(s) can skip the workflow and use the out of the box options?", "oasisworkflow")?></label>
					</div>
    				<select name="skip_workflow_roles[]" id="skip_workflow_roles[]" size="6" multiple="multiple">
    				   <?php FCUtility::owf_dropdown_roles_multi($skip_workflow_roles); ?>
    				</select>
				</div>
				<!-- hide these settings -->
				<div id="owf_settings_button_bar">
					<input type="submit" id="settingSave"
						class="button button-primary button-large"
						value="<?php echo __("Save", "oasisworkflow") ;?>" />
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
				<div class="inside inside-section">
					</a> <a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . 'img/publish.gif'; ?>);" target="_blank" href="http://oasisworkflow.com/"><?php _e('Plugin webpage', "oasisworkflow"); ?>
					</a> <a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . '/img/faq-icon.png'; ?>);" target="_blank" href="http://oasisworkflow.com/faq/"><?php _e('FAQ', "oasisworkflow"); ?>
					</a>
				</div>
            <h3><span><?php _e('Help us Improve:', 'oasisworkflow') ?></span></h3>
            <div class="inside inside-section">
                <p><a href="https://www.oasisworkflow.com/submit-a-query" target="_blank"><?php _e('Suggest', 'oasisworkflow') ?></a> <?php _e('features', 'oasisworkflow') ?>.</p>
                <p><a href="http://wordpress.org/support/view/plugin-reviews/oasis-workflow/" target="_blank"><?php _e('Rate', 'oasisworkflow') ?></a> <?php _e('the plugin 5 stars on WordPress.org', 'oasisworkflow') ?>.</p>
                <p><a href="https://www.facebook.com/oasisworkflow" target="_blank"><?php _e('Like us', 'oasisworkflow') ?></a> <?php _e('on', 'oasisworkflow') ?> Facebook. </p>
            </div>
            <h3><span><?php _e('Go Pro:', 'oasisworkflow') ?></span></h3>
            <div class="inside inside-section">
             	<p><a href="https://www.oasisworkflow.com/pricing-purchase" target="_blank"><?php _e('Pricing & Purchase', 'oasisworkflow') ?></a></p>
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
		if( jQuery("#chk_default_due_days").attr("checked") == "checked" ){
			if( !jQuery("#default_due_days").val() ){
				alert("Please enter the number of days for default due date.") ;
				return false;
			}
      	if(isNaN(jQuery("#default_due_days").val())){
      		alert("Please enter a numeric value for default due date.") ;
      		return false;
      	}
		}
				
		if( jQuery("#chk_reminder_day").attr("checked") == "checked" ){
			if( !jQuery("#reminder_days").val() ){
				alert("Please enter the number of days for reminder email before due date.") ;
				return false;
			}
			if(isNaN(jQuery("#reminder_days").val())){
				alert("Please enter a numeric value for reminder email before due date.") ;
				return false;
			}
		}

		if( jQuery("#chk_reminder_day_after").attr("checked") == "checked" ){
			if( !jQuery("#reminder_days_after").val() ){
				alert("Please enter the number of days for reminder email after due date.") ;
				return false;
			}
			if(isNaN(jQuery("#reminder_days_after").val())){
				alert("Please enter a numeric value for reminder email after due date.") ;
				return false;
			}
		}
	});
});
</script>
