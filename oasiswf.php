<?php
/*
 Plugin Name: Oasis Workflow
 Plugin URI: http://www.oasisworkflow.com
 Description: Easily create graphical workflows to manage your work.
 Version: 1.0.2
 Author: Nugget Solutions Inc.
 Author URI: http://www.nuggetsolutions.com

----------------------------------------------------------------------
Copyright 2011-2012 Nugget Solutions Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/


//Install, activate, deactivate and uninstall

define( 'OASISWF_VERSION' , '1.0.2' );
define( 'OASISWF_DB_VERSION','1.0.2');
define( 'OASISWF_PATH', plugin_dir_path(__FILE__) ); //use for include files to other files
define( 'OASISWF_ROOT' , dirname(__FILE__) );
define( 'OASISWF_FILE_PATH' , OASISWF_ROOT . '/' . basename(__FILE__) );
define( 'OASISWF_URL' , plugins_url( '/', __FILE__ ) );
define( 'OASISWF_SETTINGS_PAGE' , add_query_arg( 'page', 'ef-settings', get_admin_url( null, 'admin.php' ) ) );
define( 'OASISWF_SITE_URL' , site_url() );
load_plugin_textdomain('oasiswf-textdomain', false, basename( dirname( __FILE__ ) ) . '/languages' );

//Initialization
class FCInitialization
{
	function  __construct()
	{
		//run on activation of plugin
		register_activation_hook( __FILE__, array('FCInitialization', 'run_on_activation') );

		//run on deactivation of plugin
		register_deactivation_hook( __FILE__, array('FCInitialization', 'run_on_deactivation') );

		//run on uninstall
		register_uninstall_hook(__FILE__, array('FCInitialization', 'run_on_uninstall') );

	}

	static function run_on_activation( $networkwide )
	{
		global $wpdb;
		if (function_exists('is_multisite') && is_multisite())
		{
	        // check if it is a network activation - if so, run the activation function for each blog id
	        if ($networkwide)
	        {
	            $old_blog = $wpdb->blogid;
	            // Get all blog ids
	            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->prefix}blogs");
	            foreach ($blogids as $blog_id)
	            {
	            	switch_to_blog($blog_id);
	                FCInitialization::_run_on_activation();
	            }
	            switch_to_blog($old_blog);
	            return;
	        }
    	}
    	FCInitialization::_run_on_activation();
	}

	static function run_on_deactivation($networkwide)
	{
	    global $wpdb;

	    if (function_exists('is_multisite') && is_multisite())
	    {
	        // check if it is a network activation - if so, run the activation function for each blog id
	        if ($networkwide)
	        {
	            $old_blog = $wpdb->blogid;
	            // Get all blog ids
	            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
	            foreach ($blogids as $blog_id)
	            {
	                switch_to_blog($blog_id);
	                FCInitialization::_run_on_deactivation();
	            }
	            switch_to_blog($old_blog);
	            return;
	        }
	    }
	    FCInitialization::_run_on_deactivation();
	}

	static function run_on_uninstall()
	{
		global $wpdb;
		if (function_exists('is_multisite') && is_multisite())
		{
			//Get all blog ids; foreach them and call the uninstall procedure on each of them
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");

			//Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
			foreach ( $blog_ids as $blog_id )
			{
				switch_to_blog( $blog_id );
				if( $wpdb->query( "SHOW TABLES FROM ".$wpdb->dbname." LIKE '".$wpdb->prefix."fc_%'" ) )
				{
					FCInitialization::_run_on_uninstall();
				}
			}

			//Go back to the main blog and return - so that if not multisite or not network activation, run the procedure once
			restore_current_blog();
			return;
		}
		FCInitialization::_run_on_uninstall();
	}

	function _run_on_activation( )
	{
		$pluginOptions = get_option('oasiswf_info');
		if ( false === $pluginOptions )
		{
			$oasiswf_info=array(
				'version'=>OASISWF_VERSION,
				'db_version'=>OASISWF_DB_VERSION
			);

			$oasiswf_process_info = array(
				'assignment' => OASISWF_URL . 'img/assignment.gif',
				'review' => OASISWF_URL . 'img/review.gif',
				'publish' => OASISWF_URL . 'img/publish.gif'
			);

			$oasiswf_path_info = array(
				'success' => array('Success', 'blue'),
				'failure' => array('Failure', 'red')
			);

			$oasiswf_status = array(
				'assignment' => __('In Progress'),
				'review' => __('In Review'),
				'publish' => __('Ready to Publish')
			);
			$oasiswf_review_decision = array(
				'everyone' => __('Everyone should approve'),
				'atleast' => __('Atleast 50% should approve'),
				'more' => __('More that 50% should approve'),
				'anyone' => __('Anyone should approve'),
			);

			$oasiswf_placeholders = array(
				'%first_name%' => __('first name'),
				'%last_name%' => __('last name'),
				'%post_title%' => __('post title')
			);
			FCInitialization::install_database();
			update_option('oasiswf_info', $oasiswf_info) ;
			update_option('oasiswf_process', $oasiswf_process_info) ;
			update_option('oasiswf_path', $oasiswf_path_info) ;
			update_option('oasiswf_status', $oasiswf_status) ;
			update_option('oasiswf_review', $oasiswf_review_decision) ;
			update_option('oasiswf_placeholders', $oasiswf_placeholders) ;

		}
		else if ( OASISWF_VERSION != $pluginOptions['version'] )
		{
		   FCInitialization::run_on_upgrade();
		}

		if ( !wp_next_scheduled('oasiswf_email_schedule') )
			wp_schedule_event(time(), 'daily', 'oasiswf_email_schedule');
	}

	function run_on_upgrade( )
	{
	   $pluginOptions = get_option('oasiswf_info');
		if ($pluginOptions['version'] == "1.0")
		{
		   FCUtility::owf_logger ("inside upgrading from 1.0");
			FCInitialization::upgrade_database_101();
		}
		else if ($pluginOptions['version'] == "1.0.1")
		{
		   FCUtility::owf_logger ("inside upgrading from 1.0.1");
			// do nothing
		}

		// update the version value
		$oasiswf_info=array(
			'version'=>OASISWF_VERSION,
			'db_version'=>OASISWF_DB_VERSION
		);
		update_option('oasiswf_info', $oasiswf_info) ;
	}

	function _run_on_uninstall()
	{
		if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
			exit();

		global $wpdb;	//required global declaration of WP variable
		delete_option('oasiswf_info');
		delete_option('oasiswf_process');
		delete_option('oasiswf_path');
		delete_option('oasiswf_status');
		delete_option('oasiswf_review');
		delete_option('oasiswf_placeholders');

		$wpdb->query("DELETE FROM {$wpdb->prefix}options WHERE option_name like 'workflow_%'") ;
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fc_workflows");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fc_workflow_steps");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fc_emails");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fc_action_history");
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}fc_action");

	}

	function run_on_add_blog($blog_id, $user_id, $domain, $path, $site_id, $meta )
	{
	    global $wpdb;
	    if (is_plugin_active_for_network(basename( dirname( __FILE__ )) . '/oasiswf.php'))
	    {
	        $old_blog = $wpdb->blogid;
	        switch_to_blog($blog_id);
	        FCInitialization::_run_on_activation();
	        switch_to_blog($old_blog);
	    }
	}

	function run_on_delete_blog($blog_id, $drop )
	{
		global $wpdb;
      switch_to_blog($blog_id);
		FCInitialization::_run_on_uninstall();
	   restore_current_blog();
	}

	function upgrade_database_101()
	{
	   FCUtility::owf_logger("inside upgrade");
		//rename table for multisite support
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$table_name = 'fc_workflows';
		$new_table_name = $wpdb->prefix . 'fc_workflows';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") == $table_name)
		{
		   FCUtility::owf_logger("inside table rename workflows");
			$wpdb->query("RENAME TABLE {$table_name} to  {$new_table_name}");
		}

		$table_name = 'fc_workflow_steps';
		$new_table_name = $wpdb->prefix . 'fc_workflow_steps';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") == $table_name)
		{
			$wpdb->query("RENAME TABLE {$table_name} to  {$new_table_name}");
		}

		$table_name = 'fc_emails';
		$new_table_name = $wpdb->prefix . 'fc_emails';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") == $table_name)
		{
			$wpdb->query("RENAME TABLE {$table_name} to  {$new_table_name}");
		}

		$table_name = 'fc_action_history';
		$new_table_name = $wpdb->prefix . 'fc_action_history';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") == $table_name)
		{
		   FCUtility::owf_logger("inside table rename workflows history");
			$wpdb->query("RENAME TABLE {$table_name} to  {$new_table_name}");
		}

		$table_name = 'fc_action';
		$new_table_name = $wpdb->prefix . 'fc_action';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") == $table_name)
		{
			$wpdb->query("RENAME TABLE {$table_name} to  {$new_table_name}");
		}
	}

	function install_database()
	{
		global $wpdb;
		if (!empty ($wpdb->charset))
        	$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		if (!empty ($wpdb->collate))
        	$charset_collate .= " COLLATE {$wpdb->collate}";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        //fc_workflows table
		$table_name = $wpdb->prefix . 'fc_workflows';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name)
		{
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			      `ID` int(11) NOT NULL AUTO_INCREMENT,
			      `name` varchar(200) NOT NULL,
			      `description` mediumtext,
			      `wf_info` longtext NOT NULL,
			      `version` int(3) NOT NULL default 1,
			      `parent_id` int(11) NOT NULL default 0,
			      `start_date` date NOT NULL,
			      `end_date` date NOT NULL,
			      `is_valid` int(2) NOT NULL default 0,
			      `create_datetime` datetime NOT NULL,
			      `update_datetime` datetime NOT NULL,
			      PRIMARY KEY (`ID`)
	    		){$charset_collate};";
			dbDelta($sql);
		}
        //fc_workflow_steps table
		$table_name = $wpdb->prefix . 'fc_workflow_steps';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name)
		{
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			      `ID` int(11) NOT NULL AUTO_INCREMENT,
			      `step_info` text NOT NULL,
			      `process_info` longtext NOT NULL,
			      `workflow_id` int(11) NOT NULL,
			      `create_datetime` datetime NOT NULL,
			      `update_datetime` datetime NOT NULL,
			      PRIMARY KEY (`ID`),
			      KEY `workflow_id` (`workflow_id`)
	    		){$charset_collate};";
			dbDelta($sql);
		}
        //fc_emails table
		$table_name = $wpdb->prefix . 'fc_emails';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name)
		{
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			    `ID` int(11) NOT NULL AUTO_INCREMENT,
			    `subject` mediumtext,
			    `message` mediumtext,
			    `from_user` int(11),
			    `to_user` int(11),
			    `action` int(2) DEFAULT 1,
			    `history_id` int(11),
			    `send_date` date DEFAULT NULL,
			    `create_datetime` datetime DEFAULT NULL,
			    PRIMARY KEY (`ID`)
	    		){$charset_collate};";
			dbDelta($sql);
		}
        //fc_action_history table
		$table_name = $wpdb->prefix . 'fc_action_history';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name)
		{
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			    `ID` int(11) NOT NULL AUTO_INCREMENT,
			    `action_status` varchar(20) NOT NULL,
			    `comment` longtext NOT NULL,
			    `step_id` int(11) NOT NULL,
			    `assign_actor_id` int(11) NOT NULL,
			    `post_id` int(11) NOT NULL,
			    `from_id` int(11) NOT NULL,
			    `due_date` date DEFAULT NULL,
			    `reminder_date` date DEFAULT NULL,
			    `create_datetime` datetime NOT NULL,
			    PRIMARY KEY (`ID`)
	    		){$charset_collate};";
			dbDelta($sql);
		}
        //fc_action table
		$table_name = $wpdb->prefix . 'fc_action';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name)
		{
			$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			    `ID` int(11) NOT NULL AUTO_INCREMENT,
			    `review_status` varchar(20) NOT NULL,
			    `actor_id` int(11) NOT NULL,
			    `reassign_actor_id` int(11) NOT NULL,
			    `step_id` int(11) NOT NULL,
			    `comments` mediumtext,
			    `due_date` date DEFAULT NULL,
			    `action_history_id` int(11) NOT NULL,
			    `update_datetime` datetime NOT NULL,
			    PRIMARY KEY (`ID`)
	    		){$charset_collate};";
			dbDelta($sql);

		}
		FCInitialization::install_data();
	}

	function install_data()
	{
		/* this code is useful in future when insert example data
		 //global $wpdb;
		 */
	}

	function _run_on_deactivation()
	{
		/*
		 * Mail schedule remove
		 */
		wp_clear_scheduled_hook( 'oasiswf_email_schedule' );
	}

	static function add_css_file($stylesheet_arr)
	{
		if(is_array($stylesheet_arr))
		{
			foreach($stylesheet_arr as $stylesheet)
			{
				$myStyleDir = WP_PLUGIN_DIR. '/oasis-workflow/css/'.$stylesheet.'.css';
				$myStyleUrl = WP_PLUGIN_URL. '/oasis-workflow/css/'.$stylesheet.'.css';
				wp_register_style('fc_'.$stylesheet, $myStyleUrl);
				wp_enqueue_style( 'fc_'.$stylesheet);
			}
		}
	}

	static function add_js_file($jsfile_arr)
	{
		if(is_array($jsfile_arr))
		{
			foreach($jsfile_arr as $jsfile)
			{
				$myJsDir = WP_PLUGIN_DIR . '/oasis-workflow/js/'.$jsfile.'.js';
				$myJsUrl = WP_PLUGIN_URL. '/oasis-workflow/js/'.$jsfile.'.js';
				wp_register_script('fc_'.$jsfile, $myJsUrl);
				wp_enqueue_script( 'fc_'.$jsfile);
			}
		}
	}

	static function add_online_js_file($jsfile_arr)
	{
		if(is_array($jsfile_arr))
		{
			foreach($jsfile_arr as $k => $v )
			{
				if( isset($v['online']) && $v["dev"] == "online" )
					$myJsUrl = $v["file"];
				else
					$myJsUrl = WP_PLUGIN_URL. '/oasis-workflow/js/' . $v["file"] .'.js';
				wp_register_script($k, $myJsUrl);
			}
		}
	}
}

