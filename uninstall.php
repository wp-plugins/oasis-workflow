<?php

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
exit();

delete_option('oasiswf_info');
delete_option('oasiswf_process');
delete_option('oasiswf_path');
delete_option('oasiswf_status');
delete_option('oasiswf_review');
delete_option('oasiswf_placeholders');

global $wpdb;	//required global declaration of WP variable
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name like 'workflow_%'") ;

$table_arr=array(
                'fc_workflows'
                ,'fc_workflow_steps'
                ,'fc_emails'
                ,'fc_action_history'
                ,'fc_action'
                );

foreach($table_arr as $table_name)
{
   $sql = "DROP TABLE ". $table_name;
	$wpdb->query($sql);
}
?>