<?php

global $workflow_message ;

/*************************************/
/*     Workflow basic functions    */
/*************************************/ 
class FCWorkflowBase
{
	function get_current_user_role()
	{
		global $wp_roles;
		foreach ( $wp_roles->role_names as $role => $name ) :	
			if ( current_user_can( $role ) )
				return $role ;	
		endforeach;
	}
	
	function get_menu_position()
	{
		global $menu ;
		$sp = 0 ; $ep = 0 ;
		foreach ($menu as $k => $v) {
			if( $v[2] == "themes.php" )$ep = $k ;
			if( $v[2] == "edit-comments.php" )$sp = $k ;
			$menu_position[] = $k ;
		}
		for( $i = $ep ;$i > $sp ;$i-- ){
			if( !in_array($i, $menu_position))return $i ;
		}		
	}
	
	function get_workflow($param = null, $getform = "rows")
	{
		global $wpdb;
		$w = ($param["ID"]) ? " ID = " . $param["ID"] : 1 ;
		$w .= ($param["name"]) ? " AND name = '" . $param["name"] ."'" : "" ;
		$w .= ($param["version"]) ? " AND version = '" . $param["version"] ."'" : "" ;
		$w .= ($param["is_valid"]) ? " AND is_valid = " . $param["is_valid"] : "" ;
		
		if($param["ID"])$getform = "row" ;
		
		if($getform == "row")
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM fc_workflows WHERE $w" ) );
		else 
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM fc_workflows WHERE $w ORDER BY ID desc" ) );
					
		return $result;
	}
	
	function get_step($param = null)
	{
		global $wpdb;
		
		$w = ( $param["ID"] ) ? "ID = " . $param["ID"] : 1 ;
		
		if( $param["ID"] )
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM fc_workflow_steps WHERE $w" ) );
		else 
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM fc_workflow_steps" ) );
		
		return $result ;
			
	}
	
	function get_action($param = null, $getform = "rows")
	{
		global $wpdb;
		$w = ($param["ID"]) ? " ID = " . $param["ID"] : 1 ;
		$w .= ($param["action_status"]) ? " AND action_status = '" . $param["action_status"] ."'" : "" ;
		$w .= ($param["step_id"]) ? " AND step_id = " . $param["step_id"] : "" ;
		$w .= ($param["assign_actor_id"]) ? " AND assign_actor_id = " . $param["assign_actor_id"] : "" ;
		$w .= ($param["post_id"]) ? " AND post_id = " . $param["post_id"] : "" ;
		$w .= ($param["from_id"]) ? " AND from_id = " . $param["from_id"] : "" ;
		if($param["ID"])$getform = "row" ;
		
		if($getform == "row")
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM fc_action_history WHERE $w" ) );
		else 
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM fc_action_history WHERE $w ORDER BY create_datetime DESC" ) );
					
		return $result;
	}
	
	function get_review_action($param = null, $getform = "rows")
	{
		global $wpdb;
		$w = ($param["ID"]) ? " ID = " . $param["ID"] : 1 ;
		$w .= ($param["action_history_id"]) ? " AND action_history_id = " . $param["action_history_id"] : "" ;
		$w .= ($param["review_status"]) ? " AND review_status = '" . $param["review_status"] ."'" : "" ;
		$w .= ($param["actor_id"]) ? " AND actor_id = " . $param["actor_id"] : "" ;
		$w .= ($param["reassign_actor_id"]) ? " AND reassign_actor_id = " . $param["reassign_actor_id"] : "" ;
		$w .= ($param["step_id"]) ? " AND step_id = " . $param["step_id"] : "" ;
		
		if($param["ID"])$getform = "row" ;
		
		if($getform == "row")
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM fc_action WHERE $w" ) );
		else 
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM fc_action  WHERE $w ORDER BY ID DESC" ) );
					
		return $result;
	}
	
	function insert_to_table($table, $data)
	{
		global $wpdb;
		$result = $wpdb->insert($table, $data);
		if( $result ){
			
			$row = $wpdb->get_row("SELECT max(ID) as maxid FROM $table");		
			if($row)
				return $row->maxid ;
			else
				return false;
		}else{
			return false;
		}
	}
	
	function get_all_users_with_role($role) {
		$wp_user_search = new WP_User_Search('', '', $role);
		return $wp_user_search->get_results();
	}
	
	function get_users_by_role($role, $frm = "obj")
	{
		if( count( $role ) > 0 )
		{
			foreach ( $role as $k => $v ){
				$getusers = FCWorkflowBase::get_all_users_with_role( $k ) ;
				for( $i = 0; $i < count($getusers); $i++ ){
					$users[] = $getusers[$i] ;
				}			
			}
			
			if( $frm == "obj" ){
				for( $i = 0; $i < count($users); $i++ ){
					$part["ID"] = $users[$i] ;
					$part["name"] = FCWorkflowBase::get_user_name($users[$i]) ;
					//$part_user = get_userdata( $users[$i] ) ;
					//if($part_user->data->user_nicename)
					//	$part["name"] = $part_user->data->user_nicename ;
					//else
					//	$part["name"] = $part_user->data->user_login ;
					$userstr[] =(object) $part ;
				}
				return (object)$userstr;
			}else{
				return $users;
			}
		}
		return "" ;
		
	}
	
