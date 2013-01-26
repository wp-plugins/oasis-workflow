<?php
class FCWorkflowActions
{
	function __construct()
	{
		add_action( 'admin_footer-post-new.php', array( 'FCWorkflowActions' , 'workflow_submit_popup' ) ) ;
		add_action( 'admin_footer-post.php', array( 'FCWorkflowActions' , 'step_signoff_popup' ) ) ;
		add_filter( 'redirect_post_location', array('FCWorkflowActions', 'workflow_submit_save' ), '', 2 ) ;
		//add_filter( 'user_has_cap', array( 'FCWorkflowActions', 'add_edit_role' ), '', 3 );
		add_action( 'admin_menu', array( 'FCWorkflowActions', 'create_meta_box' ) );
		add_action( 'oasiswf_email_schedule', array( 'FCWorkflowActions', 'send_reminder_email' ) ) ;
		add_action("trash_post", array( 'FCWorkflowActions', 'when_post_trash_delete' ) ) ;
		//add_action("deleted_post", array( 'FCWorkflowActions', 'when_post_trash_delete' ) ) ;
	}

	static function create_meta_box(){

		global $chkResult ;

		$chkResult = FCProcessFlow::post_page_check();

		if( $chkResult && $chkResult != "submit" ){
			$post = get_post( $_GET["post"] ) ;
			$mbox = array(
			    'id' => 'graphic',
			    'title' => 'Workflow',
			    'page' => $post->post_type,
			    'context' => 'normal',
			    'priority' => 'high'
			    );
			add_meta_box($mbox['id'], $mbox['title'], array('FCWorkflowActions','history_graphic_box'), $mbox['page'], $mbox['context'], $mbox['priority']);
		}
	}

	static function history_graphic_box(){
		include( OASISWF_PATH . "includes/pages/subpages/history-graphic.php" ) ;
	}

	static function include_files($page, $css=null, $js=null)
	{
		$css = ( $css ) ? $css : $page ;
		$js = ( $js ) ? $js : $page ;
		include( OASISWF_PATH . "includes/pages/subpages/{$page}.php" ) ;
		echo "<link rel='stylesheet' href='" . OASISWF_URL . "css/lib/modal/basic.css' type='text/css' />";
		echo "<link rel='stylesheet' href='" . OASISWF_URL . "css/lib/calendar/datepicker.css' type='text/css' />";
		echo "<link rel='stylesheet' href='" . OASISWF_URL . "css/pages/subpages/{$css}.css' type='text/css' />";
		echo "<script type='text/javascript' src = '" . admin_url('load-scripts.php?load=jquery-ui-core, jquery-ui-datepicker') . "' ></script>";
		echo "<script type='text/javascript' src='" . OASISWF_URL . "js/lib/modal/jquery.simplemodal.js' ></script>";
		echo "<script type='text/javascript' src='" . OASISWF_URL . "js/pages/subpages/parent.js' ></script>";
		echo "<script type='text/javascript' src='" . OASISWF_URL . "js/pages/subpages/{$js}.js' ></script>";
	}

	static function workflow_submit_popup()
	{
		if( get_option("activate_workflow") == "active" ){
			FCWorkflowActions::include_files( "submit-workflow" ) ;
			$role = FCProcessFlow::get_current_user_role() ;
			if( $role != "administrator" ){ // do not hide the ootb publish section for administrator
			   FCWorkflowActions::ootb_publish_section_hide() ;
			}
		}
	}

	static function step_signoff_popup()
	{
		global $wpdb, $chkResult;
		if( get_option("activate_workflow") == "active" ){
			if( $chkResult == "submit" ){
				FCWorkflowActions::include_files( "submit-workflow" ) ;
			}else{
				if( is_numeric( $chkResult ) ){
					FCWorkflowActions::include_files( "submit-step" ) ;
				}
			}

         $role = FCProcessFlow::get_current_user_role() ;
         $row = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}fc_action_history WHERE post_id = {$_GET["post"]} AND action_status = 'assignment'") ;

         // do not hide the ootb publish section for administrator, but hide it if the post is in the workflow
         if( ($role != "administrator") || ($role == "administrator" && $row) ){
            FCWorkflowActions::ootb_publish_section_hide() ;
         }

			//--------generate exit link---------

			if( $role == "administrator" ){
				if( $row ){
					echo "<link rel='stylesheet' href='" . OASISWF_URL . "css/pages/page.css' type='text/css' />";
					echo "<script type='text/javascript'>var exit_wfid = $row->ID ;</script>" ;
					echo "<script type='text/javascript' src='" . OASISWF_URL . "js/pages/subpages/exit.js' ></script>";

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
		$ddate = gmdate( 'Y-m-d' ) ;
		$rows = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_emails WHERE action = 1 AND send_date = '$ddate'" ) ;
		foreach ($rows as $row) {
			FCWorkflowEmail::send_mail($row->to_user, $row->subject, $row->message) ;
			$wpdb->update($emails_table, array("action" => 0), array("ID" => $row->ID)) ;
		}

	}

	static function when_post_trash_delete($postid)
	{
		global $wpdb;
		$histories = FCProcessFlow::get_action_history_by_post( $postid ) ;
		if( $histories )
		{
			foreach ($histories as $history) {
				$wpdb->get_results("DELETE FROM {$wpdb->prefix}fc_action WHERE action_history_id = " . $history->ID) ;
				$wpdb->get_results("DELETE FROM {$wpdb->prefix}fc_emails WHERE history_id = " . $history->ID) ;
			}
			$wpdb->get_results("DELETE FROM {$wpdb->prefix}fc_action_history WHERE post_id = " . $postid) ;
		}
	}
}
add_action('wp_ajax_get_first_step_in_wf', array( 'FCProcessFlow', 'get_first_step_in_wf' ) );
add_action('wp_ajax_get_pre_next_steps', array( 'FCProcessFlow', 'get_pre_next_steps' ) );
add_action('wp_ajax_submit_post_to_step', array( 'FCProcessFlow', 'submit_post_to_step' ) );
add_action('wp_ajax_get_user_in_step', array( 'FCProcessFlow', 'get_user_in_step' ) );
add_action('wp_ajax_change_workflow_status_to_complete', array( 'FCProcessFlow', 'change_workflow_status_to_complete' ) );
add_action('wp_ajax_change_workflow_status_to_cancelled', array( 'FCProcessFlow', 'change_workflow_status_to_cancelled' ) );
add_action('wp_ajax_exit_post_from_workflow', array( 'FCProcessFlow', 'exit_post_from_workflow' ) );
?>