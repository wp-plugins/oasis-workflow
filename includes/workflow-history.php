<?php
class FCWorkflowHistory extends FCWorkflowBase
{
	static function get_table_header()
	{
		$order = ( $_GET["order"] == "desc" ) ? "asc" : "desc" ;
		
		if( $_GET["orderby"] == "post_title" )
			$post_order_class = $_GET["order"] ;
		else
			$post_order_class = "" ;
			
		if( $_GET["orderby"] == "wf_name" )
			$wf_order_class = $_GET["order"] ;
		else
			$wf_order_class = "" ;
			
		if( $_GET["orderby"] == "assign_actor" )
			$assign_order_class = $_GET["order"] ;
		else
			$assign_order_class = "" ;
			
		if( $_GET["orderby"] == "due_date" )
			$due_date_order_class = $_GET["order"] ;
		else
			$due_date_order_class = "" ;
			
		if( $_GET["orderby"] == "reminder_date" )
			$reminder_date_order_class = $_GET["order"] ;
		else
			$reminder_date_order_class = "" ;
			
		$wherepost = ( $_GET["post"] ) ? "&post=" . $_GET["post"] : "" ;
		
		echo "<tr>";
		echo "<th scope='col' class='manage-column check-column' ><input type='checkbox'></th>";
		echo "<th scope='col' class='sorted $post_order_class'>
				<a href='admin.php?page=oasiswf-history&orderby=post_title&order=$order" . $wherepost . "'>
					<span>". __("Title") . "</span>
					<span class='sorting-indicator'></span>
				</a>
			</th>" ;
		echo "<th>" . __("Actor") . "</th>" ; 
		echo "<th scope='col' class='sorted $wf_order_class'>
				<a href='admin.php?page=oasiswf-history&orderby=wf_name&order=$order" . $wherepost . "'>
					<span>". __("Workflow(version)") . "</span>
					<span class='sorting-indicator'></span>
				</a>
			</th>" ;
		echo "<th>" . __("Step") . "</th>";
		echo "<th scope='col' class='sorted $create_date_order_class'>
				<a href='admin.php?page=oasiswf-history&orderby=create_datetime&order=$order" . $wherepost . "'>
					<span>". __("Assigned date") . "</span>
					<span class='sorting-indicator'></span>
				</a>
			</th>" ;		
		echo "<th scope='col'>". __("Sign Off Date") . "</th>" ;
		echo "<th scope='col'>". __("Result") . "</th>" ;
		echo "<th scope='col' class='history-comment' width='70px'>". __("Comments") . "</th>" ;
		echo "</tr>";
	}
	
	static function get_workflow_history_all($postid=null)
	{
		global $wpdb;
		$orderby = ( $_GET['orderby'] ) ? " ORDER BY {$_GET['orderby']} {$_GET['order']}" : "  ORDER BY A.ID DESC" ;
		$w = "action_status!='abort_no_action' AND action_status!='complete' AND action_status!='cancelled'" ;
		if( $postid )$w .= " AND post_id=" . $postid ;	
		$sql = "SELECT A.* , B.post_title, C.ID as userid, C.display_name as assign_actor, D.step_info, D.workflow_id, D.wf_name, D.version 
					FROM 
						((SELECT * FROM fc_action_history WHERE $w) AS A
						LEFT JOIN
						{$wpdb->posts} AS B
						ON  A.post_id = B.ID 
						LEFT JOIN 
						{$wpdb->users} AS C 
						ON A.assign_actor_id = C.ID
						LEFT JOIN 
						(SELECT AA.*, BB.name as wf_name, BB.version FROM fc_workflow_steps AS AA LEFT JOIN fc_workflows AS BB ON AA.workflow_id = BB.ID) AS D 
						ON A.step_id = D.ID) 
					{$orderby}								
					" ;	
		$result = $wpdb->get_results( $sql ) ;
		return $result;	
	}
	
	static function get_history_count($postid=null)
	{
		global $wpdb;
		$w = "action_status!='abort_no_action' AND action_status!='complete'" ;
		if( $postid )$w .= " AND post_id=" . $postid ;	
		$sql = "SELECT A.*  
					FROM 
						((SELECT * FROM fc_action_history WHERE $w) AS A						
						LEFT JOIN 
						fc_action AS C 
						ON A.ID = C.action_history_id) 							
					" ;	
		$result = $wpdb->get_results( $sql ) ;
		return count($result);	
	}

	static function get_step_name($row)
	{
		if( $row->action_status == "submitted" )return "submit" ;
		$info = $row->step_info;
		if( $info ){
			$stepinfo = json_decode( $info )	;
			if( $stepinfo )
				return $stepinfo->step_name ;
		}
		return "";
	}
	
