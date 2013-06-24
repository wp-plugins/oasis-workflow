<?php
global $workflow_message;
$wfid = "";
$workflow = "";
$wfeditable = true;
if( isset($_GET['wf_id']) && $_GET["wf_id"] ) {
	$wfid = $_GET["wf_id"] ;
	$workflow = $create_workflow->get_workflow_by_id( $wfid  );
	$wfeditable = $create_workflow->is_wf_editable( $_GET["wf_id"] ) ; // check editable.

	if( isset($_POST['save_action']) && !$_POST["save_action"] ){
		$workflow_message = FCWorkflowValidate::check_workflow_validate($wfid)	;
	}
}
$workflow_info = "";
if (is_object($workflow)) {
	$workflow_info = $workflow->wf_info;
}
echo "<script type='text/javascript'>
		wf_structure_data = '{$workflow_info}';
		wfeditable = '{$wfeditable}' ;
	</script>";
?>
<div class="wrap">
	<div id="workflow-edit-icon" class="icon32"><br></div>
	<?php
	if (is_object($workflow)){?>
		<h2><label id="page_top_lbl"><?php echo $workflow->name . " (" . $workflow->version .")" ;?></label></h2>
	<?php }?>
	<form id="wf-form" method="post" action="<?php echo network_admin_url('admin.php?page=oasiswf-admin');?>" >
		<div style="margin-bottom:10px;">
			<div id='fc_message' <?php echo  ($workflow_message) ? "class='updated fc_error_message'" : "";?> >
				<p><?php echo $workflow_message ; ?></p>
			</div>
			<br class="clear" />
		</div>
		<div class="fc_action">
			<div id="workflow-info-area">
				<div class="postbox-container"  id="process-info-div">
					<div class="postbox" >
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 style="padding:7px;">
							<span class="process-lbl"><?php echo __('Processes', "oasisworkflow");?></span>
						</h3>
						<div class="move-div">
							<?php
								if($wfeditable){
									echo '<ul id="wfsortable">';
									$fw_process = get_site_option('oasiswf_process');
									foreach ($fw_process as $k => $v) {
										echo "<li class='widget'>
												<div class='widget-wf-process'>" . __($k, "oasisworkflow") . "</div>
											 </li>";
									}
									echo '</ul>';
								}else{
									echo "<ul class='wfeditable'><li class='widget wfmessage'><p>";
									echo __("Processes are not available, since there are items (post/pages) in the workflow.&nbsp;&nbsp;&nbsp; If you want to edit the workflow,&nbsp;&nbsp; please ", "oasisworkflow") . "&nbsp;<a href='#' id='save_as_link'>" . __("save it as a new version", "oasisworkflow");
									echo "</a></p></li><ul>";
								}
							?>
						</div>
					</div>
				</div>
				<div class="postbox-container">
					<div class="postbox" >
						<div class="handlediv" title="Click to toggle"><br></div>
						<h3 style="padding:7px;">
							<span class="workflow-lbl"><?php echo  __("Workflow Info", "oasisworkflow");?></span>
						</h3>
						<?php
							$title = "";
							$dec = "";
							$startdate = "";
							$enddate = "";
							$auto_submit = 0;
							$auto_submit_keywords = "";
							if($workflow){
								$title = $workflow->name;
								$dec = $workflow->description;
								$startdate = $create_workflow->format_date_for_display( $workflow->start_date );
								$enddate = $create_workflow->format_date_for_display( $workflow->end_date );
								if( !$startdate && !$enddate ){
									$pre_version_end_date = $create_workflow->get_previous_workflow_version($wfid, "end_date") ;
									$able_start_date = "";
									if( $pre_version_end_date )
									{
										$able_start_date = $create_workflow->get_pre_next_date( $pre_version_end_date ) ;
									}
									$startdate = $create_workflow->format_date_for_display( $able_start_date ) ;
								}
								/*
								$auto_submit = $workflow->is_auto_submit;
								$keyword_array = @unserialize( $workflow->auto_submit_keywords );
								if ($keyword_array !== false)
								{
									$auto_submit_keywords = implode(',', $keyword_array['keywords']);
								}
								*/
							}
						?>
						<div class="move-div" id="workflow-define-div">
							<table>
								<tr>
									<td>
										<label><?php echo  __("Title : ", "oasisworkflow");?></label>
									</td>
								</tr>
								<tr>
									<td>
										<input type="text"  id="define-workflow-title" name="define-workflow-title" style="width:100%;"  value="<?php echo $title;?>"  />
									</td>
								</tr>
								<tr height="20px;"><td>&nbsp;</td><td>&nbsp;</td></tr>
								<tr>
									<td style="vertical-align: top;">
										<label><?php echo  __("Description : ", "oasisworkflow");?></label>
									</td>
								</tr>
								<tr>
									<td>
										<textarea id="define-workflow-description" name="define-workflow-description" class="define-workflow-textarea"
									 		cols="20" rows="10"><?php echo $dec;?></textarea>
									</td>
								</tr>
							</table>
							<div class="div-line"></div>
							<table>
								<tr>
									<td width="50%"><label><?php echo  __("Start Date :", "oasisworkflow");?></label></td>
									<td><label><?php echo  __("End date :", "oasisworkflow");?></label></td>
								</tr>
								<tr><td></td><td></td></tr>
								<tr>
									<td>
										<input class="date_input" id="start-date" name="start-date" readonly value="<?php echo $startdate ;?>" />
										<?php if($wfeditable):?>
											<button class="date-clear"><?php echo __("clear", "oasisworkflow") ;?></button>
											<script type="text/javascript">jQuery(function() {jQuery( "#start-date" ).datepicker({onSelect: function(dateText, inst) {jQuery(this).css("background-color", "white");}});});</script>
										<?php endif;?>
									</td>
									<td>
										<input class="date_input" id="end-date" name="end-date" readonly value="<?php echo $enddate ;?>" />
										<button class="date-clear"><?php echo __("clear", "oasisworkflow") ;?></button>
										<script type="text/javascript">jQuery(function() {jQuery( "#end-date" ).datepicker({onSelect: function(dateText, inst) {jQuery(this).css("background-color", "white");}});});</script>
									</td>
								</tr>
							</table>
							<!--
							<div class="div-line"></div>
							<table>
								<tr>
									<td colspan="2">
									   <?php
   				                  $str="" ;
   				                  if( $auto_submit == 1 )$str = "checked=true" ;
   				               ?>
   									<label><input type="checkbox" name="auto-submit"
   											value="1" <?php echo $str;?> />&nbsp;&nbsp;<?php echo __("Enable Auto Submit?", "oasisworkflow") ;?>
   									</label>
									</td>
								</tr>
								<tr height="20px;"><td>&nbsp;</td><td>&nbsp;</td></tr>
								<tr>
									<td style="vertical-align: top;">
										<label><?php echo  __("Keywords for Auto Submit : <br>&nbsp;(comma separated) ", "oasisworkflow");?></label>
									</td>
								</tr>
								<tr>
									<td>
										<textarea id="auto-submit-keywords" name="auto-submit-keywords" class="define-workflow-textarea"
									 		cols="20" rows="5"><?php echo $auto_submit_keywords;?></textarea>
									</td>
								</tr>
							</table>
							-->
						</div>
					</div>
				</div>
			</div>
			<div class="widget-holder dropable-area" id="workflow-area" style="position:relative;"></div>
			<br class="clear">
		</div>
		<div class="save-action-div">
			<?php if($wfeditable){?>
				<input type="button" value="<?php echo __("Save", "oasisworkflow") ?>" class="button-primary workflow-save-bt" >
				<span class="save_loading">&nbsp;</span>
				<a href="#" id="delete-form"><?php echo __("Clear Workflow", "oasisworkflow") ?></a>
			<?php }else{?>
				<input type="button" value="<?php echo __("Save as new version", "oasisworkflow") ?>" class="button-primary workflow-assave-bt" >
				<input type="button" value="<?php echo __("Save", "oasisworkflow") ?>" class="button-primary workflow-save-bt" >
				<span class="save_loading">&nbsp;</span>
			<?php }?>
		</div>
		<br class="clear" />
		<input type="hidden" id="wf_graphic_data_hi" name="wf_graphic_data_hi" />
		<input type="hidden" id="wf_id" name="wf_id" value='<?php echo $wfid; ?>' />
		<input type="hidden" id="deleted_step_ids" name="deleted_step_ids" />
		<input type="hidden" id="first_step" name="first_step" value="" />
		<input type="hidden" id="wf_validate_result" name="wf_validate_result" value="active" />
		<input type="hidden" id="save_action" name="save_action" value="workflow_save" />
	</form>
