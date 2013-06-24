<?php
$selected_post = isset( $_GET['post'] ) ? $_GET["post"] : "";
$histories = $history_workflow->get_workflow_history_all( $selected_post );
$count_posts = $history_workflow->get_history_count($selected_post);
$pagenum = (isset( $_GET['paged'] ) && $_GET["paged"]) ? $_GET["paged"] : 1;
$per_page = 25;
?>
<style type="text/css">.wrap, .wrap h2{font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;}</style>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Workflow History", "oasisworkflow") ;?></h2>
	<div id="view-workflow" class="workflow-history">
		<div class="tablenav">
			<div class="alignleft actions">
				<select id="post_filter">
					<option selected="selected"><?php echo __("View Post/Page Workflow History", "oasisworkflow")?></option>
					<?php
					$wf_posts = $history_workflow->get_workflow_posts();
					if( $wf_posts )
					{
						foreach ($wf_posts as $wf_post) {
							if( isset( $_GET['post'] ) && $_GET["post"] == $wf_post->wfpostid )
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
							if ( $count >= $end )
								break;
							if ( $count >= $start )
							{
								if( $row->assign_actor_id == -1 ){
									$review_rows = $history_workflow->get_review_action_by_history_id($row->ID ) ;
									if( $review_rows ){
										foreach ($review_rows as $review_row) {
											echo "<tr>" ;
											echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>" ;
											echo "<td><a href='post.php?post={$row->post_id}&action=edit'><strong>{$row->post_title}</strong></a></td>" ;
											echo "<td>{$history_workflow->get_user_name($review_row->actor_id)}</td>";
											echo "<td>
													<a href='admin.php?page=oasiswf-admin&wf_id=". $row->workflow_id . "'><strong>{$row->wf_name} ({$row->version})</strong></a>
												</td>";
											echo "<td>{$history_workflow->get_step_name($row)}</td>";
											echo "<td>{$history_workflow->format_date_for_display( $row->create_datetime, "-", "datetime" )}</td>";
											if( $review_row->review_status == "reassigned" ){
												$info = get_option("reassign_{$review_row->ID}") ;
												$signoff_date = $info["sign_off_date"] ;
											}else{
												$signoff_date = $review_row->update_datetime ;
											}
											echo "<td>{$history_workflow->format_date_for_display( $signoff_date, "-", "datetime" )}</td>";
											echo "<td>{$history_workflow->get_review_signoff_status( $row, $review_row )}</td>" ;
											echo "<td class='comments column-comments'>
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
								}else{
									echo "<tr>";
									echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>" ;
									echo "<td>
											<a href='post.php?post={$row->post_id}&action=edit'><strong>{$row->post_title}</strong></a>
											<!--
											<div class='row-actions'>
												<span><a href='#' id='graphic_show'>View</a></span>
											</div>
											-->
										</td>";
									echo "<td>{$history_workflow->get_user_name($row->userid)}</td>";
									echo "<td>
											<a href='admin.php?page=oasiswf-admin&wf_id=". $row->workflow_id . "'><strong>{$row->wf_name} ({$row->version})</strong></a>
										</td>";
									echo "<td>{$history_workflow->get_step_name($row)}</td>";
									echo "<td>{$history_workflow->format_date_for_display( $row->create_datetime, "-", "datetime" )}</td>";
									echo "<td>{$history_workflow->format_date_for_display( $history_workflow->get_signoff_date( $row ), "-", "datetime" )}</td>";
									echo "<td>{$history_workflow->get_signoff_status( $row )}</td>" ;
									echo "<td class='comments column-comments'>
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