$initialization=new FCInitialization();
/**
 *
 * workflow create / edit
 *
 */
class FCLoadWorkflow
{
	function __construct()
	{
		//init: Runs after WordPress has finished loading but before any headers are send. It is used before sending data send to browser.
		add_action('init', array('FCLoadWorkflow', 'page_load_control'));
		add_action('admin_menu',  array('FCLoadWorkflow', 'register_menu_pages'));
	}

	function page_load_control()
	{
		require_once( OASISWF_PATH . "includes/workflow-base.php" ) ;
		if( isset($_GET['wf-popup']) && $_GET["wf-popup"] )
		{
			global $plugin_workflow ;
			$plugin_workflow = new FCWorkflowCRUD() ;
			require_once( OASISWF_PATH . "includes/pages/subpages/step-info.php" );
		}
	}

	function register_menu_pages()
	{
		$current_role = FCWorkflowBase::get_current_user_role() ;
		$position = FCWorkflowBase::get_menu_position() ;
		add_menu_page(__('Workflow Admin', 'oasiswf'),
					  	__('Workflow Admin', 'oasiswf'),
						'activate_plugins',
						'oasiswf-admin',
						array('FCLoadWorkflow','list_workflows'),
						'');

	    add_submenu_page('oasiswf-admin',
	    				__('Edit Workflows', 'oasiswf'),
	    				__('Edit Workflows', 'oasiswf'),
	    				'activate_plugins',
	    				'oasiswf-admin',
	    				array('FCLoadWorkflow','list_workflows'));

	    add_submenu_page('oasiswf-admin',
	    				__('New Workflow', 'oasiswf'),
	    				__('New Workflow', 'oasiswf'),
	    				'activate_plugins',
	    				'oasiswf-add',
	    				array('FCLoadWorkflow','create_workflow'));

	    add_submenu_page('oasiswf-admin',
	    				__('Settings', 'oasiswf'),
	    				__('Settings', 'oasiswf'),
	    				'activate_plugins',
	    				'oasiswf-setting',
	    				array('FCLoadWorkflow','workflow_settings'));

		add_menu_page(__('Workflows', 'oasiswf'),
						__('Workflows', 'oasiswf'),
						$current_role,
						'oasiswf-inbox',
						array('FCLoadWorkflow','workflow_inbox'),'', $position);

		 add_submenu_page('oasiswf-inbox',
	    				__('Inbox', 'oasiswf'),
	    				__('Inbox', 'oasiswf'),
	    				$current_role,
	    				'oasiswf-inbox',
	    				array('FCLoadWorkflow','workflow_inbox'));

		add_submenu_page('oasiswf-inbox',
						__('Workflow History', 'oasiswf'),
						__('Workflow History', 'oasiswf'),
						$current_role,
						'oasiswf-history',
						array('FCLoadWorkflow','workflow_history'));


	    add_action('admin_print_styles', array('FCLoadWorkflow', 'add_css_files'));
		add_action('admin_print_scripts', array('FCLoadWorkflow', 'add_js_files'));
		add_action('admin_footer', array('FCLoadWorkflow', '_add_js_files'));
	}

