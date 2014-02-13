<?php
/*************************************/
/*     Workflow process              */
/*************************************/

class FCProcessFlow extends FCWorkflowBase
{
	static function workflow_submit_check($selected_user)
	{
		//-------inbox----------
		$page_var = isset($_GET['page']) ? $_GET["page"] : "";
		if ( $page_var == 'oasiswf-inbox') return "inbox";

	   //-------submit----------
		$post_var = isset($_GET['post']) ? $_GET["post"] : "";
		if ( is_array($post_var) ) {//looks like the user is performing a bulk action, and hence we need not load the workflow javascripts
		   return false;
		}
		$rows = FCProcessFlow::get_action_history_by_status( "assignment", $post_var ) ;
		if( count( $rows ) == 0 )return "submit" ;

		//-------sign off------------
		if( isset($_GET['post']) && $_GET["post"] && isset($_GET['action']) && $_GET["action"] == "edit"){
			$row = FCProcessFlow::get_assigned_post( $_GET["post"], $selected_user, "row" ) ;
			if($row){
				return $row->ID ;
			}
		}
		return false;
	}

	static function get_first_last_step($wfid)
	{
		$result = FCProcessFlow::get_workflow_by_id( $wfid  ) ;
		$wfinfo = json_decode( $result->wf_info ) ;

		if( $wfinfo->steps ){
			foreach ($wfinfo->steps as $k => $v) {
				if( $v->fc_dbid == "nodefine" )return "nodefine" ;
				   $step_stru = FCProcessFlow::get_process_steps($v->fc_dbid, "target");
				if( isset($step_stru["success"]) && $step_stru["success"] )
				   continue ;
				$first_step[] = array($v->fc_dbid, $v->fc_label, $v->fc_process);
			}

			foreach ($wfinfo->steps as $k => $v) {
				if( $v->fc_dbid == "nodefine" )return "nodefine" ;
				   $step_stru = FCProcessFlow::get_process_steps($v->fc_dbid, "source");
				if( isset($step_stru["success"]) && $step_stru["success"] )continue ;
   				$last_step[] = array($v->fc_dbid, $v->fc_label, $v->fc_process);
			}

			$getStep["first"] = $first_step ;
			$getStep["last"] = $last_step ;
		}

		return $getStep ;

	}

	static function get_first_step_in_wf_internal($workflow_id)
	{
		$steps = FCProcessFlow::get_first_last_step($workflow_id) ;

		$workflow = FCProcessFlow::get_workflow_by_id( $workflow_id ) ;
		$wfinfo = json_decode( $workflow->wf_info ) ;
		if( $wfinfo->first_step && count($wfinfo->first_step) == 1 ){
			$step_db_id = FCProcessFlow::get_gpid_dbid($wfinfo, $wfinfo->first_step[0]) ;
			$step_lbl = FCProcessFlow::get_gpid_dbid($wfinfo, $wfinfo->first_step[0], "lbl") ;
			$process = FCProcessFlow::get_gpid_dbid($wfinfo, $wfinfo->first_step[0], "process") ;
			unset($steps["first"]) ;
			$steps["first"][] = array($step_db_id, $step_lbl, $process) ;
			return $steps;
		}
		else{
			return null;
		}
	}

	static function get_first_step_in_wf()
	{
      $workflowId = $_POST["wf_id"];
      $steps = FCProcessFlow::get_first_step_in_wf_internal($workflowId);
      if ($steps != null)
      {
         echo json_encode($steps) ;
      }
      else
      {
         echo "wrong" ;
      }
      exit();
	}


	static function get_users_in_step_internal($step_id, $postId=null, $decision=null)
	{
		if( $step_id == "nodefine" ){
			return null ;
		}

		$result = null;
		$wf_info = FCProcessFlow::get_step_by_id( $step_id ) ;
		if($wf_info){
			$step_info= json_decode( $wf_info->step_info ) ;
			if ($step_info->process != 'review') { // cannot review your own work
			   $decision = null;
			}
			$users = FCProcessFlow::get_users_by_role( $step_info->assignee, $postId, $decision ) ;
			if($users){
				$result["users"] = $users ;
				$result["process"] = $step_info->process ;
			}
		}
		return $result;
	}

