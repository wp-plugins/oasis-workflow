<?php
wp_enqueue_script( 'owf-workflow-history',
                   OASISWF_URL. 'js/pages/subpages/history_graphic.js',
                   '',
                   OASISWF_VERSION,
                   true);

?>
<?php
global $wpdb, $chkResult ;
$sql = "SELECT C.ID, C.wf_info
			FROM (
				(SELECT * FROM " . FCUtility::get_action_history_table_name() . " WHERE ID = $chkResult) AS A
				LEFT JOIN " . FCUtility::get_workflow_steps_table_name() . " AS B
				ON A.step_id = B.ID
				LEFT JOIN " . FCUtility::get_workflows_table_name() . " AS C
				ON B.workflow_id = C.ID
			)" ;
$workflow = $wpdb->get_row( $sql ) ;
if( $workflow ){

	$sql = "SELECT * FROM " . FCUtility::get_action_history_table_name() . " WHERE ID <= $chkResult AND (action_status = 'processed' OR action_status = 'assignment') AND post_id = {$_GET['post']} ORDER BY ID" ;
	$processes = $wpdb->get_results( $sql ) ;

	if( $processes ){

		$startid = "" ;
		foreach ($processes as $process) {
			if( $startid ){
				$newconns[] = FCProcessFlow::get_connection($workflow, $startid, $process->step_id) ;
			}
			$startid = $process->step_id ;
		}

		$currentStepId = FCProcessFlow::get_gpid_dbid($workflow->wf_info, $startid ) ;

		$wf_info = $workflow->wf_info ;
		//$wf_info = json_decode( $workflow->wf_info ) ;
		//unset($wf_info->conns) ;

		//if( $newconns ){
		//	$newconns = (object)$newconns ;
		//	$wf_info->conns = $newconns ;
		//}
		//$strWfInfo = json_encode( $wf_info ) ;
	}

	echo "<script type='text/javascript'>
			var wfPluginUrl  = '" . OASISWF_URL . "' ;
			var stepinfo='{$wf_info}' ;
			var currentStepGpId='{$currentStepId}' ;
		</script>" ;
}
?>
<div id="workflow-area" style="position:relative;width:100%;"></div>
<br class="clear">
