<?php
class FCUtility {
	public static function get_workflows_table_name() {
		global $wpdb;
		return $wpdb->base_prefix . "fc_workflows";
	}

	public static function get_workflow_steps_table_name() {
		global $wpdb;
		return $wpdb->base_prefix . "fc_workflow_steps";
	}

	public static function get_action_history_table_name() {
		global $wpdb;
		return $wpdb->prefix . "fc_action_history";
	}

	public static function get_action_table_name() {
		global $wpdb;
		return $wpdb->prefix . "fc_action";
	}

	public static function get_emails_table_name() {
		global $wpdb;
		return $wpdb->prefix . "fc_emails";
	}

	public static function owf_logger( $message )
	{
		if( WP_DEBUG === true )
		{
			if( is_array( $message ) || is_object( $message ) )
			{
				error_log( print_r( $message, true ) );
			}
			else
			{
				error_log( $message );
			}
		}
	}

	public static function owf_donation()
	{
      $str= '<div style="width:100%; float:left;  margin: 0px 50px 5px 7px; padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
                <div style="width:50%; float:left">' .
					 	__("If you find this plugin useful, please consider making a small donation to help contribute to the time invested and for further development. Thanks for your kind support!", "oasisworkflow")
                	. '</div><div style="width:50%; float:right">
						<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_s-xclick">
							<input type="hidden" name="hosted_button_id" value="8YRMFYFEAEBQG">
							<input	type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif"
								border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
							<img alt=""	border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
						</form>
                </div>
             </div>';
		echo $str;
	}

	public static function owf_pro_features()
	{
      $str= '<div style="width:90%; float:left;  margin: 0px 50px 5px 7px; padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
                <div style="width:100%; float:left; align: center">' .
					 	__("If you are looking for additional functionality like \"Multiple Workflows\", \"Revise published content and add Workflow Support to revised content\", \"Auto Submit\", \"Reports\", and much more...", "oasisworkflow")
					 	. '<br/>' .
					 	__("check out our \"Pro\" version at ", "oasisworkflow")
					 	. '<a target="_blank" href="https://www.oasisworkflow.com/pricing-purchase">' .  __("Oasis Workflow Pro", "oasisworkflow") . '</a>'
                	. '</div>
             </div>';
		echo $str;
	}

   public static function owf_dropdown_roles_multi( $selected ) {
   	$r = '';
   	$p = '';

   	$editable_roles = get_editable_roles();

   	foreach ( $editable_roles as $role => $details ) {
   		$name = translate_user_role($details['name'] );
   		if ( is_array($selected) && in_array(esc_attr($role), $selected)) // preselect specified role
   			$p .= "\n\t<option selected='selected' value='" . esc_attr($role) . "'>$name</option>";
   		else
   			$r .= "\n\t<option value='" . esc_attr($role) . "'>$name</option>";
   	}
   	echo $p . $r;
   }

   public static function owf_dropdown_post_status_multi( $selected ) {
   	$r = '';
   	$p = '';

   	foreach ( get_post_stati(array('show_in_admin_status_list' => true)) as $status ) {
   		if ( is_array($selected) && in_array($status, $selected)) // preselect specified status
   			$p .= "\n\t<option selected='selected' value='" . $status . "'>$status</option>";
   		else
   			$r .= "\n\t<option value='" . $status . "'>$status</option>";
   	}
   	echo $p . $r;
   }

   public static function str_array_pos($string, $array)
   {
     for ($i = 0, $n = count($array); $i < $n; $i++)
     {
       if (stristr($string, $array[$i]) !== false)
       {
          return true;
       }
     }
     return false;
   }

	public static function get_post($postId)
	{
	   global $wpdb;
	   $post = null;
	   if (function_exists('is_multisite') && is_multisite()) // to account for multisite
		{
			$blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
			foreach ( $blog_ids as $blog_id )
			{
				switch_to_blog( $blog_id );
            $post = get_post($postId);
            if (!empty( $post)) {
               restore_current_blog();
               return $post;
            }
            restore_current_blog();
			}
		}

		$post = get_post($postId);
		return $post;
	}
	
	/**
	 * Convert a date format to a jQuery UI DatePicker format
	 *
	 * @param string $dateFormat a date format
	 * @return string
	 */
	public static function owf_date_format_to_jquery_ui_format($dateFormat) {
	
		$chars = array(
				// Day
				'd' => 'dd', 'j' => 'd', 'l' => 'DD', 'D' => 'D',
				// Month
				'm' => 'mm', 'n' => 'm', 'F' => 'MM', 'M' => 'M',
				// Year
				'Y' => 'yy', 'y' => 'y'
		);
	
		return strtr((string)$dateFormat, $chars);
	}	
}
?>