	function create_workflow()
	{
		$create_workflow = new FCWorkflowCRUD() ;
		include( OASISWF_PATH . "includes/pages/workflow-create.php" ) ;
	}

	function list_workflows()
	{
		if(isset($_GET['wf_id']) && $_GET["wf_id"]){
			FCLoadWorkflow::create_workflow() ;
		}else{
			$list_workflow = new FCWorkflowList() ;
			include( OASISWF_PATH . "includes/pages/workflow-list.php" ) ;
		}
	}

	function workflow_inbox()
	{
		$inbox_workflow = new FCWorkflowInbox() ;
		include( OASISWF_PATH . "includes/pages/workflow-inbox.php" ) ;
	}

	function workflow_history()
	{
		$history_workflow = new FCWorkflowHistory() ;
		include( OASISWF_PATH . "includes/pages/workflow-history.php" ) ;
	}

	function workflow_settings()
	{
		include( OASISWF_PATH . "includes/pages/workflow-settings.php" ) ;
	}

	function add_css_files($page)
	{
		if( isset($_GET['page']) && $_GET["page"] == "oasiswf"){
			$stylesheet_arr=array('pages/page');
			FCInitialization::add_css_file($stylesheet_arr);
		}
		if( (isset($_GET['page']) && $_GET["page"] == "oasiswf-add") || (isset($_GET['wf_id']) && $_GET["wf_id"])){
			$stylesheet_arr=array('pages/workflow-create','pages/contextMenu','lib/modal/basic','lib/calendar/datepicker');
			wp_enqueue_style( 'thickbox' );
			FCInitialization::add_css_file($stylesheet_arr);
		}

		if( isset($_GET['page']) && $_GET["page"] == "oasiswf-history" ){
			$stylesheet_arr = array('pages/oasiswf-history');
			FCInitialization::add_css_file($stylesheet_arr);
		}

		if( isset($_GET['page']) && $_GET["page"] == "oasiswf-inbox" ){
			$stylesheet_arr = array('pages/page','pages/workflow-inbox');
			FCInitialization::add_css_file($stylesheet_arr);
		}

		if( isset($_GET['page']) && $_GET["page"] == "oasiswf-setting" ){
			$stylesheet_arr = array('pages/subpages/workflow-settings');
			FCInitialization::add_css_file($stylesheet_arr);
		}

		if( isset($_GET['page']) && $_GET["page"] == "oasiswf-admin" ){
			$stylesheet_arr = array('pages/workflow-list');
			FCInitialization::add_css_file($stylesheet_arr);
		}
	}