	function get_new_version($parentid)
	{
		global $wpdb;
		$row = $wpdb->get_row("SELECT max(version) as maxversion FROM fc_workflows WHERE parent_id=" . $parentid . " OR ID=" . $parentid);
		$current_version = $row->maxversion;
		return $current_version + 1 ;
	}

	function same_as_save($tablename, $iid)
	{
		global $wpdb;
		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $tablename WHERE ID = " .  $iid ) ) ;
		if( $result ){
			foreach ( $result as $k => $v ) {
				if( $k == "ID" ) continue ;
				$data[$k] = $v ;				
			}
			$newId = FCWorkflowBase::insert_to_table($tablename, $data) ;
			if( $newId )
				return $newId ;
			else
				return false ;
		}else{
			return false;
		}
	}
	
	function get_date_int($ddate=null, $frm="-")
	{
		$ddate = ( $ddate ) ? $ddate : current_time( 'mysql', 0 ) ;
		$arr = explode($frm, $ddate) ;
		return $arr[0] * 10000 + $arr[1] * 100 + $arr[2] * 1   ;
	}
		
	function format_date_for_db($ddate, $frm="/")
	{
		$arr = explode( $frm, $ddate ) ;
		return $arr[2] . "-" . $arr[0] . "-" . $arr[1] ;
	}
	
	function format_date_for_display($ddate, $frm="-", $dateform="date")
	{
		if( $dateform == "date" ){
			if( $ddate == "0000-00-00" ) return "";
			if( $ddate ){
				$arr = explode( $frm, $ddate ) ;
				return $arr[1] . "/" . $arr[2] . "/" . $arr[0] ;
			}
		}else{
			$s_ddate = explode( " ", $ddate ) ;
			
			if( $s_ddate[0] == "0000-00-00" ) return "";
			
			if( $s_ddate[0] ){
				$arr = explode( $frm, $s_ddate[0] ) ;
				return $arr[1] . "/" . $arr[2] . "/" . $arr[0] . " " . $s_ddate[1] ;
			}
		}
	}
	
