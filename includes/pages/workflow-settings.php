<?php 
	if( $_POST["page_action"] == "submit" ){	
		update_option("oasiswf_reminder_days", $_POST["reminder_days"]) ;		
		update_option("activate_workflow", $_POST["activate_workflow_process"]) ;
	}
	$reminder_day = get_option('oasiswf_reminder_days') ;
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Settings"); ?></h2>
	<?php if( $_POST["page_action"] == "submit" ):?>	
		<div class="message"><?php echo __("Settings saved successfully.");?></div>
	<?php endif;?>
	<form id="setting_form" method="post" >
		<div id="workflow-setting">
			<div class="select-info" style="padding:10px;">
				<label><input type="checkbox" id="chk_reminder_day" <?php echo ($reminder_day) ? "checked" : "" ;?> />&nbsp;&nbsp;<?php echo __(" Send Reminder Emails ?") ;?></label>
				<div id="div_reminder_day" style="<?php echo ($reminder_day) ? "opacity:1" : "opacity:0.5" ;?>" >
					<?php echo __("Send reminder email");?> 
					<input type="text" id ="reminder_days" name ="reminder_days" value="<?php echo $reminder_day;?>" maxlength=2 /> <?php echo __("days before due date.");?>
				</div>
			</div>
			<div class="select-info" style="padding:10px;">
				<?php
					$str="" ; 
					if( get_option("activate_workflow") == "active" )$str = "checked=true" ;
				?>
					<label><input type="checkbox" name="activate_workflow_process" value="active" <?php echo $str;?> />&nbsp;&nbsp;<?php echo __("Activate Workflow process ?") ;?></label>
			</div>
			<div class="changed-data-set">				
				<input type="submit" id="settingSave" class="button-primary" value="<?php echo __("Save") ;?>" />			
			</div>
		</div>
		<input type="hidden" name="page_action" value = "<?php echo __("submit");?>" />
	</form>
</div>
<script type='text/javascript'>
jQuery(document).ready(function($) {
	jQuery("#chk_reminder_day").click(function(){
		if(jQuery(this).attr("checked") == "checked"){
			jQuery("#div_reminder_day").css("opacity", 1) ;
			jQuery("#reminder_days").attr("disabled", false) ;
		}else{
			jQuery("#div_reminder_day").css("opacity", 0.5) ;
			jQuery("#reminder_days").attr("disabled", true) ;
		}
	}) ;

	jQuery("#settingSave").click(function(){
		if( jQuery("#chk_reminder_day").attr("checked") == "checked" ){
			if( !jQuery("#reminder_days").val() ){
				alert("Please enter the number of days") ;
				return false;
			}
			if(isNaN(jQuery("#reminder_days").val())){
				alert("Please enter correctly the number of days") ;
				return false;
			}
		}		
	});
});
</script>