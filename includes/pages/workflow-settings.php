<?php
if( isset( $_POST['page_action'] ) && sanitize_text_field( $_POST["page_action"] ) == "submit" ){
	
	$default_due_days = (isset($_POST["default_due_days"]) && $_POST["default_due_days"]) ? intval( sanitize_text_field( $_POST["default_due_days"] )) : "";
	update_site_option("oasiswf_default_due_days", $default_due_days) ;	

	$enable_workflow_process = (isset($_POST["activate_workflow_process"]) && $_POST["activate_workflow_process"]) ? sanitize_text_field( $_POST["activate_workflow_process"] ) : "";
	update_site_option("oasiswf_activate_workflow", $enable_workflow_process) ;

	$skip_workflow_roles = array();
	if (isset($_POST["skip_workflow_roles"]) && count($_POST["skip_workflow_roles"]) > 0 )
	{
	   $selectedOptions = $_POST["skip_workflow_roles"];
	   // sanitize the values
	   $selectedOptions = array_map( 'esc_attr', $selectedOptions );
	   
	   foreach ($selectedOptions as $selectedOption)
	   {
         array_push($skip_workflow_roles, $selectedOption);
	   }
	}
	update_site_option("oasiswf_skip_workflow_roles", $skip_workflow_roles) ;
	
	$wfsettings_on_post_type = array();
	if (isset($_POST["show_workflow_setting_on_post_types"]) && count($_POST["show_workflow_setting_on_post_types"]) > 0 )
	{
		$selectedTypes = $_POST["show_workflow_setting_on_post_types"];
		// sanitize the values
		$selectedTypes = array_map( 'esc_attr', $selectedTypes );
		
		foreach ($selectedTypes as $selectedType)
		{
			array_push($wfsettings_on_post_type, $selectedType);
		}
	}
	update_site_option("oasiswf_show_wfsettings_on_post_types", $wfsettings_on_post_type) ;

	$hide_workflow_graphic = (isset($_POST["hide_workflow_graphic"]) && $_POST["hide_workflow_graphic"]) ? sanitize_text_field( $_POST["hide_workflow_graphic"] ) : "";
	update_site_option("oasiswf_hide_workflow_graphic", $hide_workflow_graphic) ;
	
}
$default_due_days = get_site_option('oasiswf_default_due_days') ;
$skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
$show_wfsettings_on_post_types = get_site_option('oasiswf_show_wfsettings_on_post_types') ;
$hide_workflow_graphic = get_site_option('oasiswf_hide_workflow_graphic') ;
?>
<div class="wrap">
	<?php if( isset($_POST['page_action']) && sanitize_text_field( $_POST["page_action"] ) == "submit" ):?>
		<div class="message"><?php echo __("Settings saved successfully.", "oasisworkflow");?></div>
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
					<?php $check = ($hide_workflow_graphic == "yes") ? ' checked="checked" ' : ''; ?>
               <input type="checkbox" id="hide_workflow_graphic" name="hide_workflow_graphic" value="yes"  <?php echo $check; ?>/>
					<label class="settings-title"><?php echo __(" Hide the workflow graphic from the Post edit page.", "oasisworkflow") ; ?> </label>				
				</div>				
				<div class="select-info">
					<div>
						<label><?php echo __("Which role(s) can skip the workflow and use the out of the box options?", "oasisworkflow")?></label>
					</div>
    				<select name="skip_workflow_roles[]" id="skip_workflow_roles[]" size="6" multiple="multiple">
    				   <?php FCUtility::owf_dropdown_roles_multi( $skip_workflow_roles ); ?>
    				</select>
				</div>
				<div class="select-info">
					<div class="list-section-heading">
						<label><?php echo __("Show Workflow options for the following post/page types:", "oasisworkflow")?></label>
					</div>
    				   <?php FCUtility::owf_dropdown_post_types_multi( 'show_workflow_setting_on_post_types[]', $show_wfsettings_on_post_types ); ?>
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
		</div>
	</form>
	<?php 
	include( OASISWF_PATH . "includes/pages/about-us.php" ) ;
	?>
</div>
<script type='text/javascript'>
jQuery(document).ready(function($) {
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
	});
});
</script>