</div>
<?php
echo "<div id='connection-setting'>{$create_workflow->connection_setting_html()}</div>" ;?>
<ul id="connectionMenu" class="contextMenu">
	<div>Conn Menu</div>
	<li class="edit" id="connEdit" ><a href="#edit"><?php echo __("Edit", "oasisworkflow") ?></a></li>
	<li class="delete" id="connDelete"><a href="#delete"><?php echo __("Delete", "oasisworkflow") ?></a></li>
	<li class="quit separator" id="connQuit"><a href="#quit"><?php echo __("Quit", "oasisworkflow") ?></a></li>
</ul>
<ul id="stepMenu" class="contextMenu">
	<div>Step Menu</div>
	<li class="edit" id="stepEdit">
		<a class="thickbox"
			alt="<?php echo site_url('wp-load.php?wf-popup=step');?>"><?php echo __("Edit", "oasisworkflow") ?></a></li>
	<?php if($wfeditable):?>
		<li class="delete" id="stepDelete"><a href="#delete"><?php echo __("Delete", "oasisworkflow") ?></a></li>
	<?php endif;?>
	<li class="quit separator" id="stepQuit"><a href="#quit"><?php echo __("Quit", "oasisworkflow") ?></a></li>
</ul>
<?php echo "<div id='new-workflow-create-check'>{$create_workflow->new_workflow_create_check_html()}</div>" ;?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		//----------loading modal--------------
		if(!jQuery("#wf_id").val()){
			if(navigator.appName == "Netscape"){
				show_workflow_create_modal() ;
			}else{
				setTimeout("show_workflow_create_modal()", 500);
			}
		}
	});
	//-------------------------------------
	jQuery("#wpbody").css({"position":"inherit"});
	function call_modal(param){
		jQuery('.contextMenu').hide();
		jQuery('#'+param).modal();
	}

	function show_workflow_create_modal(){
		jQuery('#new-workflow-create-check').modal();
		jQuery(".modalCloseImg").hide() ;
	}
</script>