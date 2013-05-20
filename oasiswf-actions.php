<?php
class FCWorkflowActions
{
	function __construct()
	{
		add_action( 'admin_footer-post-new.php', array( 'FCWorkflowActions' , 'workflow_submit_popup' ) ) ;
		add_action( 'admin_footer-post.php', array( 'FCWorkflowActions' , 'step_signoff_popup' ) ) ;
		add_filter( 'redirect_post_location', array('FCWorkflowActions', 'workflow_submit_save' ), '', 2 ) ;
		add_action( 'admin_menu', array( 'FCWorkflowActions', 'create_meta_box' ) );
		add_action( 'oasiswf_email_schedule', array( 'FCWorkflowActions', 'send_reminder_email' ) ) ;
		/* add_action( 'oasiswf_auto_submit_schedule', array( 'FCWorkflowActions', 'auto_submit_articles' ) ) ;*/
		add_action("trash_post", array( 'FCWorkflowActions', 'when_post_trash_delete' ) ) ;
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
		if( get_site_option("oasiswf_activate_workflow") == "active" ){
			FCWorkflowActions::include_files( "submit-workflow" ) ;
			$role = FCProcessFlow::get_current_user_role() ;
			$skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
			if( is_array($skip_workflow_roles) && !in_array($role, $skip_workflow_roles) ){ // do not hide the ootb publish section for skip_workflow_roles option
			   FCWorkflowActions::ootb_publish_section_hide() ;
			}
		}
	}

	static function step_signoff_popup()
	{
		global $wpdb, $chkResult;
		if( get_site_option("oasiswf_activate_workflow") == "active" ){
			if( $chkResult == "submit" ){
				FCWorkflowActions::include_files( "submit-workflow" ) ;
			}else{
				if( is_numeric( $chkResult ) ){
					FCWorkflowActions::include_files( "submit-step" ) ;
				}
			}

         $role = FCProcessFlow::get_current_user_role() ;
         $row = $wpdb->get_row("SELECT * FROM " . FCUtility::get_action_history_table_name() . " WHERE post_id = {$_GET["post"]} AND action_status = 'assignment'") ;

         // do not hide the ootb publish section for skip_workflow_roles option, but hide it if the post is in the workflow
         $skip_workflow_roles = get_site_option('oasiswf_skip_workflow_roles') ;
         if( (is_array($skip_workflow_roles) && !in_array($role, $skip_workflow_roles )) || $row){
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
		$rows = $wpdb->get_results( "SELECT * FROM " . FCUtility::get_emails_table_name() . " WHERE action = 1 AND send_date = '$ddate'" ) ;
		foreach ($rows as $row) {
			FCWorkflowEmail::send_mail($row->to_user, $row->subject, $row->message) ;
			$wpdb->update($emails_table, array("action" => 0), array("ID" => $row->ID)) ;
		}

	}

	static function auto_submit_articles()
	{
	   global $wpdb;
	   $workflows = FCWorkflowBase::get_workflow_by_auto_submit(1);
	   $auto_submit_settings = get_site_option('oasiswf_auto_submit_settings');

	   $auto_submit_stati = $auto_submit_settings['auto_submit_stati'];
	   foreach ($auto_submit_stati as $key => $status) // convert to a MySQL In list ('value1', 'value2')
	   {
	      $auto_submit_stati[$key] = "'" . mysql_real_escape_string($status) . "'";
	   }
	   $auto_submit_stati_list = join("," , $auto_submit_stati);
      $auto_submit_post_count = ($auto_submit_settings['auto_submit_post_count'] != null) ? $auto_submit_settings['auto_submit_post_count'] : "5";
      $auto_submit_due_days = ($auto_submit_settings['auto_submit_due_days'] != null) ? $auto_submit_settings['auto_submit_due_days'] : "1";

	   // get all posts which are in draft or pending
	   $unsubmitted_posts = $wpdb->get_results( "SELECT distinct posts.ID, posts.post_title FROM {$wpdb->prefix}posts posts
	   	WHERE posts.post_status in (" . $auto_submit_stati_list . ")
	   	AND
	   	(NOT EXISTS (SELECT * from {$wpdb->prefix}postmeta postmeta1 WHERE postmeta1.meta_key = 'oasis_is_in_workflow' and posts.ID = postmeta1.post_id) OR
	   	EXISTS (SELECT * from {$wpdb->prefix}postmeta postmeta2 WHERE postmeta2.meta_key = 'oasis_is_in_workflow' AND postmeta2.meta_value = '0' and posts.ID = postmeta2.post_id))
	   	order by post_modified_gmt
	   	limit 0, " . $auto_submit_post_count );

	   $submitted_posts_count = 0;

	   foreach ($workflows as $wf)
	   {
   		$keyword_array = @unserialize( $wf->auto_submit_keywords );
   		if ($keyword_array === false) // no keywords defined
   		{
   		   continue;
   		}
   		$auto_submit_keywords = explode(",", implode(',', $keyword_array['keywords']));
   		foreach($unsubmitted_posts as $i => $row)
   		{
   		   if(FCUtility::str_array_pos($row->post_title, $auto_submit_keywords))
   		   {
   		      // submit the post to workflow
               $steps = FCProcessFlow::get_first_step_in_wf_internal($wf->ID);

               $users = FCProcessFlow::get_users_in_step_internal($steps["first"][0][0]);
               $actors = "";
               foreach($users["users"] as $user)
               {
                  if($actors != "")
                  {
                     $actors .= "@";
                  }
                  $actors .= $user->ID;
               }

               $dueDate = FCProcessFlow::get_pre_next_date(date("m/d/Y"), "next", $auto_submit_due_days);
               $userComments = $auto_submit_settings['auto_submit_comment'];
               if ($actors != "")
               {
                  FCProcessFlow::submit_post_to_workflow_internal($steps["first"][0][0], $row->ID, $actors,
                     FCWorkflowBase::format_date_for_display($dueDate), $userComments);

                   // increment the count of successfully submitted posts
                   $submitted_posts_count++;

                  // remove the post from the list of unsubmitted posts
                  unset($unsubmitted_posts[$i]);
               }
   		   }
   		}
	   }

	   return $submitted_posts_count;
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
		}
	}
}
add_action('wp_ajax_get_first_step_in_wf', array( 'FCProcessFlow', 'get_first_step_in_wf' ) );
add_action('wp_ajax_get_pre_next_steps', array( 'FCProcessFlow', 'get_pre_next_steps' ) );
add_action('wp_ajax_submit_post_to_step', array( 'FCProcessFlow', 'submit_post_to_step' ) );
add_action('wp_ajax_get_users_in_step', array( 'FCProcessFlow', 'get_users_in_step' ) );
add_action('wp_ajax_change_workflow_status_to_complete', array( 'FCProcessFlow', 'change_workflow_status_to_complete' ) );
add_action('wp_ajax_change_workflow_status_to_cancelled', array( 'FCProcessFlow', 'change_workflow_status_to_cancelled' ) );
add_action('wp_ajax_exit_post_from_workflow', array( 'FCProcessFlow', 'exit_post_from_workflow' ) );
?>