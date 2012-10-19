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
				FCStepInfo::add_popup_stylesheet() ;
				FCStepInfo::add_popup_script() ;
				call_user_func_array( array( 'FCStepInfo','oasiswf_set_' . $_GET["wf-popup"]), array() ) ;								
			}			
		}
    			
		public function header_html()
		{?><!DOCTYPE html>
		   <html <?php language_attributes(); ?>>
		   <head>
				<meta charset="<?php bloginfo( 'charset' ); ?>" />
				<title><?php echo __("Step Setting");?></title>
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
		
		public function add_popup_stylesheet()
		{
			if( $_GET["wf-popup"] == "step" ) 
				echo "<link rel='stylesheet'href='" . OASISWF_URL . "css/pages/subpages/step-info.css' type='text/css' />";			
		}
		
		public function add_popup_script()
		{
			if( $_GET["wf-popup"] == "step" ) {
				echo "<script type='text/javascript' src='" . OASISWF_URL . "/js/lib/textedit/whizzywig63.js' ></script>";
				echo "<script type='text/javascript' src='" . OASISWF_URL . "/js/pages/subpages/step-info.js' ></script>";
			}
		}
	 }	 
	 $popup = new FCStepInfo ();	 
}
 ?>