<?php
$selected_tab = 'workflowSettings'; // default tab, if nothing is set
if (isset ( $_GET['tab'] )) { // if something is set, go to that tab
   $selected_tab =  $_GET['tab'];
}

?>
<div class="wrap">
	<?php
       $tabs = array( 'workflowSettings' => __('Workflow Settings', "oasisworkflow"),
       					 'emailSettings' => __('Email Settings', "oasisworkflow") );
       echo '<div id="icon-themes" class="icon32"><br></div>';
       echo '<h2 class="nav-tab-wrapper">';
       foreach( $tabs as $tab => $name ){
           $class = ( $tab == $selected_tab ) ? ' nav-tab-active' : '';
           echo "<a class='nav-tab$class' href='?page=oasiswf-setting&tab=$tab'>$name</a>";

       }
       echo '</h2>';
   	echo '<table class="form-table">';
   	switch ( $selected_tab ){
   		case 'workflowSettings' :
   		   include( OASISWF_PATH . "includes/pages/workflow-settings.php" ) ;
   		   break;
   		case 'emailSettings' :
   		  	include( OASISWF_PATH . "includes/pages/email-settings.php" ) ;
   		  	break;   		   
   	}
   	echo '</table>';
	?>
</div>