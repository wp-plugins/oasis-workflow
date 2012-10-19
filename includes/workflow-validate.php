<?php
class FCWorkflowValidate extends FCWorkflowBase
{
	
	static function check_workflow_validate($wfid=null)
	{	
		global $wpdb;
		
		if( $wfid ){
			$wf_id = $wfid ;
			$workflow = FCWorkflowCRUD::get_workflow( array("ID" => $wf_id ) ) ;
			$start_date = $workflow->start_date ;
			$end_date = $workflow->end_date ;
			$graphic = $workflow->wf_info ;
			$wfinfo = json_decode($graphic) ;
		}else{
			$wf_id = $_POST["wf_id"] ;
			$start_date = FCWorkflowCRUD::format_date_for_db( $_POST["start-date"] ) ;
			$end_date =  FCWorkflowCRUD::format_date_for_db( $_POST["end-date"] ) ;
			$graphic = stripcslashes($_POST["wf_graphic_data_hi"]) ;
			$wfinfo = json_decode($graphic) ;
			$workflow = FCWorkflowCRUD::get_workflow( array("ID" => $wf_id ) ) ;
		}			
		
		//--------Workflow validation check---------
		$stepCount = 0 ;
		$connCount = 0 ;
		$successCount = 0 ;
		$failCount = 0 ;
		
		if( $wfinfo->steps ){
			foreach ($wfinfo->steps as $step) {
				if( $step->fc_dbid == "nodefine" ){
					$error_message = "Missing step information. Workflow is not active." ;	
					return $error_message ;
				}
				$stepCount++ ;
			}
		}
		
		
		if( $stepCount == 0 ){
			$error_message = "No steps found." ;	
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
			$error_message = "No connections found." ; 
			return $error_message ;
		}
		
		if( $failCount == 0 ){
			$error_message = "Please provide failure path for all steps except the first one." ;
			return $error_message ;
		}
		
		$steps = FCWorkflowCRUD::copy_get_first_last_step($wfinfo);
		
		if( count($steps["first"]) == 0 && count($steps["last"]) == 0 ){
			$error_message = "The workflow doesn't have a valid exit path.<br>
								Items in this workflow will never exit the workflow.<br>
							Please fix the workflow and provide an exit path." ;
			return $error_message ;
		}
		
		if( count($wfinfo->first_step) > 1 ){
			$error_message = 'Multiple steps marked as first step.<br>Workflow can have only one starting point. Please fix the workflow.' ;
			return $error_message ;
		}
		
		if( count($wfinfo->first_step) == 0 ){
			$error_message = 'Starting step not found.<br>Workflow should have a starting point. Please fix the workflow.' ;
			return $error_message ;
		}		
		
		//--------Date Check-----------
		$startDataInt = FCWorkflowCRUD::get_date_int($start_date) ;
		$endDataInt = FCWorkflowCRUD::get_date_int($end_date) ;
		if( $startDataInt > $endDataInt ){
			$error_message = "End date should be greater than the start date." ;
			return $error_message ;
		}
		
		$w = "ID <> " . $wf_id . " && ((start_date <= '$start_date' && end_date >= '$start_date') OR (start_date <= '$end_date' && end_date >= '$end_date'))" ;
		
		if( $workflow->parent_id ){
			$sql = "SELECT * FROM fc_workflows 
					WHERE (ID = $workflow->parent_id || parent_id = $workflow->parent_id) && $w	" ;
		}else{
			$sql = "SELECT * FROM fc_workflows 
					WHERE (parent_id = $wf_id) && $w " ;
		}
		
		$result = $wpdb->get_row( $sql );
		if( count( $result ) ){
			$error_message = "The start date or end date is between " . $result->name . "(" . $result->version . ")"  ;
			return $error_message ;
		}		
	}
	
	static function ajax_check_workflow_validate()
	{
		global $wpdb;	
		$error_message = "" ;	
		
		$graphic = stripcslashes($_POST["wf_graphic_data_hi"]) ;		
		
		//--------Date Check-----------
		$start_date = FCWorkflowCRUD::format_date_for_db( $_POST["start-date"] ) ;
		$end_date = FCWorkflowCRUD::format_date_for_db( $_POST["end-date"] ) ;
		
		$workflow = FCWorkflowCRUD::get_workflow( array("ID" => $wf_id ) ) ;
		$w = "ID <> " . $wf_id . " && ((start_date <= '$start_date' && end_date >= '$start_date') OR (start_date <= '$end_date' && end_date >= '$end_date'))" ;
		
		if( $workflow->parent_id ){
			$sql = "SELECT * FROM fc_workflows 
					WHERE (ID = $workflow->parent_id || parent_id = $workflow->parent_id) && $w	" ;
		}else{
			$sql = "SELECT * FROM fc_workflows 
					WHERE (parent_id = $wf_id) && $w " ;
		}
		
		$result = $wpdb->get_row( $sql );
		if( count( $result ) )$error_message = "The start date or end date is between start date and end date of " . $result->name . "(" . $result->version . ")"  ;
		
		//--------Workflow validation check---------
		$wf_id = $_POST["wfid"] ;		
		$wfinfo = stripcslashes($_POST["wfinfo"]) ;
		$wfinfo = json_decode($wfinfo) ;
		$steps = FCWorkflowCRUD::copy_get_first_last_step($wfinfo);
		if( count($steps["first"]) == 0 && count($steps["last"]) == 0 ){
			$error_message = "The workflow doesn't have a valid exit path.\n Items in this workflow will never exit the workflow.\n Please fix the workflow and provide an exit path." ;
		}

		echo $error_message ;
		exit();
	}
}
?>