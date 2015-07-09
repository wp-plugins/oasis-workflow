<?php
class FCWorkflowActions
{
	function __construct()
	{
		add_action( 'admin_footer', array( 'FCWorkflowActions' , 'step_signoff_popup' ) ) ;
		add_filter( 'redirect_post_location', array('FCWorkflowActions', 'workflow_submit_save' ), '', 2 ) ;
		add_action( 'admin_menu', array( 'FCWorkflowActions', 'create_meta_box' ) );
		add_action( 'oasiswf_email_schedule', array( 'FCWorkflowActions', 'send_reminder_email' ) ) ;
		add_action( 'wp_trash_post', array( 'FCWorkflowActions', 'when_post_trash_delete' ) ) ;
		add_filter( 'get_edit_post_link', array( 'FCWorkflowActions', 'oasis_edit_post_link' ), '', 3 );
	}

	static function create_meta_box(){

		global $chkResult ;

		$selected_user = isset($_GET['user']) ? $_GET['user'] : get_current_user_id();
		$chkResult = FCProcessFlow::workflow_submit_check($selected_user);

		if( $chkResult && $chkResult != "submit" && $chkResult != "inbox" ){
			$post = get_post( $_GET["post"] ) ;
			$mbox = array(
			    'id' => 'graphic',
			    'title' => 'Workflow',
			    'page' => $post->post_type,
			    'context' => 'normal',
			    'priority' => 'high'
			    );
		    // TODO: this might cause issues with other plugins - compatibility issues - for the time being this feature is turned off.
			if( get_site_option("oasiswf_hide_workflow_graphic") !== "yes") {
		    	add_meta_box($mbox['id'], $mbox['title'], array('FCWorkflowActions','history_graphic_box'), $mbox['page'], $mbox['context'], $mbox['priority']);
			}
		}
	}

	static function history_graphic_box(){
		include( OASISWF_PATH . "includes/pages/subpages/history-graphic.php" ) ;
	}