	static function get_users_in_step()
	{
	   $stepId = $_POST["stepid"];
	   $postId = isset($_POST["postid"]) ? $_POST["postid"] : null;
	   $decision = $_POST["decision"];
      $users = FCProcessFlow::get_users_in_step_internal($stepId, $postId, $decision);
      if ($users != null)
      {
         echo json_encode( $users );
      }
      else
      {
         echo "no users found";
      }

      exit();
	}

	static function save_action($data, $actors, $actionid=null, $actionfrm=null)
	{
	   // reminder days BEFORE the due date
		$reminder_days = get_site_option("oasiswf_reminder_days") ;
		if ($reminder_days) {
		   $data["reminder_date"] = FCProcessFlow::get_pre_next_date( $data["due_date"], "pre", $reminder_days) ;
		}

		// reminder days AFTER the due date
		$reminder_days_after = get_site_option("oasiswf_reminder_days_after") ;
		if ($reminder_days_after) {
		   $data["reminder_date_after"] = FCProcessFlow::get_pre_next_date( $data["due_date"], "next", $reminder_days_after) ;
		}

		$action_history_table = FCUtility::get_action_history_table_name();
		$action_table = FCUtility::get_action_table_name();
      $wf_info = FCProcessFlow::get_step_by_id( $data["step_id"] ) ;
		if($wf_info)
		{
			$step_info = json_decode( $wf_info->step_info ) ;
		}
		if( is_numeric( $actors ) )
		{
			$data["assign_actor_id"] = $actors ;
			$iid = FCProcessFlow::insert_to_table( $action_history_table, $data ) ;
			if( !$actionfrm )FCWorkflowEmail::send_step_email( $iid ) ; // send mail to the actor .
		}
		else if (!is_numeric( $actors ) && ($step_info->process == "assignment" || $step_info->process == "publish" )) //multiple actors are assigned in assignment step or publish step
		{
         $arr = explode("@", $actors) ;
         for( $i = 0; $i < count( $arr ); $i++ )
         {
            $data["assign_actor_id"] = $arr[$i];
			   $iid = FCProcessFlow::insert_to_table( $action_history_table, $data ) ;
			   if( !$actionfrm )FCWorkflowEmail::send_step_email( $iid ) ; // send mail to the actor .
         }
		}
		else
		{
			$data["assign_actor_id"] = -1 ;
			$iid = FCProcessFlow::insert_to_table( $action_history_table, $data ) ;

			$redata = array(
						'review_status' => 'assignment',
						'action_history_id' => $iid
					);

			$arr = explode("@", $actors) ;
			for( $i = 0; $i < count( $arr ); $i++ ){
				if(!$arr[$i])continue;
				$redata["actor_id"] = $arr[$i] ;
				FCProcessFlow::insert_to_table( $action_table, $redata ) ;
				FCWorkflowEmail::send_step_email($iid, $arr[$i]) ; // send mail to the actor .
			}
		}

		if( $actionid ){
			global $wpdb;
			$wpdb->update($action_history_table, array( "action_status" => "processed" ), array( "ID" => $actionid ) ) ;
		}

		return $iid ;
	}

