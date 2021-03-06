<?php
$selected_post = isset( $_GET['post'] ) ? intval( sanitize_text_field( $_GET["post"] )) : "";
$histories = $history_workflow->get_workflow_history_all( $selected_post );
$count_posts = $history_workflow->get_history_count($selected_post);
$pagenum = (isset( $_GET['paged'] ) && $_GET["paged"]) ? intval( sanitize_text_field( $_GET["paged"] )) : 1;
$per_page = 25;
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Workflow History", "oasisworkflow") ;?></h2>
	<div id="view-workflow" class="workflow-history">
		<div class="tablenav">
			<div class="alignleft actions">
				<select id="post_filter">
					<option selected="selected" value="0"><?php echo __("View Post/Page Workflow History", "oasisworkflow")?></option>
					<?php
					$wf_posts = $history_workflow->get_workflow_posts();
					if( $wf_posts )
					{
						foreach ($wf_posts as $wf_post) {
							if( isset( $_GET['post'] ) && intval( sanitize_text_field( $_GET["post"] )) == $wf_post->wfpostid )
								echo "<option value={$wf_post->wfpostid} selected>{$wf_post->title}</option>" ;
							else
								echo "<option value={$wf_post->wfpostid}>{$wf_post->title}</option>" ;
						}
					}
					?>
				</select>

				<a href="javascript:window.open('<?php echo admin_url('admin.php?page=oasiswf-history&post=')?>' + jQuery('#post_filter').val(), '_self')">
					<input type="button" class="button-secondary action" value="<?php echo __("Filter", "oasisworkflow"); ?>" />
				</a>
				<?php 
				$current_user_role = FCProcessFlow::get_current_user_role() ;
				if ( $current_user_role == "administrator" ){
				?>
					<input type="button" class="button-secondary action" id="owf-delete-history" value="<?php echo __("Delete History", "oasisworkflow"); ?>" />
				<?php 	
				}
				?>
				
			</div>
			<div class="tablenav-pages">
				<?php $history_workflow->get_page_link($count_posts, $pagenum, $per_page);?>
			</div>
		</div>
		<table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
			<thead>
				<?php $history_workflow->get_table_header();?>
			</thead>
			<tfoot>
				<?php $history_workflow->get_table_header();?>
			</tfoot>
			<tbody id="coupon-list">
				<?php
					if($histories):
						$act = array("", "active");
						$count = 0;
						$start = ($pagenum - 1) * $per_page;
						$end = $start + $per_page;
						foreach ($histories as $row){
							$workflow_name = "<a href='admin.php?page=oasiswf-admin&wf_id=". $row->workflow_id . "'><strong>" . $row->wf_name;
							if (!empty( $row->version )) {
							   $workflow_name .= "(" . $row->version . ")";
							}
							$workflow_name .= "</strong></a>";

							if ( $count >= $end )
								break;
							if ( $count >= $start )
							{
							if( $row->assign_actor_id != -1 ){ //assignment and/or publish steps
								echo "<tr>";
								echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>" ;
								echo "<td>
										<a href='post.php?post={$row->post_id}&action=edit'><strong>{$row->post_title}</strong></a>
									</td>";
								echo "<td>{$history_workflow->get_user_name($row->userid)}</td>";
								echo "<td>{$workflow_name}</td>";
								echo "<td>{$history_workflow->get_step_name($row)}</td>";
								echo "<td>{$history_workflow->format_date_for_display( $row->create_datetime, "-", "datetime" )}</td>";
								echo "<td>{$history_workflow->format_date_for_display( $history_workflow->get_signoff_date( $row ), "-", "datetime" )}</td>";
								echo "<td>{$history_workflow->get_signoff_status( $row )}</td>" ;
								echo "<td class='comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid={$row->ID} class='post-com-count' real='history'>
													<span class='comment-count'>{$history_workflow->get_signoff_comment_count($row)}</span>
												</a>
												<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
											</strong>
										</div>
									  </td>" ;
								echo "</tr>";
								}
								if( $row->assign_actor_id == -1 ){ //review step
									$review_rows = $history_workflow->get_review_action_by_history_id($row->ID, "update_datetime" ) ;
									if( $review_rows ){
										foreach ($review_rows as $review_row) {
											echo "<tr>" ;
											echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>" ;
											echo "<td><a href='post.php?post={$row->post_id}&action=edit'><strong>{$row->post_title}</strong></a></td>" ;
											if ($review_row->actor_id == 0) {
												$actor = "System";
											} else {
												$actor = $history_workflow->get_user_name($review_row->actor_id);
											}											
											echo "<td>{$actor}</td>";
											echo "<td>{$workflow_name}</td>";
											echo "<td>{$history_workflow->get_step_name($row)}</td>";
											echo "<td>{$history_workflow->format_date_for_display( $row->create_datetime, "-", "datetime" )}</td>";
											if( $review_row->review_status == "reassigned" ){
												$info = get_option("reassign_{$review_row->ID}") ;
												$signoff_date = $info["sign_off_date"] ;
											}else{
												$signoff_date = $review_row->update_datetime ;
											}
											echo "<td>{$history_workflow->format_date_for_display( $signoff_date, "-", "datetime" )}</td>";
											// If editors' review status is "no_action" (Not acted upon) then set user status as "No action taken"
											if($review_row->review_status == "no_action" || $review_row->review_status == "abort_no_action")
											{
												$review_status = __("No Action Taken", "oasisworkflow");
											}
											else
											{
												if ($history_workflow->get_next_step_status_history($row) == "complete") {
													$review_status = __("Workflow completed","oasisworkflow") ;
												} else {
													$review_status = $history_workflow->get_review_signoff_status( $row, $review_row );
												}
											}
											echo "<td>$review_status</td>" ;
											echo "<td class='comments'>
													<div class='post-com-count-wrapper'>
														<strong>
															<a href='#' actionid={$review_row->ID} class='post-com-count' real='review'>
																<span class='comment-count'>{$history_workflow->get_review_signoff_comment_count($review_row)}</span>
															</a>
															<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
														</strong>
													</div>
												  </td>" ;
											echo "</tr>" ;
											$count++;
										}
									}
								}
							}
							$count++;
						}
					else:
						echo "<tr>" ;
						echo "<td colspan='9' class='no-found-td'><lavel>";
						echo __("No workflow history data found.", "oasisworkflow");
						echo "</label></td>";
						echo "</tr>" ;
					endif;
				?>
			</tbody>
		</table>

		<div class="tablenav">
			<div class="tablenav-pages">
				<?php $history_workflow->get_page_link($count_posts,$pagenum, $per_page);?>
			</div>
		</div>
	</div>
</div>

<div id="post_com_count_content"></div>
