<?php
if( isset($_POST['page_action']) && $_POST["page_action"] == "submit" ){
	update_option("oasiswf_reminder_days", $_POST["reminder_days"]) ;
	update_option("activate_workflow", $_POST["activate_workflow_process"]) ;
}
$reminder_day = get_option('oasiswf_reminder_days') ;
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Settings"); ?></h2>
	<?php if( isset($_POST['page_action']) && $_POST["page_action"] == "submit" ):?>
		<div class="message"><?php echo __("Settings saved successfully.");?></div>
	<?php endif;?>
	<form id="setting_form" method="post">
		<div id="workflow-setting">
			<div id="settingstuff">
				<div class="select-info" style="padding: 10px;">
					<label>
						<input type="checkbox" id="chk_reminder_day"	<?php echo ($reminder_day) ? "checked" : "" ;?> />&nbsp;&nbsp;<?php echo __(" Send Reminder Emails ?") ;?>
					</label>
					<div id="div_reminder_day" style="<?php echo ($reminder_day) ? "opacity:1" : "opacity:0.5" ;?>" >
					<?php echo __("Send reminder email");?>
						<input type="text" id="reminder_days" name="reminder_days"
							value="<?php echo $reminder_day;?>" maxlength=2 />
							<?php echo __("days before due date.");?>
					</div>
				</div>
				<div class="select-info" style="padding: 10px;">
				<?php
				$str="" ;
				if( get_option("activate_workflow") == "active" )$str = "checked=true" ;
				?>
					<label><input type="checkbox" name="activate_workflow_process"
						value="active" <?php echo $str;?> />&nbsp;&nbsp;<?php echo __("Activate Workflow process ?") ;?>
					</label>
				</div>
				<div class="changed-data-set">
					<input type="submit" id="settingSave"
						class="button-primary owf-button"
						value="<?php echo __("Save") ;?>" /> <input type="hidden"
						name="page_action" value="<?php echo __("submit");?>" />
				</div>
			</div>
	
	</form>
	<div id="poststuff">
		<div class="owf-sidebar">
			<div class="postbox" style="float: left;">
				<h3 style="cursor: default;">
					<span><?php _e("About this Plugin:", 'owf'); ?> </span>
				</h3>
				<div class="inside">
					<a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . '/img/nugget-solutions.png'; ?>);" target="_blank" href="http://www.nuggetsolutions.com/"><?php _e("Author's website", 'owf'); ?>
					</a> <a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . 'img/publish.gif'; ?>);" target="_blank" href="http://oasisworkflow.com/"><?php _e('Plugin webpage', 'owf'); ?>
					</a> <a class="owf_about_link" style="background-image:url(<?php echo OASISWF_URL . '/img/faq-icon.png'; ?>);" target="_blank" href="http://oasisworkflow.com/faq/"><?php _e('FAQ', 'owf'); ?>
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