	function get_page_link($count_posts,$pagenum,$per_page=20)
	{
		$allpages=ceil($count_posts / $per_page);
		$base= add_query_arg( 'paged', '%#%' );
		$page_links = paginate_links( array(
			'base' => $base,
			'format' => '',
			'prev_text' => __('&laquo;'),
			'next_text' => __('&raquo;'),
			'total' => $allpages,
			'current' => $pagenum
		));
		$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
				number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
				number_format_i18n( min( $pagenum * $per_page, $count_posts ) ),
				number_format_i18n( $count_posts ),
				$page_links
				);
		echo $page_links_text;
	}
	
	function get_postcount_in_wf($wfid)
	{
		global $wpdb;
		$sql = "SELECT DISTINCT(A.post_id) 
					FROM 
						(SELECT AA.post_id, BB.workflow_id  
							FROM 
							(SELECT * FROM fc_action_history WHERE action_status='assignment') AS AA 
							LEFT JOIN 
							fc_workflow_steps AS BB 
							ON AA.step_id=BB.ID) as A 
					WHERE A.workflow_id=$wfid" ;
		$result = $wpdb->get_results( $sql ) ;
		return count( $result );
		//return $wfid ;
	}

	function get_pre_next_date($ddate, $frm="next", $days=1)
	{
		$date = new DateTime($ddate);
		
		$dstamp = $date->format("U");

		if( $frm == "next" )
			$dstamp = $dstamp + 3600 * 24 * $days ;
		else
			$dstamp = $dstamp - 3600 * 24 * $days ;
			
		return gmdate("Y-m-d", $dstamp) ;
	}
	
	function get_assigned_post($postid = null, $frm = "rows")
	{
		global $wpdb;
		$user_id = get_current_user_id();
		
		if( $postid )
			$w = "WHERE (assign_actor_id = $user_id OR actor_id = $user_id) AND post_id = " . $postid ;
		else			
			$w = "WHERE assign_actor_id = $user_id OR actor_id = $user_id" ;
		
		$sql = "SELECT A.*, B.review_status, B.actor_id, B.reassign_actor_id, B.step_id as review_step_id, B.action_history_id, B.update_datetime FROM 
							(SELECT * FROM fc_action_history WHERE action_status = 'assignment') as A 
							LEFT OUTER JOIN 
							(SELECT * FROM fc_action WHERE review_status = 'assignment') as B 
							ON A.ID = B.action_history_id $w" ;
		if( $frm == "rows" )
			$result = $wpdb->get_results( $sql ) ;
		else
			$result = $wpdb->get_row( $sql ) ;
			
		return $result;	
	}
	
	function get_pre_next_action($fromid)
	{
		$action = FCWorkflowBase::get_action( array("ID" => $fromid ) ) ;
		if($action->action_status == "processed"){
			return $action->ID ;
		}else{
			FCWorkflowBase::get_pre_next_action($action->from_id);
		}
	}
	
	function get_pre_action($wfid)
	{
		$action = FCWorkflowBase::get_action( array( "ID" => $wfid ) ) ;	
		if( $action->from_id == 0 ){
			return $action->ID ;
		}else{
			return FCWorkflowBase::get_pre_next_action($action->from_id);
		}			
	}

	function get_comment_count($actionid)
	{
		$action = FCWorkflowInbox::get_action( array("ID" => $actionid ) ) ;
		$i = 0 ;
		if( $action ){
			$comments = json_decode($action->comment) ;
			if($comments){
				foreach ($comments as $comment) {
					if($comment->comment)$i++ ;
				}
			}
		}
		return $i ;
	}
	
	function get_gpid_dbid($wpinfo, $stepid, $frm="")
	{
		if( is_object( $wpinfo ) ){
			$wf_steps = $wpinfo->steps ;
		}else{
			if( is_numeric( $wpinfo ) ){
				$workflow = FCWorkflowBase::get_workflow( array( "ID" => $wpinfo ) ) ;
				$info = json_decode( $workflow->wf_info ) ;
				$wf_steps = $info->steps ;
			}else{
				$info = json_decode( $wpinfo ) ;
				$wf_steps = $info->steps ;
			}			
		}
		
		if( $wf_steps ){
			if( is_numeric( $stepid ) ){				
				foreach ( $wf_steps as $k => $v ){				
					if( $stepid == $v->fc_dbid ){
						if( $frm == "lbl" )
							return $v->fc_label ;
						if( $frm == "process" )
							return $v->fc_process ;
							
						return $v->fc_addid ;		
					}
				}				
			}else{
				if( $frm == "lbl" )
					return $wf_steps->$stepid->fc_label ;
				if( $frm == "process" )
					return $wf_steps->$stepid->fc_process ;
				return $wf_steps->$stepid->fc_dbid ;				
			}
		}
		
		return false;	
	}
	
	function get_process_steps($stepid, $direct="source")
	{
		$step = FCProcessFlow::get_step( array( "ID" => $stepid ) ) ;
		if( $step ){
			$workflow = FCProcessFlow::get_workflow( array( "ID" => $step->workflow_id ) ) ;
			if( $workflow )
			{
				$info = json_decode( $workflow->wf_info );
				$conns = $info->conns ;
				$stepgpid = FCProcessFlow::get_gpid_dbid( $info, $stepid );
				$all_path = get_option("oasiswf_path") ;
				foreach ($all_path as $k => $v) {
					$path[$v[1]] = $k ;
				}
								
				if( $conns ){
					if( $direct == "source" )
						foreach ($conns as $k => $v){
							if( $stepgpid == $v->sourceId ){
								$color = $v->connset->paintStyle->strokeStyle ;
								$steps[$path[$color]][FCProcessFlow::get_gpid_dbid($info, $v->targetId )] = FCProcessFlow::get_gpid_dbid($info, $v->targetId, "lbl" ) ;
							}
						}
					else{
						foreach ($conns as $k => $v){
							if( $stepgpid == $v->targetId ){
								$color = $v->connset->paintStyle->strokeStyle ;
								$steps[$path[$color]][FCProcessFlow::get_gpid_dbid($info, $v->sourceId)] =  FCProcessFlow::get_gpid_dbid($info, $v->sourceId, "lbl") ;
							}
						}
					}
					if( count($steps) > 0 )	return $steps ;
				}				
			}
		}
		return false;
	}

	function get_user_name($userid)
	{
		$user = get_userdata($userid) ;
		if( $user )return $user->data->display_name ;
	}
}

/*************************************/
/*     Workflow validate             */
/*************************************/
include( OASISWF_PATH . "includes/workflow-validate.php" ) ;

/*************************************/
/*     Workflow create               */
/*************************************/
include( OASISWF_PATH . "includes/workflow-crud.php" ) ;

/*************************************/
/*     Workflow list                 */
/*************************************/
include( OASISWF_PATH . "includes/workflow-list.php" ) ;

/*************************************/
/*     Workflow inbox                */
/*************************************/
include( OASISWF_PATH . "includes/workflow-inbox.php" ) ;

/*************************************/
/*     Workflow History              */
/*************************************/
include( OASISWF_PATH . "includes/workflow-history.php" ) ;

/*************************************/
/*      Workflow flow process        */
/*************************************/
include( OASISWF_PATH . "includes/process-flow.php" ) ;


if( $_POST["save_action"] == "workflow_save" )
{
	FCWorkflowCRUD::save();
}

if( $_POST["save_action"] == "workflow_as_save" )
{
	FCWorkflowCRUD::as_save();
}

if( $_GET["page"] == "oasiswf-admin" && $_GET["action"] == "delete" )
{
	FCWorkflowList::delete();
}
?>