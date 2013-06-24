<?php
$selected_user = isset( $_GET['user'] ) ? $_GET["user"] : null;
$wfactions = $inbox_workflow->get_assigned_post( null, $selected_user ) ;
$count_posts = count($wfactions);
$pagenum = (isset($_GET['paged']) && $_GET["paged"]) ? $_GET["paged"] : 1;
$per_page = 10;
$posteditable = current_user_can('edit_others_posts') ;
$current_user_role = FCProcessFlow::get_current_user_role() ;
$current_user_id = get_current_user_id();

FCUtility::owf_donation();
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Inbox", "oasisworkflow"); ?></h2>
	<div id="workflow-inbox">
		<div class="tablenav">
		<?php if ( $current_user_role == "administrator" ){?>
			<div class="alignleft actions">
				<select id="inbox_filter">
				<option value=<?php echo $current_user_id;?> selected="selected"><?php echo __("View inbox of ", "oasisworkflow")?></option>
					<?php
					$assigned_users = $inbox_workflow->get_assigned_users();
					if( $assigned_users )
					{
						foreach ($assigned_users as $assigned_user) {
							if( (isset( $_GET['user'] ) && $_GET["user"] == $assigned_user->ID) )
								echo "<option value={$assigned_user->ID} selected>{$assigned_user->display_name}</option>" ;
							else
								echo "<option value={$assigned_user->ID}>{$assigned_user->display_name}</option>" ;

						}
					}
					?>
				</select>

				<a href="javascript:window.open('<?php echo admin_url('admin.php?page=oasiswf-inbox&user=')?>' + jQuery('#inbox_filter').val(), '_self')">
					<input type="button" class="button-secondary action" value="<?php echo __("Show", "oasisworkflow"); ?>" />
				</a>
			</div>
		<?php }?>
			<ul class="subsubsub"></ul>
			<div class="tablenav-pages">
				<?php $inbox_workflow->get_page_link($count_posts,$pagenum, $per_page);?>
			</div>
		</div>
		<table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
			<thead>
				<?php $inbox_workflow->get_table_header();?>
			</thead>
			<tfoot>
				<?php $inbox_workflow->get_table_header();?>
			</tfoot>
			<tbody id="coupon-list">
				<?php
					$wfstatus = get_site_option( "oasiswf_status" ) ;
					$sspace = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
					if($wfactions):
						$count = 0;
						$start = ($pagenum - 1) * $per_page;
						$end = $start + $per_page;
						foreach ($wfactions as $wfaction){
							if ( $count >= $end )
								break;
							if ( $count >= $start )
							{
								$post = get_post($wfaction->post_id);
								$user = get_userdata( $post->post_author ) ;
								$stepId = $wfaction->step_id;
								if ($stepId <= 0 || $stepId == "" ) {
									$stepId = $wfaction->review_step_id;
								}
								$step = $inbox_workflow->get_step_by_id( $stepId ) ;
								$workflow = $inbox_workflow->get_workflow_by_id( $step->workflow_id );

								$chk_claim = $inbox_workflow->check_claim($wfaction->ID) ;

								echo "<tr id='post-{$wfaction->post_id}' class='post-{$wfaction->post_id} post type-post status-pending format-standard hentry category-uncategorized alternate iedit author-other'> " ;
								echo "<th scope='row' class='check-column'><input type='checkbox' name='post[]' value={$wfaction->post_id}></th>" ;

								echo "<td><strong>{$post->post_title}";
											 _post_states( $post ) ;
										echo "</strong>" ;
										if( $chk_claim ){
											echo "<div class='row-actions'>
													<span>
														<a href='#' class='claim' actionid={$wfaction->ID}>Claim</a>
														<span class='loading'>$sspace</span>
													</span>
												</div>" ;
										}else{
											echo "<div class='row-actions'>" ;
											if($posteditable){
												echo "<span><a href='post.php?post={$wfaction->post_id}&action=edit&oasiswf={$wfaction->ID}&user={$selected_user}' class='edit' real={$wfaction->post_id}>" . __("Edit", "oasisworkflow"). "</a></span>&nbsp;|&nbsp;" ;
												echo "<span>
															<a href='#' class='editinline' real='{$post->post_type}'>" . __("Quick Edit", "oasisworkflow") . "</a>
															<span class='loading'>$sspace</span>
													</span>&nbsp;|&nbsp; ";
											}

												echo "<span><a href='" . get_permalink($wfaction->post_id) . "'>" . __("View", "oasisworkflow") . "</a></span>&nbsp;|&nbsp;";
											if($posteditable){
												echo "<span>
														<a href='#' wfid='$wfaction->ID' postid='$wfaction->post_id' class='quick_sign_off'>" . __("Sign Off", "oasisworkflow") . "</a>
														<span class='loading'>$sspace</span>
													</span>&nbsp;|&nbsp;" ;
											}
												echo "<span>
														<a href='#' wfid='$wfaction->ID' class='reassign'>" . __("Reassign", "oasisworkflow") . "</a>
														<span class='loading'>$sspace</span>
													</span>
												</div>";
												get_inline_data($post);
										}
								echo "</td>";
								echo "<td>{$post->post_type}</td>" ;
								echo "<td>{$inbox_workflow->get_user_name($user->ID)}</td>" ;
								echo "<td>{$workflow->name}</td>" ;
								echo "<td>" . FCProcessFlow::get_gpid_dbid( $workflow->ID, $stepId, 'lbl' ) . "</td>" ;
								echo "<td>". $wfstatus[FCProcessFlow::get_gpid_dbid( $workflow->ID, $stepId, 'process' )] ."</td>" ;
								echo "<td>" . $inbox_workflow->format_date_for_display($wfaction->due_date) . "</td>" ;
								echo "<td class='comments column-comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid={$wfaction->ID} class='post-com-count'>
													<span class='comment-count'>{$inbox_workflow->get_comment_count($wfaction->ID)}</span>
												</a>
												<span class='loading'>$sspace</span>
											</strong>
										</div>
									  </td>" ;
								echo "</tr>" ;
							}
							$count++;
						}
					else:
						echo "<tr>" ;
						echo "<td class='hurry-td' colspan='9'>
								<label class='hurray-lbl'>";
						echo __("Hurray! No assignments", "oasisworkflow");
						echo "</label></td>" ;
						echo "</tr>" ;
					endif;
				?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php $inbox_workflow->get_page_link($count_posts,$pagenum, $per_page);?>
			</div>
		</div>
	</div>
</div>
<span id="wfeditlinecontent"></span>
<div id ="step_submit_content"></div>
<div id="reassign-div"></div>
<div id="post_com_count_content"></div>
