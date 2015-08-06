<?php
if( isset($_POST['page_action'])){
	$from_name = (isset($_POST["wf_from_name"]) && $_POST["wf_from_name"]) ? sanitize_text_field( $_POST["wf_from_name"] ) : "";
	$from_email_address = (isset($_POST["wf_from_email_address"]) && $_POST["wf_from_email_address"]) ? sanitize_email( $_POST["wf_from_email_address"] ) : "";
	$assignment_emails = (isset($_POST["assignment_emails"]) && $_POST["assignment_emails"]) ? sanitize_text_field( $_POST["assignment_emails"] ) : "";
	$reminder_emails = (isset($_POST["reminder_emails"]) && $_POST["reminder_emails"]) ? sanitize_text_field( $_POST["reminder_emails"] ): "";
	$post_publish_emails = (isset($_POST["post_publish_emails"]) && $_POST["post_publish_emails"]) ? sanitize_text_field( $_POST["post_publish_emails"] ) : "";
	
	$reminder_days = (isset($_POST["reminder_days"]) && $_POST["reminder_days"]) ? intval( sanitize_text_field( $_POST["reminder_days"] )) : "";
	update_site_option("oasiswf_reminder_days", $reminder_days) ;
	
	$reminder_days_after = (isset($_POST["reminder_days_after"]) && $_POST["reminder_days_after"]) ? intval( sanitize_text_field( $_POST["reminder_days_after"] )) : "";
	update_site_option("oasiswf_reminder_days_after", $reminder_days_after) ;	
	
	$email_settings = array(
		'from_name' => $from_name,
		'from_email_address' => $from_email_address,
		'assignment_emails' => $assignment_emails,
		'reminder_emails' => $reminder_emails,
		'post_publish_emails' => $post_publish_emails
	);
	update_site_option("oasiswf_email_settings", $email_settings) ;
}
$email_settings = get_site_option('oasiswf_email_settings');
$from_name = $email_settings['from_name'];
$from_email_address = $email_settings['from_email_address'];
$assignment_emails = $email_settings['assignment_emails'];
$reminder_emails = $email_settings['reminder_emails'];
$post_publish_emails = $email_settings['post_publish_emails'];

$reminder_day = get_site_option('oasiswf_reminder_days') ;
$reminder_day_after = get_site_option('oasiswf_reminder_days_after') ;

?>
<div class="wrap">
	<?php if( isset($_POST['page_action']) && sanitize_text_field( $_POST["page_action"] ) == "submit_email_settings" ):?>
		<div class="message"><?php echo __("Email settings saved successfully.", "oasisworkflow");?></div>
	<?php endif;?>

	<form id="wf_settings_form" method="post">
		<div id="workflow-email-setting">
			<div id="settingstuff">
      		<div class="select-info">
        			<label class="settings-title">
     				   <?php echo __("From Name:", "oasisworkflow") ;?>
        			</label>
         		<input type="text" id="wf_from_name" name="wf_from_name" class="regular-text" value="<?php echo $from_name;?>" />
      			</br/>
      			<span class="description"><?php echo __("(Name to be used for sending the workflow related emails. If left blank, the emails will be sent from the blog name.)", "oasisworkflow");?></span>
      		</div>			
      		<div class="select-info">
        			<label class="settings-title">
     				   <?php echo __("From Email:", "oasisworkflow") ;?>
        			</label>
         		<input type="text" id="wf_from_email_address" name="wf_from_email_address" class="regular-text" value="<?php echo $from_email_address;?>" />
      			</br/>
      			<span class="description"><?php echo __("(Email address to be used for sending the workflow related emails. If left blank, the default email will be used.)", "oasisworkflow");?></span>
      		</div>
      		<hr/>
            <div class="select-info">
					<?php $check = ($post_publish_emails == "yes") ? ' checked="checked" ' : ''; ?>
               <input type="checkbox" id="post_publish_emails" name="post_publish_emails" value="yes"  <?php echo $check; ?>/>&nbsp;&nbsp;
					<label class="settings-title"><?php echo __("Check this box if you want to send an email to the author when post/page is published.", "oasisworkflow") ; ?> </label>
				</div>
            <div class="select-info">
					<?php $check = ($assignment_emails == "yes") ? ' checked="checked" ' : ''; ?>
               <input type="checkbox" id="assignment_emails" name="assignment_emails" value="yes"  <?php echo $check; ?>/>&nbsp;&nbsp;
					<label class="settings-title"><?php echo __("Check this box if you want to send emails when tasks are assigned.", "oasisworkflow") ; ?> </label>
				</div>
				<fieldset class="owf_fieldset">
					<legend><?php echo __("Task reminder settings","oasisworkflow");?></legend>
					<span class="description"><?php echo __("(Applicable only if reminder email configuration is completed during workflow setup.)", "oasisworkflow");?></span>
	            <div class="select-info">
						<?php $check = ($reminder_emails == "yes") ? ' checked="checked" ' : ''; ?>
	               <input type="checkbox" id="reminder_emails" name="reminder_emails" value="yes"  <?php echo $check; ?>/>&nbsp;&nbsp;
						<label class="settings-title"><?php echo __("Check this box if you want to send reminder emails about a pending task.", "oasisworkflow") ; ?> </label>
						<br/>
					</div> 					
      			<div class="select-info">
     					<label class="settings-title">
     						<input type="checkbox" id="chk_reminder_day"	<?php echo ($reminder_day) ? "checked" : "" ;?> />&nbsp;&nbsp;
     						<?php echo __(" Send Reminder Email", "oasisworkflow") ;?>
     					</label>
     					<input type="text" id="reminder_days" name="reminder_days" size="4" class="reminder_days" value="<?php echo $reminder_day;?>" maxlength=2 />
     					<label class="settings-title"><?php echo __("day(s) before due date.", "oasisworkflow");?></label>
     				</div>
     				<div class="select-info">
     					<label class="settings-title">
     						<input type="checkbox" id="chk_reminder_day_after"	<?php echo ($reminder_day_after) ? "checked" : "" ;?> />&nbsp;&nbsp;
     						<?php echo __(" Send Reminder Email", "oasisworkflow") ;?>
     					</label>
     					<input type="text" id="reminder_days_after" name="reminder_days_after" size="4" class="reminder_days" value="<?php echo $reminder_day_after;?>" maxlength=2 />
     					<label class="settings-title"><?php echo __("day(s) after due date.", "oasisworkflow");?></label>
     				</div>
				</fieldset>			   
				<div id="owf_settings_button_bar">
					<input type="submit" id="emailSettingSave"
						class="button button-primary button-large"
						value="<?php echo __("Save", "oasisworkflow") ;?>" />

					<input type="hidden"
						name="page_action" id="page_action" value="submit_email_settings" />
				</div>
			</div>
		</div>	
	</form>
	<?php 
	include( OASISWF_PATH . "includes/pages/about-us.php" ) ;
	?>	
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

	jQuery("#emailSettingSave").click(function(){
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