	static function get_workflow_posts()
	{
		global $wpdb ;
		$sql = "SELECT DISTINCT(A.post_id) as wfpostid , B.post_title as title 
					FROM fc_action_history AS A 
						LEFT JOIN 
						{$wpdb->posts} AS B 
						ON  A.post_id = B.ID   
					GROUP BY A.post_id" ;
		$result = $wpdb->get_results( $sql ) ;
		return $result;	
	}
	
	static function get_signoff_date($row)
	{
		if( $row->action_status == "complete" ) return $row->create_datetime ;
		if( $row->action_status == "claim_cancel" ){
			$claimed_row = FCWorkflowHistory::get_action(array("action_status" => "claimed", "step_id" => $row->step_id, "post_id" => $row->post_id, "from_id" => $row->from_id ), "row") ;
			return $claimed_row->create_datetime ;	
		}
		$action = FCWorkflowHistory::get_action( array( "from_id" => $row->ID ), "row" );
		if( $action )
			return $action->create_datetime ;
	}

	static function get_review_actors($reaction)
	{
		$user = get_userdata( $reaction->actor_id ) ;
		return $user->data->display_name;
	}
	
	static function get_process_result($fromStep, $toStep)
	{
		$fromsteps = FCWorkflowHistory::get_process_steps($fromStep) ;
		if( $fromsteps && $fromsteps["success"] )
		{			
			foreach ($fromsteps["success"] as $k => $v) {
				
				if( $k == $toStep )return "success" ;
			}
		}

		if( $fromsteps && $fromsteps["failure"] )
		{			
			foreach ($fromsteps["failure"] as $k => $v) {
				if( $k == $toStep )return "failure" ;
			}
		}	
	}
	
	static function get_signoff_status($row)
	{
		
		if( $row->action_status == "submitted" ) return "Submitted" ;
		if( $row->action_status == "aborted" ) return "Aborted" ;
		if( $row->action_status == "claim_cancel" )return "Unclaimed" ;
		if( $row->action_status == "claimed" )return "Claimed" ;
		if( $row->action_status == "reassigned" )return "Reassigned" ;
		$nextHistory = FCWorkflowHistory::get_action( array( "from_id" => $row->ID ), "row" ) ;
		if( !$nextHistory )return "";
		if( $nextHistory->action_status == "complete" ) return "Workflow completed" ;
		if( $nextHistory->action_status == "cancelled" ) return "Cancelled" ;
		$step_info = json_decode($row->step_info) ;
		$process = $step_info->process ;
		
		$fromStep = $row->step_id ;
		$toStep = $nextHistory->step_id ;				
		$pro_result = FCWorkflowHistory::get_process_result($fromStep, $toStep) ;
		
		if( $process == "review" ){
			if($pro_result == "success")return "Approved" ;
			if($pro_result == "failure")return "Rejected" ;
		}
		if($pro_result == "success")return "Completed" ;
		if($pro_result == "failure")return "Unable to Complete" ;
	}
	
	static function get_review_signoff_status($row, $review_row)
	{
		if( $review_row->review_status == "reassigned" )return "reassigned" ;
		
		$fromStep = $row->step_id ;
		$toStep = $review_row->step_id ;
		if( !($fromStep &&  $toStep ))return "" ;
		$step_info = json_decode($row->step_info) ;
		$process = $step_info->process ;
		
		$pro_result = FCWorkflowHistory::get_process_result($fromStep, $toStep) ;
		
		if( $process == "review" ){
			if($pro_result == "success")return "Approved" ;
			if($pro_result == "failure")return "Rejected" ;
		}
		if($pro_result == "success")return "Complete" ;
		if($pro_result == "failure")return "Unable to Complete" ;
		
	}
	
	static function get_signoff_comment_count($row)
	{
		if( $row->action_status == "claimed" ||  $row->action_status == "claim_cancel" || $row->action_status == "reassigned" || $row->action_status == "complete" )return ;
		$nextHistory = FCWorkflowHistory::get_action( array( "from_id" => $row->ID ), "row" ) ;
		return FCWorkflowHistory::get_comment_count($nextHistory->ID) ;
	}
	
	static function get_review_signoff_comment_count($review_row)
	{
		if($review_row->review_status == "reassigned")return "" ;
		$i = 0 ;
		if( $review_row ){
			$comments = json_decode($review_row->comments) ;
			if($comments){
				foreach ($comments as $comment) {
					if($comment->comment)$i++ ;
				}
			}
		}
		return $i ;
	}
}
?>