<?php
if ( isset( $_POST['submit'] ) ) {
   // owf_submit_settings
   if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'owf_workflow_terminology_settings_nonce' ) ) {
      return false;
   }

   $submit_to_workflow = sanitize_text_field( $_POST['action_submit'] );
   $sign_off = sanitize_text_field( $_POST['sign_off'] );
   $assign_actors = sanitize_text_field( $_POST['assign_actors'] );
   $due_date = sanitize_text_field( $_POST['due_date'] );
   $publish_date = sanitize_text_field( $_POST['publish_date'] );
   $abort_workflow = sanitize_text_field( $_POST['abort_workflow'] );
   $workflow_history = sanitize_text_field( $_POST['workflow_history'] );
   $oasiswf_custom_workflow_terminology = array(
       'submitToWorkflowText' => $submit_to_workflow,
       'signOffText' => $sign_off,
       'assignActorsText' => $assign_actors,
       'dueDateText' => $due_date,
       'publishDateText' => $publish_date,
       'abortWorkflowText' => $abort_workflow,
       'workflowHistoryText' => $workflow_history
   );

   update_site_option( 'oasiswf_custom_workflow_terminology', $oasiswf_custom_workflow_terminology );
}

$oasiswf_custom_workflow_terminology = get_site_option( 'oasiswf_custom_workflow_terminology' );
$submit_to_workflow = !empty( $oasiswf_custom_workflow_terminology['submitToWorkflowText'] ) ? $oasiswf_custom_workflow_terminology['submitToWorkflowText'] : __( 'Submit to Workflow', 'oasisworkflow' );
$sign_off = !empty( $oasiswf_custom_workflow_terminology['signOffText'] ) ? $oasiswf_custom_workflow_terminology['signOffText'] : __( 'Sign Off', 'oasisworkflow' );
$assign_actors = !empty( $oasiswf_custom_workflow_terminology['assignActorsText'] ) ? $oasiswf_custom_workflow_terminology['assignActorsText'] : __( 'Assign Actor(s)', 'oasisworkflow' );
$due_date = !empty( $oasiswf_custom_workflow_terminology['dueDateText'] ) ? $oasiswf_custom_workflow_terminology['dueDateText'] : __( 'Due Date', 'oasisworkflow' );
$publish_date = !empty( $oasiswf_custom_workflow_terminology['publishDateText'] ) ? $oasiswf_custom_workflow_terminology['publishDateText'] : __( 'Publish Date', 'oasisworkflow' );
$abort_workflow = !empty( $oasiswf_custom_workflow_terminology['abortWorkflowText'] ) ? $oasiswf_custom_workflow_terminology['abortWorkflowText'] : __( 'Abort Workflow', 'oasisworkflow' );
$workflow_history = !empty( $oasiswf_custom_workflow_terminology['workflowHistoryText'] ) ? $oasiswf_custom_workflow_terminology['workflowHistoryText'] : __( 'Workflow History' );
?>
<div class="wrap">
    <?php if ( isset( $_POST['submit'] ) ) : ?>
       <div class="message">
           <?php _e( 'Settings saved successfully', 'oasisworkflow' ); ?>
       </div>
    <?php endif; ?>

    <form method="post" novalidate="novalidate">
    	<div id="workflow-terminology-setting">
			<div id="settingstuff">
        		<div class="select-info">
        			<div class="half-width left">
                   <label for="action_submit"><?php echo __( "Submit to Workflow", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
	             </div>
	             <div class="half-width left">   
                	<input type="text" class="regular-text" id="action_submit" name="action_submit" value="<?php echo $submit_to_workflow; ?>" />
                	<p class="description" id="tagline-description"><?php echo __( "Label for \"Submit to Workflow\" button/link." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>
        		<div class="select-info">
        			<div class="half-width left">
                    <label for="sign_off"><?php echo __( "Sign Off", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
                </div>
                <div class="half-width left">
                    <input type="text" class="regular-text" id="sign_off" name="sign_off" value="<?php echo $sign_off; ?>" />
                    <p class="description" id="tagline-description"><?php echo __( "Label for \"Sign Off\" button/link." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>
            <div class="select-info">
                <div class="half-width left">
                    <label for="assign_actors"><?php echo __( "Assign Actor(s)", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
                </div>
                <div class="half-width left">
                    <input type="text" class="regular-text" id="assign_actors" name="assign_actors" value="<?php echo $assign_actors; ?>" />
                    <p class="description" id="tagline-description"><?php echo __( "Label for \"Assign Actor(s)\" field." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>
            <div class="select-info">
                <div class="half-width left">
                    <label for="due_date"><?php echo __( "Due Date", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
                </div>
                <div class="half-width left">
                    <input type="text" class="regular-text" id="due_date" name="due_date" value="<?php echo $due_date; ?>" />
                    <p class="description" id="tagline-description"><?php echo __( "Label for \"Due Date\" field." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>
            <div class="select-info">
                <div class="half-width left">
                    <label for="publish_date"><?php echo __( "Publish Date", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
                </div>
                <div class="half-width left">
                    <input type="text" class="regular-text" id="publish_date" name="publish_date" value="<?php echo $publish_date; ?>" />
                    <p class="description" id="tagline-description"><?php echo __( "Label for \"Publish Date\" field on the \"Submit to Workflow\" popup." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>
            <div class="select-info">
                <div class="half-width left">
                    <label for="abort_workflow"><?php echo __( "Abort Workflow", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
                </div>
                <div class="half-width left">
                    <input type="text" class="regular-text" id="abort_workflow" name="abort_workflow" value="<?php echo $abort_workflow; ?>" />
                    <p class="description" id="tagline-description"><?php echo __( "Label for \"Abort Workflow\" button/link." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>
            <div class="select-info">
                <div class="half-width left">
                    <label for="workflow_history"><?php echo __( "Workflow History", "oasisworkflow" ) . __( " Label", "oasisworkflow" ) ?></label>
                </div>
                <div class="half-width left">
                    <input type="text" class="regular-text" id="workflow_history" name="workflow_history" value="<?php echo $workflow_history; ?>" />
                    <p class="description" id="tagline-description"><?php echo __( "Label for \"Workflow History\" menu." , "oasisworkflow"); ?></p>
                </div>
                <br class="clear">
            </div>            
			
        		<?php wp_nonce_field( 'owf_workflow_terminology_settings_nonce' ); ?>
        		<?php submit_button(); ?>
        	</div>
    	</div>    		
    </form>
	<?php 
	include( OASISWF_PATH . "includes/pages/about-us.php" ) ;
	?>
</div>	