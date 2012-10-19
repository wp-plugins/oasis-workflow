<?php $workflow = FCProcessFlow::get_workflow( array( "is_valid" => 1 ) ) ;?>				
<div class="info-setting" id="new-workflow-submit-div">
	<div class="dialog-title"><strong><?php echo __("Submit") ;?></strong></div>
	<div>					
		<div class="select-part">
			<label><?php echo __("Workflow : ") ;?></label>
			<select id="workflow-select" style="width:200px;">
				<option></option>
				<?php 
				if( $workflow ){
					foreach ($workflow as $row) {
						if( FCProcessFlow::check_submit_wf_editable( $row->ID ) ){
							if( $row->version== 1 )
								echo "<option value={$row->ID}>" . $row->name . "</option>" ;
							else
								echo "<option value={$row->ID}>" . $row->name . " (" . $row->version . ")" . "</option>" ;
						}
					}								
				}
				?>
			</select>			
			<br class="clear">
		</div>
		<div class="select-info">
			<label><?php echo __("Step : ") ;?></label>
			<select id="step-select" name="step-select" style="width:150px;" real="step-loading-span" disabled="true"></select>
			<span id="step-loading-span"></span>			
			<br class="clear">
		</div>
		
		<div id="one-actors-div" class="select-info">
			<label><?php echo __("Assign actor : ") ;?></label>
			<select id="actor-one-select" name="actor-one-select" style="width:150px;" real="assign-loading-span"></select>
			<span class="assign-loading-span">&nbsp;</span>
			<br class="clear">
		</div>
		
		<div id="multipule-actors-div" class="select-info" style="height:140px;">				
			<label><?php echo __("Assign actor(s) :") ;?></label>				
			<div class="select-actors-div">
				<div class="select-actors-list" >
					<label><?php echo __("Available") ;?></label>
					<span class="assign-loading-span" style="float:right;">&nbsp;</span><br>
					
					<p>
						<select id="actors-list-select" name="actors-list-select" size=10></select>
					</p>
				</div>
				<div class="select-actors-div-point">
					<a href="#" id="assignee-set-point"><img src="<?php echo OASISWF_URL . "img/role-set.png";?>" style="border:0px;" /></a><br><br>
					<a href="#" id="assignee-unset-point"><img src="<?php echo OASISWF_URL . "img/role-unset.png";?>" style="border:0px;" /></a>
				</div>
				<div class="select-actors-list">
					<label><?php echo __("Assigned") ;?></label><br>
					<p>
						<select id="actors-set-select" name="actors-set-select" size=10></select>
					</p>
				</div>
			</div>
			<br class="clear">					
		</div>					
		<div class="text-info">
			<label style="float:left;margin-top:5px;"><?php echo __("Due Date : ") ;?></label>
			<div style="float:left;">
				<input class="date_input" name="due-date" id="due-date"  />
		        <button class="date-clear" ><?php echo __("clear") ;?></button>
			</div>
			<br class="clear">
		</div>
		<div class="text-info" id="comments-div">
			<label style="float:left;"><?php echo __("Comments : ") ;?></label>
			<div style="float:left;">
				<textarea id="comments" style="height:200px;width:400px;margin-top:10px;" ></textarea>
			</div>
			<br class="clear">
		</div>
		<div class="changed-data-set">			
			<input type="button" id="submitSave" class="button-primary" value="<?php echo __("Submit") ;?>" />
			<span>&nbsp;</span>			
			<a href="#" id="submitCancel"><?php echo __("Cancel") ;?></a>				
		</div>
		<br class="clear">					
	</div>				
</div>