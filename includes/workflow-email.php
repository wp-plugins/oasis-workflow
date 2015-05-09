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
		
		$email_settings = get_site_option('oasiswf_email_settings') ;
		$from_name = $email_settings['from_name'];
		if (empty( $email_settings['from_name'] )) {
			$decoded_blog_name = html_entity_decode (  get_option( 'blogname' ), ENT_QUOTES, 'UTF-8' );
			$from_name = $decoded_blog_name;
		}
		
		$from_email = $email_settings['from_email_address'];
		if (empty( $email_settings['from_email_address'] )) {
			$from_email = get_site_option( 'admin_email' );
		}
		$headers = array("From: " .  $from_name . " <" . $from_email . ">", "Content-Type: text/html; charset=UTF-8");

		$h = implode("\r\n",$headers) . "\r\n";
		$decoded_title = html_entity_decode (  $title, ENT_QUOTES, 'UTF-8' );
		$decoded_message = html_entity_decode (  $message, ENT_QUOTES, 'UTF-8' );

		wp_mail($user->data->user_email, $decoded_title, $decoded_message, $h) ;

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
		$post_title = addslashes( $post->post_title );
		$post_url = admin_url( 'post.php?post=' . $postid . '&action=edit&oasiswf=' . $actionid );
      $blog_name = '[' . addslashes( get_bloginfo( 'name' )) . '] ';
		if( $step && $post ){
			$messages = json_decode( $step->process_info ) ;
			if( !$messages )return false;

   		$post_link = '';
   		$message_content = trim($messages->assign_content);

   		// replace all the non visible characters with space
   		$subject_line = str_replace(array("\\r\\n", "\\r", "\\n", "\\t", "<br />", ' '), '', trim( $messages->assign_subject ));
   		$content_line = str_replace(array("\\r\\n", "\\r", "\\n", "\\t", "<br />", ' '), '', trim( $message_content ));

   		if (empty($content_line)) {
   		   $post_link = '<a href="' . $post_url . '" target="_blank">' . $post_title . '</a>';
   		}

   		$messages->assign_subject = (!empty($subject_line)) ? $blog_name . $messages->assign_subject : $blog_name . __("You have an assignment", "oasisworkflow");
   		$messages->assign_content = (!empty($content_line)) ? $messages->assign_content : __("You have an assignment related to post - " . $post_link, "oasisworkflow");

		$callback_custom_placeholders = apply_filters('oasiswf_custom_placeholders_handler',$post);
		$categories = get_the_category($post->ID);
		$cats = array();

			foreach($categories as $c)
			{
				$cats[] = $c->name;
			}
			$all_cats = implode(', ',$cats);
			$last_modified = date('d-m-Y h:i:s', strtotime($post->post_modified));
			$future_publish_date = date('d-m-Y h:i:s', strtotime($post->post_date));

			foreach ($messages as $k => $v) {
				$v = str_replace("%first_name%", $first_name, $v);
				$v = str_replace("%last_name%", $last_name, $v);
				$v = str_replace("%category%", $all_cats, $v);
				$v = str_replace("%last_modified_date%", $last_modified, $v);
				$v = str_replace("%publish_date%", $future_publish_date, $v);

				if ($k === "assign_content" || $k === "reminder_content") { //replace %post_title% with a link to the post
				   $v = str_replace("%post_title%", '<a href="' . $post_url . '" target="_blank">' . $post_title . '</a>', $v);
				}
				if ($k === "assign_subject" || $k === "reminder_subject") { // since its a email subject, we don't need to have a link to the post
				   $v = str_replace("%post_title%", '"' . $post_title . '"', $v);
				}

				foreach($callback_custom_placeholders as $ki=>$vi)
				{
					if(strpos($v, $ki) !== false)
					{
						$v = str_replace($ki,$vi,$v);
					}
				}

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
			$dueDate = '';
			if ( !empty( $actionStep->due_date )) {
			   $dueDate = FCWorkflowBase::format_date_for_display ($actionStep->due_date);
			}
         if ($comment->comment != "")
         {
			   $commentStr .= "<p><strong>" . __('Additionally,', "oasisworkflow") . "</strong> {$nameStr} " . __('added the following comments', "oasisworkflow") . ":</p>" ;
			   $commentStr .= "<p>" . nl2br($comment->comment) . "</p>" ;
         }
			$commentStr .= "<p>" . __('Sign off date', "oasisworkflow") . " : {$signOffDate}</p>" ;
			if ( !empty( $dueDate )) {
			   $commentStr .= "<p>" . __('Due date', "oasisworkflow") . " : {$dueDate} </p>" ;
			}

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
		$email_settings = get_site_option('oasiswf_email_settings') ;
		if( $mails->assign_subject && 
				$mails->assign_content &&
				$email_settings['assignment_emails'] == "yes" ){

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

	static function post_published_notification( $new_status, $old_status, $post )
	{
		$email_settings = get_site_option('oasiswf_email_settings') ;
		
	   // Send email when post is published, also do not send email when post has auto-draft or inherit statuses.
    	if ( $email_settings['post_publish_emails'] == "yes" 
    			&& $old_status != 'publish' && $new_status == 'publish' 
    			&& $post->post_status != 'auto-draft'  
    			&& $post->post_status != 'inherit')
    	{
    		$subject =  __("Your article has been published.", "oasisworkflow");
			$user = get_userdata($post->post_author);
			$to = $user->user_email;

			$msg = sprintf( __( '<div>Hello, <strong>%1$s</strong></div><p>Your article <a href="%2$s" title="%3$s">%3$s</a> has been published on <a href="%4$s" title="%5$s">%5$s</a></p><p>Thanks</p>', 'oasisworkflow' ), $user->display_name, esc_url( $post->guid ), $post->post_title, esc_url(get_bloginfo('url')),  get_bloginfo('name'));
			$message='<html><head></head><body><div class="email_notification_body">'.$msg.'</div></body></html>';

		   FCWorkflowEmail::send_mail($to, $subject, $message);
 	   }
	}
}
?>