	static function submit_post_to_workflow_internal($stepId, $postId, $actors, $dueDate, $userComments)
	{
		$userId = get_current_user_id() ;

		$comments[] = array( "send_id" => $userId, "comment" => stripcslashes($userComments) ) ;
		$saveComments = json_encode( $comments ) ;

		//--- post create and sign off by admin ----
		$post = get_post($postId) ;
		$auserid= get_current_user_id() ;
		$auser = FCProcessFlow::get_user_name($auserid) ;
		$acomments[] = array( "send_id" => "System", "comment" => "Post/Page was submitted to the workflow by " . $auser ) ;
		$adata = array(
					'action_status' => "submitted",
					'comment' => json_encode( $acomments ) ,
					'step_id' => $stepId,
					'post_id' => $postId,
					'from_id' => '',
					'due_date' => '',
					'create_datetime' => $post->post_date
				);
		$aiid = FCProcessFlow::save_action( $adata, $auserid, "", "temp") ;		// This action doesn't send email.
		//-----------------------------------------
		$due_date = $dueDate != null ? FCWorkflowCRUD::format_date_for_db( $dueDate ) : null;
		$data = array(
					'action_status' => "assignment",
					'comment' => $saveComments,
					'step_id' => $stepId,
					'post_id' => $postId,
					'from_id' => $aiid,
					'due_date' => $due_date,
					'create_datetime' => current_time('mysql')
				);

		$iid = FCProcessFlow::save_action( $data, $actors) ;
		update_post_meta($postId, "oasis_is_in_workflow", 1); // set the post meta to 1, specifying that the post is in a workflow.
		// update_option( "workflow_" . $iid, $userId ) ;
	}

   static function submit_post_to_workflow()
   {
      $stepId = $_POST["hi_step_id"];
      $postId = $_POST["post_ID"];
      $actors = $_POST["hi_actor_ids"];
      $dueDate = $_POST["hi_due_date"];
      $comments = $_POST["hi_comment"];

      FCProcessFlow::submit_post_to_workflow_internal($stepId, $postId, $actors, $dueDate, $comments);

   }

	//----------------review process-------------------------
	static function review_result_process_internal($ddata, $actionId, $result)
	{
		$action = FCProcessFlow::get_action_history_by_id( $actionId ) ;
		$data = array(
					'action_status' => "assignment",
					'post_id' => $action->post_id,
					'from_id' => $action->ID,
					'create_datetime' => current_time('mysql')
				);

		foreach ($ddata as $k => $v) {
			$data["assign_actor_id"] = $v["re_actor_id"] ;
			$data["step_id"] = $v["re_step_id"] ;
			$data["comment"] = $v["re_comment"] ;
			$data["due_date"] = $v["re_due_date"] ;
			$newid = FCProcessFlow::save_action( $data, $v["re_actor_id"], $action->ID ) ;
		}
		//--------post status change---------------

		FCProcessFlow::copy_step_status_to_post($_POST["post_ID"], $action->step_id, $result) ;
	}

	static function get_review_result_data($ddata)
	{
		for( $i = 0; $i < count( $ddata ); $i++ )
		{
			$kkey = $ddata[$i]["re_actor_id"] . "_" . $ddata[$i]["re_step_id"] ;

			if( $getdata[$kkey] ) {

				//-----integrate some comments into one comment-----
				$sumcomment = json_decode($getdata[$kkey]["re_comment"]) ;
				$temp_comment = json_decode($ddata[$i]["re_comment"]) ;
				$sumcomment[] = (object)$temp_comment[0] ;

				//-----get minimal due date--------
				$g_date = FCProcessFlow::get_date_int($getdata[$kkey]["re_due_date"]) ;
				$d_date = FCProcessFlow::get_date_int($ddata[$i]["re_due_date"]) ;
				$temp_date = ( $g_date < $d_date ) ? $getdata[$kkey]["re_due_date"] : $ddata[$i]["re_due_date"] ;

				$getdata[$kkey] = $ddata[$i] ;

				$getdata[$kkey]["re_comment"] = json_encode($sumcomment) ;
				$getdata[$kkey]["re_due_date"] = $temp_date ;
			}else{
				$getdata[$kkey] = $ddata[$i] ;
			}
		}

		return $getdata ;
	}