	function add_js_files()
	{
		if( isset($_GET['page']) && $_GET["page"] == "oasiswf" ){
			$jsfile_arr = array('pages/workflow-list');
			FCInitialization::add_js_file($jsfile_arr);
		}
		if( isset($_GET['page']) && $_GET["page"] == "oasiswf-add" ||
			isset($_GET['wf_id']) && $_GET["wf_id"] ){
			$jsfiles = array(
							'drag-drop-jsplumb' => array('file' => 'pages/drag-drop-jsplumb'),
							'workflow-create' => array('file' => 'pages/workflow-create'),
							'jsPlumb-1.3.9-all-min' => array('file' => 'lib/jquery.jsPlumb-1.3.9-all-min'),
							'jquery-json-2.3' => array('file' => 'lib/jquery.json-2.3'),
							'jquery-simplemodal' => array('file' => 'lib/modal/jquery.simplemodal')
						) ;
			FCInitialization::add_online_js_file($jsfiles);
			echo "<script type='text/javascript'>
						var wf_structure_data = '' ;
						var wfeditable = '' ;
						var wfPluginUrl  = '" . OASISWF_URL . "' ;
					</script>";

		}

		if( isset($_GET['page']) && $_GET["page"] == "oasiswf-inbox" ){
			$jsfile_arr = array('pages/workflow-inbox');
			FCInitialization::add_js_file($jsfile_arr);
		}
	}

