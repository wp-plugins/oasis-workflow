<?php
class FCWorkflowInbox extends FCWorkflowBase
{
	static function get_table_header()
	{
		$order = ( isset($_GET['order']) && $_GET["order"] == "desc" ) ? "asc" : "desc" ;
		
		if( isset($_GET['orderby']) && $_GET["orderby"] == "post_title" )
			$post_order_class = $_GET["order"] ;
		else
			$post_order_class = "" ;
				
		echo "<tr>";
		echo "<th scope='col' class='manage-column check-column' ><input type='checkbox'></th>";
		echo "<th width='300px' scope='col' class='sorted $post_order_class'>
				<a href='admin.php?page=oasiswf-inbox&orderby=post_title&order=$order" . "'>
					<span>". __("Post/Page", "oasisworkflow") . "</span>
					<span class='sorting-indicator'></span>
				</a>				
				</th>";
		echo "<th>" . __("Type", "oasisworkflow") . "</th>";
		echo "<th>" . __("Author", "oasisworkflow") . "</th>";
		echo "<th>" . __("Workflow", "oasisworkflow") . "</th>";
		echo "<th>" . __("Step", "oasisworkflow") . "</th>";
		echo "<th>" . __("Status", "oasisworkflow") . "</th>";
		echo "<th>" . __("Due Date", "oasisworkflow") . "</th>";
		echo "<th>" . __("Comments", "oasisworkflow") . "</th>";
		echo "</tr>";
	}

	static function get_editinline_html()
	{
		global $current_screen;
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		$current_screen->post_type=$_POST["post_type"];
		$wp_list_table->inline_edit();
		exit();
	}

	static function get_step_signoff_content()
	{
		ob_start() ;
	   include( OASISWF_PATH . "includes/pages/subpages/submit-step.php" ) ;
		$result = ob_get_contents();
		ob_end_clean();
		echo $result;
		exit();
	}

	static function get_reassign_content()
	{
		ob_start() ;
		include( OASISWF_PATH . "includes/pages/subpages/reassign.php" ) ;
		$result = ob_get_contents();
		ob_end_clean();
		echo $result;
		exit();
	}
	
	static function check_claim_ajax() {
		$historyId = $_POST["history_id"] ;
		if (self::check_claim( $historyId )) {
			echo "true";
		} else {
			echo "false";
		}
		exit();
	}	

	static function check_claim($actionid)
	{
		global $wpdb;
		$action = FCWorkflowInbox::get_action_history_by_id( $actionid ) ;
		$rows = $wpdb->get_results("SELECT * FROM " . FCUtility::get_action_history_table_name() . " WHERE action_status = 'assignment' AND post_id = {$action->post_id}") ;
		if( count($rows) > 1 )return $rows;
		return false;
	}

	static function claim_process()
	{
		global $wpdb;
		$actioid = $_POST["actionid"] ;
		$actions = FCWorkflowInbox::check_claim($_POST["actionid"]) ;
		$action_history_table = FCUtility::get_action_history_table_name();
		$post_title = "";
		if( $actions ){
			foreach ($actions as $action) {
            if ($post_title == "")
            {
               $post_title = stripcslashes(get_post($action->post_id)->post_title);
            }
				if( $actioid == $action->ID ){
					$newData = (array)$action ;
					unset($newData["ID"]) ;
					$newData["action_status"] = "assignment" ;
					$newData["from_id"] = $action->ID ;
					$newData["create_datetime"] = current_time('mysql') ;
				   if ( empty( $action->due_date )) {
                  unset( $newData["due_date"] );
               }
				   if ( empty( $action->reminder_date )) {
                  unset( $newData["reminder_date"] );
               }
				   if ( empty( $action->reminder_date_after )) {
                  unset( $newData["reminder_date_after"] );
               }
					$iid = FCWorkflowInbox::insert_to_table( $action_history_table, $newData ) ;
					// delete reminder emails, since the assignment is now claimed
					FCWorkflowEmail::delete_step_email($action->ID, $action->assign_actor_id);

					// send mail to the actor about the assignment and add email reminders, if any
					FCWorkflowEmail::send_step_email( $iid ) ;

					$data["action_status"] = "claimed" ;
					//$data["comment"] = "" ;
				}else{
					$data["action_status"] = "claim_cancel" ;
					$email_settings = get_site_option('oasiswf_email_settings') ;
					if ( $email_settings['assignment_emails'] == "yes" ) {
						$blog_name = '[' . addslashes( get_bloginfo( 'name' )) . '] ';
						$title = $blog_name. __("Task claimed", "oasisworkflow") ;
						$message = __('Another user has claimed the task for the article "' . $post_title . '", so please ignore it.', "oasisworkflow") ;
						FCWorkflowEmail::send_mail($action->assign_actor_id, $title, $message) ;
					}
					FCWorkflowEmail::delete_step_email($action->ID, $action->assign_actor_id);
					//$data["comment"] = "" ;
				}
				$wpdb->update( $action_history_table, $data, array( "ID" => $action->ID ) ) ;

			}
		}
		echo $iid ;
		exit();
	}