	static function review_result_process($actionid)
	{
		$reviews = FCProcessFlow::get_review_action_by_history_id( $actionid ) ;

		if( $reviews ){
			foreach ($reviews as $review) {
				$r = array(
						"re_actor_id" => $review->reassign_actor_id,
						"re_step_id" => $review->step_id,
						"re_comment" => $review->comments,
						"re_due_date" => $review->due_date
					) ;
				$data[$review->review_status][] = $r ;
			}
		}

		if( isset($data["assignment"]) && $data["assignment"] )return false;

		if( isset($data["unable"]) && $data["unable"] ){
			$ddata = FCProcessFlow::get_review_result_data($data["unable"]) ;
			FCProcessFlow::review_result_process_internal($ddata, $actionid, "unable") ;
			return false;
		}

		if( isset($data["complete"]) && $data["complete"] ){
			$ddata = FCProcessFlow::get_review_result_data($data["complete"]) ;
			FCProcessFlow::review_result_process_internal($ddata, $actionid, "complete") ;
			return false;
		}

	}

	static function submit_post_to_step()
	{
		global $wpdb ;

		$action = FCProcessFlow::get_action_history_by_id( $_POST["oasiswf"] ) ;
		$userId = get_current_user_id() ;
		if( isset($_POST["hi_task_user"]) && $_POST["hi_task_user"] != "" )
		{
		  $current_actor_id = $_POST["hi_task_user"];
		}
		else
		{
		  $current_actor_id = $userId;
		}
		$comments[] = array( "send_id" => $userId, "comment" => stripcslashes($_POST["hi_comment"]) ) ;
		$saveComments = json_encode( $comments ) ;
		$action_table = FCUtility::get_action_table_name();
		if( $action->assign_actor_id == -1 )
		{
		   if( is_numeric( $_POST["hi_actor_ids"] ) )
		   {
		      $first_actor = $_POST["hi_actor_ids"];
		   }
		   else
		   {
		      $arr = explode("@", $_POST["hi_actor_ids"]) ;
		      $first_actor = $arr[0];
		   }
         // update with the first actor
         $due_date = (isset($_POST["hi_due_date"]) && !empty($_POST["hi_due_date"])) ? FCWorkflowCRUD::format_date_for_db( $_POST["hi_due_date"] ) : null;
			$updatedata = array(
							"review_status" => $_POST["review_result"],
							"reassign_actor_id" => $first_actor,
							"step_id" => $_POST["hi_step_id"],
							"comments" => $saveComments,
							"due_date" => $due_date,
							"update_datetime" => current_time('mysql')
						 ) ;
			$wpdb->update($action_table, $updatedata, array( "actor_id" => $current_actor_id, "action_history_id" => $_POST["oasiswf"] ) ) ;

		   if( !is_numeric( $_POST["hi_actor_ids"] ) ) // insert the rest of the data for other actors
		   {
            for( $i = 1; $i < count( $arr ); $i++ )
            {
               $due_date = (isset($_POST["hi_due_date"]) && !empty($_POST["hi_due_date"])) ? FCWorkflowCRUD::format_date_for_db( $_POST["hi_due_date"] ) : null;
      			$redata = array(
      							"review_status" => $_POST["review_result"],
      							"reassign_actor_id" => $arr[$i],
      							"actor_id" => $current_actor_id,
      							"step_id" => $_POST["hi_step_id"],
      							"comments" => $saveComments,
      							"due_date" => $due_date,
      							"action_history_id" => $_POST["oasiswf"],
      							"update_datetime" => current_time('mysql')
      						 ) ;
               FCProcessFlow::insert_to_table( $action_table, $redata ) ;
            }
		   }
		   // delete all the unsend emails for this workflow
			FCWorkflowEmail::delete_step_email($_POST["oasiswf"], $current_actor_id);
			FCProcessFlow::review_result_process( $_POST["oasiswf"] ) ;
		}else{
		   $due_date = (isset($_POST["hi_due_date"]) && !empty($_POST["hi_due_date"])) ? FCWorkflowCRUD::format_date_for_db( $_POST["hi_due_date"] ) : null;
			$data = array(
						'action_status' => "assignment",
						'comment' => $saveComments,
						'step_id' => $_POST["hi_step_id"],
						'post_id' => $_POST["post_ID"],
						'from_id' => $_POST["oasiswf"],
						'due_date' => $due_date,
						'create_datetime' => current_time('mysql')
					);
			$iid = FCProcessFlow::save_action( $data, $_POST["hi_actor_ids"], $_POST["oasiswf"]) ;
		   // delete all the unsend emails for this workflow
			FCWorkflowEmail::delete_step_email($_POST["oasiswf"], $current_actor_id);
			//------post status chage----------
			FCProcessFlow::copy_step_status_to_post($_POST["post_ID"], $action->step_id, $_POST["review_result"]) ;
		}
		echo "success";
		exit();
	}
	//-----------------------------------------------------------
	static function change_workflow_status_to_complete()
	{
		if( $_POST["immediately"] )
		{
			$im_dt = new DateTime($_POST["immediately"]);
			$now_dt = new DateTime(current_time('mysql'));
		}

		$history = FCProcessFlow::get_action_history_by_id( $_POST["oasiswf_id"] ) ;
		$currentTime = current_time('mysql') ;
		$data = array(
					'action_status' => "complete",
					'step_id' => $history->step_id,
					'assign_actor_id' => get_current_user_id(),
					'post_id' => $_POST["post_id"],
					'from_id' => $_POST["oasiswf_id"],
					'due_date' => $currentTime,
					'reminder_date' => $currentTime,
					'create_datetime' => $currentTime
				);
		$action_history_table = FCUtility::get_action_history_table_name();
		$iid = FCProcessFlow::insert_to_table( $action_history_table, $data ) ;
		if( isset($_POST["hi_task_user"]) && $_POST["hi_task_user"] != "" )
		{
		  $current_actor_id = $_POST["hi_task_user"];
		}
		else
		{
		  $current_actor_id = get_current_user_id();
		}
		if( $iid ){
			global $wpdb;
		   // delete all the unsend emails for this workflow
			FCWorkflowEmail::delete_step_email($_POST["oasiswf_id"], $current_actor_id);

			$result = $wpdb->update($action_history_table, array('action_status' => 'processed'), array('ID' => $_POST["oasiswf_id"]));

			$action = FCProcessFlow::get_action_history_by_id( $_POST["oasiswf_id"] ) ;
			if($_POST["immediately"] && $now_dt < $im_dt){
				FCProcessFlow::copy_step_status_to_post($_POST["post_id"], $action->step_id, "complete", $_POST["immediately"]) ;
			}else{
				FCProcessFlow::copy_step_status_to_post($_POST["post_id"], $action->step_id, "complete") ;
			}
			update_post_meta($_POST["post_id"], "oasis_is_in_workflow", 0); // set the post meta to 0, specifying that the post is out of a workflow.
			echo $iid;
		}
		exit();
	}