	static function workflow_submit_popup()
	{
		if( get_site_option("oasiswf_activate_workflow") == "active" &&
		   is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches )){
		   
			$is_post_published = false;
			if( isset($_GET['post']) && $_GET["post"] && isset($_GET['action']) && $_GET["action"] == "edit")
			{
				$post_status = get_post_status( $_GET["post"]);
				if ($post_status == "publish" || $post_status == "future") {
					$is_post_published = true;
				}
			}

			if ( !$is_post_published ) {	
				wp_enqueue_script( 'owf_submit_workflow',
								   OASISWF_URL. 'js/pages/subpages/submit-workflow.js',
								   array('jquery'),
								   OASISWF_VERSION,
								   true);
				FCWorkflowActions::localize_submit_workflow_script();
			}
			$role = FCProcessFlow::get_current_user_role() ;
			$skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
			$show_workflow_for_post_types = get_site_option('oasiswf_show_wfsettings_on_post_types');
		   if( (is_array($skip_workflow_roles) && !in_array($role, $skip_workflow_roles)) && // do not hide the ootb publish section for skip_workflow_roles option
         (is_array(show_workflow_for_post_types) && in_array($post_type, show_workflow_for_post_types))){ // do not show ootb publish section for oasiswf_show_wfsettings_on_post_types
            FCWorkflowActions::ootb_publish_section_hide() ;
         }
		}
	}

	static function step_signoff_popup()
	{
		global $wpdb, $chkResult;
		$selected_user = isset($_GET['user']) ? $_GET["user"] : get_current_user_id();
		$chkResult = FCProcessFlow::workflow_submit_check($selected_user);
		$is_post_published = false;
		if( isset($_GET['post']) && $_GET["post"] && isset($_GET['action']) && $_GET["action"] == "edit")
		{
			$post_status = get_post_status( $_GET["post"]);
			if ($post_status == "publish" || $post_status == "future") {
				$is_post_published = true;
			}
		}		
		if( get_site_option("oasiswf_activate_workflow") == "active" &&
		   is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches )){

			if( $chkResult == "inbox" ){
            wp_enqueue_script( 'owf_submit_step',
                        OASISWF_URL. 'js/pages/subpages/submit-step.js',
                         array('jquery'),
                         OASISWF_VERSION,
                         true);
            FCWorkflowActions::localize_submit_step_script();
			}
		   else if( $chkResult == "submit" &&
		      is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches )
			  && !$is_post_published){
			   include( OASISWF_PATH . "includes/pages/subpages/submit-workflow.php" ) ;
			   wp_enqueue_script( 'owf_submit_workflow',
			                   OASISWF_URL. 'js/pages/subpages/submit-workflow.js',
			                   array('jquery'),
			                   OASISWF_VERSION,
			                   true);
            FCWorkflowActions::localize_submit_workflow_script();
			}else{
				if( is_numeric( $chkResult ) &&
				   is_admin() && preg_match_all('/page=oasiswf(.*)|post-new\.(.*)|post\.(.*)/', $_SERVER['REQUEST_URI'], $matches )){
				   include( OASISWF_PATH . "includes/pages/subpages/submit-step.php" ) ;
               wp_enqueue_script( 'owf_submit_step',
                           OASISWF_URL. 'js/pages/subpages/submit-step.js',
                            array('jquery'),
                            OASISWF_VERSION,
                            true);
               FCWorkflowActions::localize_submit_step_script();

				}
			}

         $role = FCProcessFlow::get_current_user_role() ;
         $post_type = get_post_type();

         // do not hide the ootb publish section for skip_workflow_roles option, but hide it if the post is in the workflow
         $skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
         $show_workflow_for_post_types = get_site_option('oasiswf_show_wfsettings_on_post_types');
         $row = null;
         if( isset($_GET['post']) && $_GET["post"] && isset($_GET['action']) && $_GET["action"] == "edit")
         {
            $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . FCUtility::get_action_history_table_name() . " WHERE post_id = %d AND action_status = 'assignment'", $_GET["post"] )) ;
         }
		 
		   if( !$is_post_published && (is_array($skip_workflow_roles) && !in_array($role, $skip_workflow_roles)) && // do not hide the ootb publish section for skip_workflow_roles option
         	(is_array($show_workflow_for_post_types) && in_array($post_type, $show_workflow_for_post_types))){ // do not show ootb publish section for oasiswf_show_wfsettings_on_post_types
            FCWorkflowActions::ootb_publish_section_hide() ;
         }

		   // if the item is in the workflow, hide the OOTB publish section
         if ( $row ) {
            FCWorkflowActions::ootb_publish_section_hide() ;
         }


			//--------generate abort workflow link---------

			if( $role == "administrator" ){
				if( $row ){
					echo "<script type='text/javascript'>var exit_wfid = $row->ID ;</script>" ;
               wp_enqueue_script( 'owf-abort-workflow',
                         OASISWF_URL. 'js/pages/subpages/exit.js',
                         '',
                   		 OASISWF_VERSION,
                         true);

               wp_localize_script( 'owf-abort-workflow', 'owf_abort_workflow_vars', array(
         						'abortWorkflow' => __( 'Abort workflow', 'oasisworkflow' ),
               				'abortWorkflowConfirm' => __( 'Are you sure to abort the workflow?', 'oasisworkflow' )
                       ));
				}
			}
		}
	}

	static function ootb_publish_section_hide()
	{
		echo "<script type='text/javascript'>
					jQuery(document).ready(function() {
						jQuery('#publish, .misc-pub-section-last').hide() ;
						jQuery('#misc-publishing-actions').children('.curtime').hide() ;
						jQuery('#post-status-display').parent().hide() ;
					});
				</script>";
	}


	static function workflow_submit_save($location, $postid)
	{
		if( isset($_POST["save_action"]) && $_POST["save_action"] == "submit_post_to_workflow" ){
			FCProcessFlow::submit_post_to_workflow() ;
		}

		if( isset($_POST["hi_process_info"]) && $_POST["hi_process_info"] ){
			FCProcessFlow::set_loading_post_status() ;
		}

		return $location;
	}

	// Add Oasis Workflow sign off buttons on the edit post link, if the item is in workflow
	static function oasis_edit_post_link($url, $post_id, $context)
	{
	   $new_url = $url;
	   $row = FCProcessFlow::get_assigned_post( $post_id, get_current_user_id(), "row" ) ;
		if($row){
			$new_url = $url . '&oasiswf=' . $row->ID;
		}
      return $new_url;
	}

	static function add_edit_role($allcaps, $caps, $args)
	{
		if( $_GET["post"] && $_GET["action"] == "edit"  )
		{
			$allcaps["edit_posts"] = 1 ;
			$allcaps["edit_published_posts"] = 1 ;
			$allcaps["edit_others_posts"] = 1 ;
			$allcaps["publish_posts"] = 1 ;
			$allcaps["upload_files"] = 1 ;
		}
		return $allcaps;
	}

   static function send_reminder_email()
   {
      global $wpdb;
      $emails_table = FCUtility::get_emails_table_name();
      if( !class_exists('FCProcessFlow') ){
         require_once( OASISWF_PATH . "includes/workflow-base.php" ) ;
         require_once( OASISWF_PATH . "includes/process-flow.php" ) ;
      }
      $email_settings = get_site_option('oasiswf_email_settings') ;
      if ( $email_settings['reminder_emails'] == "yes" ) {
      	$ddate = gmdate( 'Y-m-d' ) ;
      	$rows = $wpdb->get_results( "SELECT * FROM " . FCUtility::get_emails_table_name() . " WHERE action = 1 AND send_date = '$ddate'" ) ;
	      foreach ($rows as $row) {
	         FCWorkflowEmail::send_mail($row->to_user, $row->subject, $row->message) ;
	         $wpdb->update($emails_table, array("action" => 0), array("ID" => $row->ID)) ;
	      }
      }

   }

	static function when_post_trash_delete($postid)
	{
		global $wpdb;
		$histories = FCProcessFlow::get_action_history_by_post( $postid ) ;
		if( $histories )
		{
			foreach ($histories as $history) {
				$wpdb->get_results("DELETE FROM " . FCUtility::get_action_table_name() . " WHERE action_history_id = " . $history->ID) ;
				$wpdb->get_results("DELETE FROM " . FCUtility::get_emails_table_name() . " WHERE history_id = " . $history->ID) ;
			}
			$wpdb->get_results("DELETE FROM " . FCUtility::get_action_history_table_name() . " WHERE post_id = " . $postid) ;
			delete_post_meta($postid, 'oasis_is_in_workflow');
		}
	}

	static function redirect_after_signoff( $url ) {
   	if(isset($_POST['hi_oasiswf_redirect']) AND $_POST['hi_oasiswf_redirect']=='step')
   	{
   		wp_redirect(admin_url( 'admin.php?page=oasiswf-inbox' ));
   		die();
   	}
   	return $url;
	}

	static function localize_submit_workflow_script()
	{
      wp_localize_script( 'owf_submit_workflow', 'owf_submit_workflow_vars', array(
				'submitToWorkflowButton' => __( 'Submit to Workflow', 'oasisworkflow' ),
				'allStepsNotDefined' => __( 'All steps are not defined.\n Please check the workflow.', 'oasisworkflow' ),
				'notValidWorkflow' => __( 'The selected workflow is not valid.\n Please check this workflow.', 'oasisworkflow' ),
				'noUsersDefined' => __( 'No users found for the given role.', 'oasisworkflow' ),
				'multipleUsers' => __( 'You can select multiple users only for review step.\n Selected step is', 'oasisworkflow' ),
   			'step' => __( 'step.', 'oasisworkflow' ),
   			'selectWorkflow' => __( 'Please select a workflow.', 'oasisworkflow' ),
   			'selectStep' => __( 'Please select a step.', 'oasisworkflow' ),
            'stepNotDefined' => __( 'This step is not defined.', 'oasisworkflow' ),
            'dueDateRequired' => __( 'Please enter a due date.', 'oasisworkflow' ),
            'noAssignedActors' => __( 'No assigned actor(s).', 'oasisworkflow' ),
				'drdb' =>  get_site_option('oasiswf_reminder_days'),
				'drda' =>  get_site_option('oasiswf_reminder_days_after'),
				'dateFormat' => FCUtility::owf_date_format_to_jquery_ui_format( get_option( 'date_format' )),
				'editDateFormat' => FCUtility::owf_date_format_to_jquery_ui_format( OASISWF_EDIT_DATE_FORMAT ),
				'allowedPostTypes' => json_encode(get_site_option('oasiswf_show_wfsettings_on_post_types')),
				'defaultDueDays' =>  get_site_option('oasiswf_default_due_days')
				
      ));
	}

	static function localize_submit_step_script()
	{
      wp_localize_script( 'owf_submit_step', 'owf_submit_step_vars', array(
				'signOffButton' => __( 'Sign Off', 'oasisworkflow' ),
				'claimButton' => __( 'Claim', 'oasisworkflow' ),
				'inboxButton' => __( 'Go to Workflow Inbox', 'oasisworkflow' ),
				'firstStepMessage' => __( 'This is the first step in the workflow.</br> Do you really want to cancel the post/page from the workflow?', 'oasisworkflow' ),
				'lastStepMessage' => __( 'This is the last step in the workflow. Are you sure to complete the workflow?', 'oasisworkflow' ),
				'noUsersFound' => __( 'No users found for the given role.', 'oasisworkflow' ),
   			'decisionSelectMessage' => __( 'Please select an action.', 'oasisworkflow' ),
   			'selectStep' => __( 'Please select a step.', 'oasisworkflow' ),
            'dueDateRequired' => __( 'Please enter a due date.', 'oasisworkflow' ),
            'noAssignedActors' => __( 'No assigned actor(s).', 'oasisworkflow' ),
				'multipleUsers' => __( 'You can select multiple users only for review step.\n Selected step is', 'oasisworkflow' ),
				'step' => __( 'step.', 'oasisworkflow' ),
      		'drdb' =>  get_site_option('oasiswf_reminder_days'),
				'drda' =>  get_site_option('oasiswf_reminder_days_after'),
				'dateFormat' => FCUtility::owf_date_format_to_jquery_ui_format( get_option( 'date_format' )),
				'editDateFormat' => FCUtility::owf_date_format_to_jquery_ui_format( OASISWF_EDIT_DATE_FORMAT ),
				'allowedPostTypes' => json_encode(get_site_option('oasiswf_show_wfsettings_on_post_types')),
				'defaultDueDays' =>  get_site_option('oasiswf_default_due_days')
      ));
	}
	
	static function owf_submit_to_workflow_hook_test($postId, $workflowId) {
		FCUtility::owf_logger("this is testing the submit to workflow hook");
		FCUtility::owf_logger("PostID:" . $postId);
		FCUtility::owf_logger("Workflow ID:" . $workflowId);
	}
	 
	static function owf_step_sign_off_hook_test($postId, $workflowId, $fromStepId, $toStepId) {
		FCUtility::owf_logger("this is testing the step sign off workflow hook");
		FCUtility::owf_logger("PostID:" . $postId);
		FCUtility::owf_logger("Workflow ID:" . $workflowId);
		FCUtility::owf_logger("From Step ID:" . $fromStepId);
		FCUtility::owf_logger("To Step ID:" . $toStepId);
	}
	
	static function owf_workflow_complete_hook_test($postId, $workflowId) {
		FCUtility::owf_logger("this is testing the workflow completion hook");
		FCUtility::owf_logger("PostID:" . $postId);
		FCUtility::owf_logger("Workflow ID:" . $workflowId);
	}	
}
add_action('wp_ajax_get_first_step_in_wf', array( 'FCProcessFlow', 'get_first_step_in_wf' ) );
add_action('wp_ajax_get_pre_next_steps', array( 'FCProcessFlow', 'get_pre_next_steps' ) );
add_action('wp_ajax_submit_post_to_step', array( 'FCProcessFlow', 'submit_post_to_step' ) );
add_action('wp_ajax_get_users_in_step', array( 'FCProcessFlow', 'get_users_in_step' ) );
add_action('wp_ajax_change_workflow_status_to_complete', array( 'FCProcessFlow', 'change_workflow_status_to_complete' ) );
add_action('wp_ajax_change_workflow_status_to_cancelled', array( 'FCProcessFlow', 'change_workflow_status_to_cancelled' ) );
add_action('wp_ajax_exit_post_from_workflow', array( 'FCProcessFlow', 'exit_post_from_workflow' ) );
add_action('wp_ajax_get_step_status_by_history_id', array( 'FCProcessFlow', 'get_step_status_by_history_id' ) );
add_action('wp_ajax_get_step_status_by_step_id', array( 'FCProcessFlow', 'get_step_status_by_step_id' ) );
add_action('redirect_post_location', array( 'FCWorkflowActions', 'redirect_after_signoff' ) );
add_action('wp_ajax_get_post_publish_date', array( 'FCProcessFlow', 'get_post_publish_date' ));

?>