<?php
class FCWorkflowList extends FCWorkflowBase
{
	static function delete()
	{
		global $wpdb;
		$wf_id = $_GET["wf_id"] ;
		FCWorkflowList::delete_step_by_wfid( $wf_id ) ;
		$result = $wpdb->get_results( $wpdb->prepare( "DELETE FROM fc_workflows WHERE ID = " . $wf_id ) );
		wp_redirect( admin_url( 'admin.php?page=oasiswf-admin' ) );
		exit();	
	}
	
	static function delete_step_by_wfid( $wfid )
	{
		global $wpdb;
		$wf = FCWorkflowList::get_workflow( array( "ID" => $wfid ) ) ;
		if( $wf ){
			$wfinfo = $wf->wf_info ;
			if( $wfinfo ) {
				$wf_info = json_decode( $wfinfo ) ;
				foreach ($wf_info->steps as $k => $v) {
					if( $v->fc_dbid == "nodefine" ) continue;
					$result = $wpdb->get_results( $wpdb->prepare( "DELETE FROM fc_workflow_steps WHERE ID = " . $v->fc_dbid ) );
				}
			}
		}	
	}
	
	static function get_table_header()
	{
		echo "<tr>";
		echo "<th scope='col' class='manage-column check-column' ><input type='checkbox'></th>";
		echo "<th>" . __("Title") . "</th>";
		echo "<th>" . __("Version") . "</th>";
		echo "<th>" . __("Start Date") . "</th>";
		echo "<th>" . __("End Date") . "</th>";
		echo "<th>" . __("Post/Pages in workflow") . "</th>";	
		echo "<th>" . __("Is Valid?") . "</th>";
		echo "</tr>";
	}
	
	static function get_workflow_list($action=null)
	{
		global $wpdb;
		$currenttime = date("Y-m-d") ;
		if( $action == "all" )
			return FCWorkflowList::get_workflow();	

		if( $action == "active" )
			$sql = "SELECT * FROM fc_workflows WHERE start_date <= '$currenttime' AND end_date >= '$currenttime' AND is_valid = 1" ;
		
		if( $action == "inactive" )
			$sql = "SELECT * FROM fc_workflows WHERE NOT(start_date <= '$currenttime' AND end_date >= '$currenttime' AND is_valid = 1)" ;
			
		return $wpdb->get_results( $sql ) ;
	}
	
	static function get_workflow_count()
	{
		global $wpdb;
		$currenttime = date("Y-m-d") ;
		$sql = "SELECT 
					SUM(ID > 0) as wfall, 
					SUM(start_date <= '$currenttime' AND end_date >= '$currenttime' AND is_valid = 1) as wfactive, 
					SUM(NOT(start_date <= '$currenttime' AND end_date >= '$currenttime' AND is_valid = 1)) as wfinactive 
				FROM fc_workflows" ;
		return $wpdb->get_row( $sql ) ;
	}
}
?>