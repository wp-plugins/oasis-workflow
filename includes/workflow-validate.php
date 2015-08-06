<?php
class FCWorkflowValidate extends FCWorkflowBase
{

	static function check_workflow_validate($wfid=null)
	{
		global $wpdb;

		if( $wfid ){
			$wf_id = $wfid ;
			$workflow = FCWorkflowCRUD::get_workflow_by_id( $wf_id ) ;
			$start_date = $workflow->start_date ;
			$end_date = $workflow->end_date ;
			$graphic = $workflow->wf_info ;
			$wfinfo = json_decode($graphic) ;
		}else{
			$wf_id = intval( sanitize_text_field( $_POST["wf_id"] )) ;
			if (isset($_POST["start-date"]) && empty($_POST["start-date"])) {
				$error_message = __( "Start and End date are required.", "oasisworkflow" ) ;
				return $error_message;
			} else {
				$start_date_ui = sanitize_text_field( $_POST["start-date"] );
				$start_date = FCWorkflowCRUD::format_date_for_db_wp_default( $start_date_ui ) ;
			}
			if (isset($_POST["end-date"]) && empty($_POST["end-date"])) {
				$error_message = __( "Start and End date are required.", "oasisworkflow" ) ;
				return $error_message;
			} else {
				$end_date_ui = sanitize_text_field( $_POST["start-date"] );
				$end_date =  FCWorkflowCRUD::format_date_for_db_wp_default( $end_date_ui ) ;
			}
			$graphic = stripcslashes($_POST["wf_graphic_data_hi"]) ;
			$wfinfo = json_decode($graphic) ;
			$workflow = FCWorkflowCRUD::get_workflow_by_id( $wf_id ) ;
		}

		//--------Workflow validation check---------
		$stepCount = 0 ;
		$connCount = 0 ;
		$successCount = 0 ;
		$failCount = 0 ;

		if( $wfinfo->steps ){
			foreach ($wfinfo->steps as $step) {
				if( $step->fc_dbid == "nodefine" ){
					$error_message = __( "Missing step information. Right click on each of the steps to edit step information. Workflow is not active.", "oasisworkflow" ) ;
					return $error_message ;
				}
				$stepCount++ ;
			}
		}


		if( $stepCount == 0 ){
			$error_message = __( "No steps found.", "oasisworkflow" ) ;
			return $error_message ;
		}

		foreach ($wfinfo->conns as $conn) {
			if( $conn->connset->paintStyle->strokeStyle == "blue" ){
				$successCount++ ;
			}else{
				$failCount++ ;
			}
			$connCount++ ;
		}

		if( $connCount == 0 ){
			$error_message = __( "No connections found.", "oasisworkflow" ) ;
			return $error_message ;
		}

		if( $failCount == 0 ){
			$error_message = __( "Please provide failure path for all steps except the first one.", "oasisworkflow" ) ;
			return $error_message ;
		}

		$steps = FCWorkflowCRUD::copy_get_first_last_step($wfinfo);

		if( count($steps["first"]) == 0 && count($steps["last"]) == 0 ){
			$error_message = __( "The workflow doesn't have a valid exit path.<br>
								Items in this workflow will never exit the workflow.<br>
							Please fix the workflow and provide an exit path.", "oasisworkflow" ) ;
			return $error_message ;
		}

		if( count($wfinfo->first_step) > 1 ){
			$error_message = __( 'Multiple steps marked as first step.<br>Workflow can have only one starting point. Please fix the workflow.' , "oasisworkflow" ) ;
			return $error_message ;
		}

		if( count($wfinfo->first_step) == 0 ){
			$error_message = __( 'Starting step not found.<br>Workflow should have a starting point. Please fix the workflow.', "oasisworkflow" ) ;
			return $error_message ;
		}

		//--------Date Check-----------
		$startDataInt = FCWorkflowCRUD::get_date_int($start_date) ;
		$endDataInt = FCWorkflowCRUD::get_date_int($end_date) ;
		if( $startDataInt > $endDataInt ){
			$error_message = __("End date should be greater than the start date.", "oasisworkflow" ) ;
			return $error_message ;
		}

		$where_clause = "ID <> %d && ((start_date <= %s && end_date >= %s) OR (start_date <= %s && end_date >= %s))";
		$result = "";
		
		if( $workflow->parent_id ){
			$sql = "SELECT * FROM " . FCUtility::get_workflows_table_name() . "
					WHERE (ID = %d || parent_id = %d) && $where_clause	" ;
			$result = $wpdb->get_row( $wpdb->prepare( $sql, array( $workflow->parent_id, $workflow->parent_id, $wf_id, $start_date, $start_date, $end_date, $end_date )));
		}else{
			$sql = "SELECT * FROM " . FCUtility::get_workflows_table_name() . "
					WHERE (parent_id = %d) && $where_clause " ;
			$result = $wpdb->get_row( $wpdb->prepare( $sql, array( $wf_id, $wf_id, $start_date, $start_date, $end_date, $end_date )));
		}

		if( count( $result ) ){
			$error_message = __( "The start date or end date is between ", "oasisworkflow" ) . $result->name . "(" . $result->version . ")"  ;
			return $error_message ;
		}
	}

	static function ajax_check_workflow_validate()
	{
		global $wpdb;
		$error_message = "" ;

		$graphic = stripcslashes($_POST["wf_graphic_data_hi"]) ;
		$start_date_ui = sanitize_text_field( $_POST["start-date"] );
		$end_date_ui = sanitize_text_field( $_POST["end-date"] );

		//--------Date Check-----------
		$start_date = FCWorkflowCRUD::format_date_for_db_wp_default( $start_date_ui ) ;
		$end_date = FCWorkflowCRUD::format_date_for_db_wp_default( $end_date_ui ) ;

		$workflow = FCWorkflowCRUD::get_workflow_by_id( $wf_id ) ;
		$where_clause = "ID <> %d && ((start_date <= %s && end_date >= %s) OR (start_date <= %s && end_date >= %s))";
		$result = "";
		
		if( $workflow->parent_id ){
			$sql = "SELECT * FROM " . FCUtility::get_workflows_table_name() . "
					WHERE (ID = %d || parent_id = %d) && $where_clause	" ;
			$result = $wpdb->get_row( $wpdb->prepare( $sql, array( $workflow->parent_id, $workflow->parent_id, $wf_id, $start_date, $start_date, $end_date, $end_date )));
		}else{
			$sql = "SELECT * FROM " . FCUtility::get_workflows_table_name() . "
					WHERE (parent_id = %d) && $where_clause " ;
			$result = $wpdb->get_row( $wpdb->prepare( $sql, array( $wf_id, $wf_id, $start_date, $start_date, $end_date, $end_date )));
		}
		

		$result = $wpdb->get_row( $sql );
		if( count( $result )) {
			$error_message = __( "The start date or end date is between start date and end date of ", "oasisworkflow" ) . $result->name . "(" . $result->version . ")"  ;
		}

		//--------Workflow validation check---------
		$wf_id = intval( sanitize_text_field( $_POST["wfid"] )) ;
		$wfinfo = stripcslashes($_POST["wfinfo"]) ;
		$wfinfo = json_decode($wfinfo) ;
		$steps = FCWorkflowCRUD::copy_get_first_last_step($wfinfo);
		if( count($steps["first"] ) == 0 && count($steps["last"]) == 0 ){
			$error_message = __( "The workflow doesn't have a valid exit path.\n Items in this workflow will never exit the workflow.\n Please fix the workflow and provide an exit path.","oasisworkflow" );
		}

		echo $error_message ;
		exit();
	}
}
?>