	static function change_workflow_status_to_cancelled()
	{

		$userId = get_current_user_id() ;
		$comments[] = array( "send_id" => $userId, "comment" => stripcslashes($_POST["hi_comment"]) ) ;
		$saveComments = json_encode( $comments ) ;

		$data = array(
					'action_status' => "cancelled",
					'comment' => $saveComments,
					'post_id' => $_POST["post_id"],
					'from_id' => $_POST["oasiswf_id"],
					'create_datetime' => current_time('mysql')
				);
		$action_history_table = FCUtility::get_action_history_table_name();
		$iid = FCProcessFlow::insert_to_table( $action_history_table, $data ) ;

		if( isset($_POST["hi_task_user"]) && $_POST["hi_task_user"] != "" )
		{
		  $current_actor_id = $_POST["hi_task_user"];
		}
		else
		{
		  $current_actor_id = get_current_user_id();
		}

		if( $iid ){
			global $wpdb;
		   // delete all the unsend emails for this workflow
			FCWorkflowEmail::delete_step_email($_POST["oasiswf_id"], $current_actor_id);
			$result = $wpdb->update($action_history_table, array('action_status' => 'processed'), array('ID' => $_POST["oasiswf_id"]));

			$action = FCProcessFlow::get_action_history_by_id( $_POST["oasiswf_id"] ) ;

			FCProcessFlow::copy_step_status_to_post($_POST["post_id"], $action->step_id, $_POST["review_result"]) ;

			//-----------------email-----------------------
			$current_userid = get_current_user_id() ;
         $users = $wpdb->get_results( "SELECT users_1.ID, users_1.display_name FROM {$wpdb->base_prefix}users users_1
         					INNER JOIN {$wpdb->base_prefix}usermeta usermeta_1 ON ( users_1.ID = usermeta_1.user_id )
								WHERE (usermeta_1.meta_key = '{$wpdb->prefix}capabilities' AND CAST( usermeta_1.meta_value AS CHAR ) LIKE '%administrator%')");

			$post = get_post($_POST["post_id"]) ;
			$title = "'{$post->post_title}' was cancelled from the workflow" ;
         foreach ( $users as $user ) {
            FCWorkflowEmail::send_mail( $user->ID, $title, stripcslashes($_POST["hi_comment"])) ;
         }
			//---------------------------------------------
		   update_post_meta($_POST["post_id"], "oasis_is_in_workflow", 0); // set the post meta to 0, specifying that the post is out of a workflow.
			echo $iid;
		}
		exit() ;
	}

	static function get_step_status_by_history_id()
	{
	   $action = FCProcessFlow::get_action_history_by_id( $_POST["oasiswf"] ) ;
	   $step_id = $action->step_id;
	   $step_result = $_POST["review_result"];
		$step = FCProcessFlow::get_step_by_id( $step_id ) ;

		if( $step ){
			$step_info = json_decode( $step->step_info ) ;
         $step_status = "draft";

			if($step_result=="complete")
				$step_status = $step_info->status ;
			else
				$step_status = $step_info->failure_status ;

			if( $step_status ){
			   echo $step_status;
			   exit();
			}
		}
	}

	static function get_step_status_by_step_id()
	{
		$step = FCProcessFlow::get_step_by_id( $_POST["step_id"] ) ;
      $step_result = $_POST["review_result"];

		if( $step ){
			$step_info = json_decode( $step->step_info ) ;
         $step_status = "draft";

			if($step_result=="complete")
				$step_status = $step_info->status ;
			else
				$step_status = $step_info->failure_status ;

			if( $step_status ){
			   echo $step_status;
			   exit();
			}
		}
	}

	static function copy_step_status_to_post($postid, $stepid, $result, $immediately=null)
	{
		$step = FCProcessFlow::get_step_by_id( $stepid ) ;

		if( $step ){
			$step_info = json_decode( $step->step_info ) ;

			if($result=="complete")
				$step_status = $step_info->status ;
			else
				$step_status = $step_info->failure_status ;

			if( $step_status ){
				global $wpdb;
				if($immediately){
					if($step_status == "publish")$step_status = "future" ;
					$publish_post = array(
   	   			"ID" => $postid,
					   "post_date_gmt" => $immediately,
					   "post_date" => $immediately,
						"post_status" => $step_status
					);
					wp_update_post( $publish_post );
				}else{
					$update_post = array(
   	   			"ID" => $postid,
						"post_status" => $step_status
					);
					wp_update_post( $update_post );
				}
			}
		}
	}

	static function set_loading_post_status()
	{
		$status_info = $_POST["hi_process_info"] ;
		$temp = explode("@#@", $status_info) ;
		$action = FCProcessFlow::get_action_history_by_id( $temp[0] ) ;
		FCProcessFlow::copy_step_status_to_post($_POST["post_ID"], $action->step_id, $temp[1]) ;
	}

	static function get_pre_next_steps()
	{
		$oasiswf = FCProcessFlow::get_action_history_by_id( $_POST["oasiswfId"] );
		$steps = FCProcessFlow::get_process_steps( $oasiswf->step_id );
		echo json_encode( $steps ) ;
		exit();
	}

	static function check_submit_wf_editable($wfid)
	{
		$workflow = FCWorkflowCRUD::get_workflow_by_id( $wfid ) ;

		$s_stamp = FCWorkflowCRUD::get_date_int( $workflow->start_date ) ;
		$e_stamp = FCWorkflowCRUD::get_date_int( $workflow->end_date ) ;
		$c_stamp = FCWorkflowCRUD::get_date_int() ;
		if( $s_stamp > $c_stamp ) return false ; // filter-1
		if( $e_stamp < $c_stamp ) return false ;  // filter-2

		return true ;
	}


	//-------------------graphic functions ------------------------
	static function get_connection($workflow, $sourceId, $targetId)
	{
		global $connCount;
		$wf_info = json_decode( $workflow->wf_info ) ;
		$conns = $wf_info->conns ;
		if( $conns ){
			$connCount++;
			$sourceGpId = FCProcessFlow::get_gpid_dbid($workflow->wf_info, $sourceId) ;
			$targetGpId = FCProcessFlow::get_gpid_dbid($workflow->wf_info, $targetId) ;

			foreach ($conns as $conn) {
				if( $conn->sourceId == $sourceGpId && $conn->targetId == $targetGpId ){
					//$conn->connset->ConnectionOverlays["Label"] = (object)(array("label"=>"a", "id"=>"label"));
					$conn->connset->paintStyle->lineWidth = 1 ;
					$conn->connset->labelStyle =  (object)array("cssClass" => "labelcomponent") ;
					$conn->connset->label =  "$connCount";
					return $conn ;
				}
			}
		}
	}

	//-------------exit post/page from workflow-----------
	static function exit_post_from_workflow()
	{
		global $wpdb ;
		$action = FCProcessFlow::get_action_history_by_id( $_POST["exitId"] ) ;
		$comment[] = array(
						"send_id" => get_current_user_id(),
						"comment" => "Post/Page was aborted from the workflow."
					) ;
		$data = array(
					"action_status" => "abort_no_action",
					"post_id" => $action->post_id,
					"comment" => json_encode($comment),
					"from_id" => $_POST["exitId"],
					'create_datetime' => current_time('mysql')
				) ;
		$action_history_table = FCUtility::get_action_history_table_name();
		$iid = FCProcessFlow::insert_to_table( $action_history_table, $data ) ;
		if($iid){
		   // delete all the unsend emails for this workflow
		   FCWorkflowEmail::delete_step_email($_POST["exitId"]);
			$wpdb->update($action_history_table, array( "action_status" => "aborted" ), array( "ID" => $_POST["exitId"] ) ) ;
		   update_post_meta($action->post_id, "oasis_is_in_workflow", 0); // set the post meta to 0, specifying that the post is out of a workflow.
			echo $iid ;
		}
		exit() ;
	}

	//-----------get immediately content----------------
	static function get_immediately_content($status)
	{
		if( $status != "publish" )return;
		$months = array(1 => "01-Jan", 2 => "02-Feb", 3 => "03-Mar", 4 => "04-Apr", 5 => "05-May", 6 => "06-Jun", 7 => "07-Jul", 8 => "08-Aug", 9 => "09-Sep", 10 => "10-Oct", 11 => "11-Nov", 12 => "12-Dec") ;
		$today = getdate();
		echo "<select id='im-mon'>" ;
			foreach ($months as $k => $v) {
				if( $today["mon"] * 1 == $k )
					echo "<option value={$k} selected>{$v}</option>" ;
				else
					echo "<option value={$k}>{$v}</option>" ;
			}
		echo "</select>" ;
		echo "<input type='text' id='im-day' value='{$today["mday"]}' class='immediately' size='2' maxlength='2' autocomplete='off'>,
			  <input type='text' id='im-year' value='{$today["year"]}' class='immediately' size='4' maxlength='4' autocomplete='off'> @
			  <input type='text' id='im-hh' value='{$today["hours"]}' class='immediately' size='2' maxlength='2' autocomplete='off'> :
			  <input type='text' id='im-mn' value='{$today["minutes"]}' class='immediately' size='2' maxlength='2' autocomplete='off'>";
	}
}
include(OASISWF_PATH . "includes/workflow-email.php") ;
?>