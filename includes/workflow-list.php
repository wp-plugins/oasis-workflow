<?php
class FCWorkflowList extends FCWorkflowBase
{
	static function delete()
	{
		global $wpdb;
		$wf_id = $_GET["wf_id"] ;
		FCWorkflowList::delete_step_by_wfid( $wf_id ) ;
		$result = $wpdb->get_results( $wpdb->prepare( "DELETE FROM " . FCUtility::get_workflows_table_name() . " WHERE ID = %d", $wf_id ) );
		wp_redirect( network_admin_url( 'admin.php?page=oasiswf-admin' ) );
		exit();
	}

	static function delete_step_by_wfid( $wfid )
	{
		global $wpdb;
		$wf = FCWorkflowList::get_workflow_by_id( $wfid ) ;
		if( $wf ){
			$wfinfo = $wf->wf_info ;
			if( $wfinfo ) {
				$wf_info = json_decode( $wfinfo ) ;
				foreach ($wf_info->steps as $k => $v) {
					if( $v->fc_dbid == "nodefine" ) continue;
					$result = $wpdb->get_results( $wpdb->prepare( "DELETE FROM " . FCUtility::get_workflow_steps_table_name() . " WHERE ID = %d", $v->fc_dbid ) );
				}
			}
		}
	}

	static function get_table_header()
	{
		echo "<tr>";
		echo "<th scope='col' class='manage-column check-column' ><input type='checkbox'></th>";
		echo "<th>" . __("Title", "oasisworkflow") . "</th>";
		echo "<th>" . __("Version", "oasisworkflow") . "</th>";
		echo "<th>" . __("Start Date", "oasisworkflow") . "</th>";
		echo "<th>" . __("End Date", "oasisworkflow") . "</th>";
		echo "<th>" . __("Post/Pages in workflow", "oasisworkflow") . "</th>";
		echo "<th>" . __("Is Valid?", "oasisworkflow") . "</th>";
		/*echo "<th>" . __("Is Auto Submit?", "oasisworkflow") . "</th>";*/
		echo "</tr>";
	}

	static function get_workflow_list($action=null)
	{
		global $wpdb;
		$currenttime = date("Y-m-d") ;
		if( $action == "all" )
			return FCWorkflowList::get_all_workflows();

		if( $action == "active" )
			$sql = "SELECT * FROM " . FCUtility::get_workflows_table_name() . " WHERE start_date <= '$currenttime' AND end_date >= '$currenttime' AND is_valid = 1" ;

		if( $action == "inactive" )
			$sql = "SELECT * FROM " . FCUtility::get_workflows_table_name() . " WHERE NOT(start_date <= '$currenttime' AND end_date >= '$currenttime' AND is_valid = 1)" ;

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
				FROM " . FCUtility::get_workflows_table_name();
		return $wpdb->get_row( $sql ) ;
	}
}
?>