	static function reset_assign_actor()
	{
		global $wpdb;
		$action_table = FCUtility::get_action_table_name();
		$action_history_table = FCUtility::get_action_history_table_name();
		$current_user = ($_POST["task_user"] != "")? $_POST["task_user"] : get_current_user_id();
		if( $_POST["oasiswf"] && $_POST["reassign_id"] ){
			$action = FCWorkflowInbox::get_action_history_by_id( $_POST["oasiswf"] ) ;
			$data = (array)$action ;
			if( $data["assign_actor_id"] != -1 ){
				unset( $data["ID"] ) ;
				if ( empty($data['due_date']) || $data['due_date'] == '0000-00-00') {
				   unset($data['due_date']);
				}
				if ( empty($data['reminder_date']) || $data['reminder_date'] == '0000-00-00') {
				   unset($data['reminder_date']);
				}
				if ( empty($data['reminder_date_after']) || $data['reminder_date_after'] == '0000-00-00') {
				   unset($data['reminder_date_after']);
				}
				$data["assign_actor_id"] = $_POST["reassign_id"] ;
				$data["from_id"] = $_POST["oasiswf"] ;
				$data["create_datetime"] = current_time('mysql') ;
				$iid = FCWorkflowInbox::insert_to_table( $action_history_table, $data ) ;
				if( $iid ){
					$wpdb->update($action_history_table, array( "action_status" => "reassigned" ), array( "ID" => $_POST["oasiswf"] ) ) ;
					$wpdb->get_results("DELETE FROM " . FCUtility::get_emails_table_name() . " WHERE action=1  AND to_user = " . $current_user . " AND history_id=" . $_POST["oasiswf"]) ;
					FCWorkflowEmail::send_step_email($iid, $_POST["reassign_id"]) ; // send mail to the actor .
					echo $iid ;
				}
			}else{
				$reviews = FCWorkflowInbox::get_review_action_by_status( "assignment", $_POST["oasiswf"] ) ;
				foreach ($reviews as $review) {
					if( $review->actor_id == $_POST["reassign_id"] ){
						echo "Selected user is already assigned." ;
						exit() ;
					}
				}
				$review = FCWorkflowInbox::get_review_action( "assignment", $current_user, $_POST["oasiswf"] ) ;
				$review = (array)$review ;
				$reviewId = $review["ID"] ;
				unset( $review["ID"] ) ;
				if ( empty($review['due_date']) || $review['due_date'] == '0000-00-00') {
				   unset($review['due_date']);
				}
				if ( empty($review['comments'] )) {
				   unset($review['comments']);
				}
				$review["actor_id"] = $_POST["reassign_id"] ;
				$r_iid = FCWorkflowInbox::insert_to_table( $action_table, $review ) ;
				if( $r_iid ){
					$wpdb->update($action_table, array( "review_status" => "reassigned" ), array( "ID" => $reviewId ) ) ;
					$data = array("to_id" => $r_iid, "sign_off_date" => current_time("mysql")) ;
					update_option("reassign_{$reviewId}", $data) ;
					$wpdb->get_results("DELETE FROM " . FCUtility::get_emails_table_name() . " WHERE action=1 AND to_user = " . $current_user . " AND history_id=" . $_POST["oasiswf"]) ;
					FCWorkflowEmail::send_step_email($_POST["oasiswf"], $_POST["reassign_id"]) ; // send mail to the actor .
					echo $r_iid ;
				}
			}
			exit();
		}
	}

	static function get_step_comment()
	{
		ob_start() ;
		include( OASISWF_PATH . "includes/pages/subpages/action-comments.php" ) ;
		$result = ob_get_contents();
		ob_end_clean();
		echo $result;
		exit();
	}

	static function get_assigned_users()
	{
		global $wpdb;
		$order_by = " ORDER BY USERS.DISPLAY_NAME";
		$sql = "SELECT distinct USERS.ID, USERS.display_name FROM
					(SELECT U1.ID, U1.display_name FROM {$wpdb->users} AS U1
					LEFT JOIN " . FCUtility::get_action_history_table_name() . " AS AH ON U1.ID = AH.assign_actor_id
					WHERE AH.action_status = 'assignment'
					UNION
					SELECT U2.ID, U2.display_name FROM {$wpdb->users} AS U2
					LEFT JOIN " . FCUtility::get_action_table_name() . " AS A ON U2.ID = A.actor_id
					WHERE A.review_status = 'assignment') USERS
					{$order_by} ";

		$result = $wpdb->get_results( $sql ) ;
		return $result;
	}

}
?>