	function _add_js_files()
	{
		if( (isset($_GET['page']) && $_GET["page"] == "oasiswf-add") ||
		(isset($_GET['wf_id']) && $_GET["wf_id"] )){
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_script( 'jquery-ui-core' ) ;
			wp_enqueue_script( 'jquery-ui-widget' ) ;
			wp_enqueue_script( 'jquery-ui-mouse' ) ;
			wp_enqueue_script( 'jquery-ui-draggable' ) ;
			wp_enqueue_script( 'jquery-ui-droppable' ) ;
			wp_enqueue_script( 'jquery-ui-sortable' ) ;
			wp_enqueue_script( 'drag-drop-jsplumb' ) ;
			wp_enqueue_script( 'workflow-create' ) ;
			wp_enqueue_script( 'jsPlumb-1.3.9-all-min' ) ;
			wp_enqueue_script( 'jquery-json-2.3' ) ;
			wp_enqueue_script( 'jquery-simplemodal' ) ;
			wp_enqueue_script( 'jquery-ui-datepicker' ) ;
		}
	}
}

/* plugin activation whenenver a new blog is created */
add_action( 'wpmu_new_blog', array( 'FCInitialization', 'run_on_add_blog' ), 10, 6);
add_action( 'delete_blog', array( 'FCInitialization', 'run_on_delete_blog' ), 10, 2);
add_action( 'admin_init', array( 'FCInitialization', 'run_on_upgrade' ));

