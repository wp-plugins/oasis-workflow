<?php 
global $chkResult;
$oasiswf = ($_GET["oasiswf"]) ? $_GET["oasiswf"] : $chkResult ;
$editable = current_user_can('edit_posts') ;
$parent_page = ( $_GET["parent_page"] ) ? $_GET["parent_page"] : "post_edit" ; //check to be called from which page 
if( $oasiswf ){
	$current_action = FCProcessFlow::get_action( array( "ID" => $oasiswf ) ) ;	
	$current_step = FCProcessFlow::get_step( array( "ID" => $current_action->step_id ) );
	$process = FCProcessFlow::get_gpid_dbid($current_step->workflow_id, $current_action->step_id, "process" );
	//$workflow = FCProcessFlow::get_workflow(array("ID" => $current_step->workflow_id)) ;
	$success_status = json_decode($current_step->step_info) ;
	$success_status = $success_status->status ;
}
?>
<div class="info-setting" id="new-step-submit-div" style="display:none;">
	<div class="dialog-title"><strong><?php echo __("Sign Off") ;?></strong></div>
	<div id="message_div"></div>
	<div>					
		<div class="select-part">
			<label><?php echo __("Action : ") ;?></label>
			<select id="decision-select" style="width:200px;">
				<option></option>
				<option value="complete"><?php echo ( $process == "review" ) ? __("Approved") :  __("Complete") ;?></option>
				<option value="unable"><?php echo ( $process == "review" ) ? __("Reject") :  __("Unable to Complete") ;?></option>
			</select>	
			<br class="clear">
		</div>	

		<div id="immediately-div">	
			<?php if($success_status == "publish"):?>		
				<label><?php echo __("Publish");?> : </label>
				<input type="checkbox" id="immediately-chk" checked=true />&nbsp;&nbsp;<?php echo __("Immediately") ;?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span id="immediately-span" style="display:none;">
					<?php FCProcessFlow::get_immediately_content($success_status);?>
				</span>
				<br class="clear">	
			<?php endif;?>		
		</div>		

		<div id="sum_step_info">	
			<div class="select-info">
				<label><?php echo __("Select Step : ") ;?></label>
				<select id="step-select" name="step-select" style="width:150px;">
					<option></option>
				</select><span id="step-loading-span"></span>				
				<br class="clear">
			</div>
			<div id="one-actors-div" class="select-info">
				<label><?php echo __("Assign actor : ") ;?></label>
				<select id="actor-one-select" name="actor-one-select" style="width:150px;" real="assign-loading-span"></select>
				<span class="assign-loading-span">&nbsp;</span>
				<br class="clear">
			</div>
			<div id="multi-actors-div" class="select-info" style="height:120px;">				
				<label><?php echo __("Assign actor(s) :") ;?></label>				
				<div class="select-actors-div">
					<div class="select-actors-list" >
						<label><?php echo __("Available") ;?></label>
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
						<label><?php echo __("Assigned") ;?></label><br class="clear">
						<p>
							<select id="actors-set-select" name="actors-set-select" size=10></select>
						</p>
					</div>
				</div>					
			</div>						
			<div class="text-info" style="margin-top:30px;">
				<label style="float:left;margin-top:5px;"><?php echo __("Due Date : ") ;?></label>
				<div style="float:left;">
					<input class="date_input" id="due-date" value=""/>
			        <button class="date-clear"><?php echo __("clear") ;?></button>
				</div>
				<br class="clear">
			</div>
		</div>	
			<div class="text-info" id="comments-div">
				<label style="float:left;"><?php echo __("Comments : ") ;?></label>
				<div style="float:left;">
					<textarea id="comments" style="height:200px;width:400px;margin-top:10px;" ></textarea>
				</div>
				<br class="clear">
			</div>
		
		<div class="changed-data-set">
			<input type="button" id="submitSave" class="button-primary" value="<?php echo __("Sign Off") ;?>" />
			<input type="button" id="cancelSave" class="button-primary" value="<?php echo __("Sign Off") ;?>" />
			<input type="button" id="completeSave" class="button-primary" value="<?php echo __("Sign Off") ;?>" />
			<span>&nbsp;</span>
			<a href="#" id="submitCancel"><?php echo __("Cancel") ;?></a>			
		</div>
		<br class="clear">					
	</div>
	<input type="hidden" id="hi_post_id"  value="<?php echo $_GET["post"] ;?>" />
	<input type="hidden" id="hi_oasiswf_id" name="hi_oasiswf_id" value="<?php echo $oasiswf ;?>" />
	<input type="hidden" id="hi_editable" value="<?php echo $editable ;?>" />
	<input type="hidden" id="hi_parrent_page" value="<?php echo $parent_page ;?>" />
	<input type="hidden" id="hi_current_process" value="<?php echo $process ;?>" />
</div>