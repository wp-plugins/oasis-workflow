<?php
$wfactions = $inbox_workflow->get_assigned_post() ;
$count_posts = count($wfactions);				
$pagenum=($_GET["paged"]) ? $_GET["paged"] : 1;	
$per_page=($per_page) ? $per_page : 10;

$posteditable = current_user_can('edit_others_posts') ;
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2><?php echo __("Inbox"); ?></h2>	
	<div id="workflow-inbox">
		<div class="tablenav">
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
					$wfstatus = get_option( "oasiswf_status" ) ; 
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
								if( $wfaction->assign_actor_id == -1 ){
									$stepId = $wfaction->review_step_id;
								}else{									
									$stepId = $wfaction->step_id;
								}
								$step = $inbox_workflow->get_step( array('ID' => $stepId ) ) ;
								$workflow = $inbox_workflow->get_workflow( array( "ID" => $step->workflow_id ) );
								
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
												echo "<span><a href='post.php?post={$wfaction->post_id}&action=edit&oasiswf={$wfaction->ID}' class='edit' real={$wfaction->post_id}>Edit</a></span>&nbsp;|&nbsp;" ;
												echo "<span>
															<a href='#' class='editinline' real='{$post->post_type}'>" . __("Quick Edit") . "</a>
															<span class='loading'>$sspace</span>
													</span>&nbsp;|&nbsp; ";
											}	
												
												echo "<span><a href='" . get_permalink($wfaction->post_id) . "'>" . __("View") . "</a></span>&nbsp;|&nbsp;";
											if($posteditable){	
												echo "<span>
														<a href='#' wfid='$wfaction->ID' postid='$wfaction->post_id' class='quick_sign_off'>" . __("Sign Off") . "</a>
														<span class='loading'>$sspace</span>
													</span>&nbsp;|&nbsp;" ;
											}	
												echo "<span>
														<a href='#' wfid='$wfaction->ID' class='reassign'>" . __("Reassign") . "</a>
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
						echo __("Hurray! No assignments");
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