include( OASISWF_PATH . "oasiswf-utilities.php" ) ;
$fcLoadWorkflow = new FCLoadWorkflow();

include( OASISWF_PATH . "oasiswf-actions.php" ) ;
$fcWorkflowActions = new FCWorkflowActions();

/* ajax */
add_action('wp_ajax_create_new_workflow', array( 'FCWorkflowCRUD', 'create_new_workflow' ) );
add_action('wp_ajax_get_workflow_count', array( 'FCWorkflowCRUD', 'get_workflow_count' ) );
add_action('wp_ajax_step_save', array( 'FCWorkflowCRUD', 'workflow_step_save' ) );
add_action('wp_ajax_get_editinline_html', array( 'FCWorkflowInbox', 'get_editinline_html' ) );
add_action('wp_ajax_get_step_signoff_content', array( 'FCWorkflowInbox', 'get_step_signoff_content' ) );
add_action('wp_ajax_get_reassign_content', array( 'FCWorkflowInbox', 'get_reassign_content' ) );
add_action('wp_ajax_claim_process', array( 'FCWorkflowInbox', 'claim_process' ) );
add_action('wp_ajax_reset_assign_actor', array( 'FCWorkflowInbox', 'reset_assign_actor' ) );
add_action('wp_ajax_get_step_comment', array( 'FCWorkflowInbox', 'get_step_comment' ) );

?>