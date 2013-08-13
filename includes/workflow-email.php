<?php
//------------- mail functions ----------------
class FCWorkflowEmail extends FCWorkflowBase
{

	static function send_mail($touser, $title, $message)
	{
		if( is_numeric( $touser ) ){
			$user = get_userdata( $touser ) ;
		}else{
			$user = get_user_by('email', $touser);
		}

		$headers = array("From: " . get_option( 'blogname' ) . " <" . get_site_option( 'admin_email' ) . ">", "Content-Type: text/html");
		$h = implode("\r\n",$headers) . "\r\n";

		wp_mail($user->data->user_email, $title, $message, $h) ;
	}

	static function get_step_mail_content($actionid, $stepid, $touserid, $postid)
	{
		$step = FCProcessFlow::get_step_by_id( $stepid ) ;

		$nickname = get_user_meta( $touserid, "nickname", true ) ;
		$first_name = get_user_meta( $touserid, "first_name", true ) ;
		$first_name = ( $first_name ) ? $first_name : $nickname ;
		$last_name = get_user_meta( $touserid, "last_name", true ) ;
		$last_name = ( $last_name ) ? $last_name : $nickname ;

		$post = get_post( $postid ) ;
		$post_title = $post->post_title ;
		$post_url = admin_url( 'post.php?post=' . $postid . '&action=edit&oasiswf=' . $actionid );
      $blog_name = '[' . get_bloginfo( 'name' ) . '] ';

		if( $step && $post ){
			$messages = json_decode( $step->process_info ) ;

			if( !$messages )return false;

   		$post_link = '';
   		$message_content = trim($messages->assign_content);
   		if (empty($message_content)) {
   		   $post_link = '<a href=' . $post_url . ' target="_blank">' . $post_title . '</a>';
   		}
   		$messages->assign_subject = (!empty($messages->assign_subject)) ? $blog_name . $messages->assign_subject : $blog_name . __("You have an assignment", "oasisworkflow");
   		$messages->assign_content = (!empty($message_content)) ? $messages->assign_content : __("You have an assignment related to post - " . $post_link, "oasisworkflow");


			foreach ($messages as $k => $v) {
				$v = str_replace("%first_name%", $first_name, $v);
				$v = str_replace("%last_name%", $last_name, $v);
				$v = str_replace("%post_title%", '<a href=' . $post_url . ' target="_blank">' . $post_title . '</a>', $v);
				$messages->$k = $v ;
			}
			return 	$messages ;
		}
		return false ;
	}

	static function get_step_comment_content($actionid)
	{
		$actionStep = FCProcessFlow::get_action_history_by_id( $actionid ) ;
		if( !$actionStep->comment )return false ;
		$comments = json_decode($actionStep->comment) ;
		$commentStr = "";
		foreach ($comments as $comment) {
			$nickname = get_user_meta( $comment->send_id, "nickname", true ) ;
			$first_name = get_user_meta( $comment->send_id, "first_name", true ) ;
			$first_name = ( $first_name ) ? $first_name : $nickname ;
			$last_name = get_user_meta( $comment->send_id, "last_name", true ) ;
			$last_name = ( $last_name ) ? $last_name : $nickname ;
			$nameStr = ( $first_name == $last_name ) ? $nickname : $first_name . " " . $last_name ;
			$signOffDate = FCWorkflowBase::format_date_for_display($actionStep->create_datetime, "-", "datetime");
			$dueDate = FCWorkflowBase::format_date_for_display ($actionStep->due_date);
         if ($comment->comment != "")
         {
			   $commentStr .= "<p><strong>" . __('Additionally,', "oasisworkflow") . "</strong> {$nameStr} " . __('added the following comments', "oasisworkflow") . ":</p>" ;
			   $commentStr .= "<p>" . nl2br($comment->comment) . "</p>" ;
         }
			$commentStr .= "<p>" . __('Sign off date', "oasisworkflow") . " : {$signOffDate}</p>" ;
			$commentStr .= "<p>" . __('Due date', "oasisworkflow") . " : {$dueDate} </p>" ;

		}
		return $commentStr ;
	}

	static function send_step_email($actionid, $touserid=null)
	{
		$actionStep = FCProcessFlow::get_action_history_by_id( $actionid ) ;
		$touserid = ( $touserid ) ? $touserid : $actionStep->assign_actor_id ;
		$fromuserid = get_current_user_id() ;
		$mails = FCWorkflowEmail::get_step_mail_content($actionid, $actionStep->step_id, $touserid, $actionStep->post_id) ;
		$comment = FCWorkflowEmail::get_step_comment_content($actionid) ;

		$data = array(
				'to_user' => $touserid,
				'history_id' => $actionid,
				'create_datetime' => current_time('mysql')
			);
		$emails_table = FCUtility::get_emails_table_name();

		if( $mails->assign_subject && $mails->assign_content ){

			$mailcontent = $mails->assign_content . $comment ;

			FCWorkflowEmail::send_mail($touserid, $mails->assign_subject, $mailcontent);

			$data["subject"] = $mails->assign_subject ;
			$data["message"] = $mailcontent ;
			$data["send_date"] = current_time('mysql') ;
			$data["action"] = 0 ;
			FCProcessFlow::insert_to_table( $emails_table, $data ) ;
		}

		if( $mails->reminder_subject && $mails->reminder_content ){
			$mailcontent = $mails->reminder_content . $comment ;

			$data["subject"] = $mails->reminder_subject ;
			$data["message"] = $mailcontent ;
			$data["action"] = 1 ;
			if ( $actionStep->reminder_date )
			{
			   $data["send_date"] = $actionStep->reminder_date ;
			   FCProcessFlow::insert_to_table( $emails_table, $data ) ;
			}

			if ( $actionStep->reminder_date_after )
			{
			   $data["send_date"] = $actionStep->reminder_date_after ;
			   FCProcessFlow::insert_to_table( $emails_table, $data ) ;
			}
		}
	}

	static function delete_step_email($action_history_id, $user_id = null)
	{
      // if the user completes the assignment on time, then no need to send reminder emails
      global $wpdb;
      if ( $user_id )
      {
         $wpdb->get_results( "DELETE FROM " . FCUtility::get_emails_table_name() . " WHERE action = 1 and history_id = " . $action_history_id . " and to_user = " . $user_id) ;
      }
      else
      {
         $wpdb->get_results( "DELETE FROM " . FCUtility::get_emails_table_name() . " WHERE action = 1 and history_id = " . $action_history_id) ;
      }
	}
}
?>