<?php
/**

 */
If (! Class_Exists ( 'FCStepInfo' )) {

	/**
	 *
	 */
	class FCStepInfo
	{
		function __construct()
		{
			if($_GET["wf-popup"])
			{
				call_user_func_array( array( 'FCStepInfo','oasiswf_set_' . $_GET["wf-popup"]), array() ) ;
			}
		}

		public function header_html()
		{?><!DOCTYPE html>
		   <html <?php language_attributes(); ?>>
		   <head>
				<meta charset="<?php bloginfo( 'charset' ); ?>" />
				<title><?php echo __("Step Setting", "oasisworkflow");?></title>
		   </head>
		   <body>
		   <div id="wrapper" class="hfeed">
		   		<ul>
		       <?php
		}

		public function footer_html()
		{
	             ?>
		       </ul>
		    </div>
		    </body>
		<?php
		}

		public function oasiswf_set_step()
		{
			include( OASISWF_PATH . "includes/pages/subpages/step-info-content.php" ) ;
		}

	 }
	 $popup = new FCStepInfo ();
}
 ?>