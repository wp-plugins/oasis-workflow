<?php
//----------------
$action = (isset($_GET['action']) && $_GET["action"]) ? $_GET["action"] : "all" ;
$workflows = $list_workflow->get_workflow_list( $action ) ;
$wcclass[$action] = 'class="current"';
$wfcount = $list_workflow->get_workflow_count() ;
//----------------
$count_posts = count($workflows);
$pagenum=(isset($_GET['paged']) && $_GET["paged"]) ? $_GET["paged"] : 1;
$per_page=15;
?>
<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
	<h2>Edit Workflows<a href="admin.php?page=oasiswf-add" class="add-new-h2"><?php echo __("Add New"); ?></a></h2>
	<div id="view-workflow">
		<div class="tablenav">
			<ul class="subsubsub">
				<?php
				$active_val = isset($wcclass["active"])? $wcclass["active"] : "";
				$inactive_val = isset($wcclass["inactive"])? $wcclass["inactive"] : "";
				echo '<li class="all"><a href="admin.php?page=oasiswf-admin"' . $wcclass["all"] . ' >' . __('All') .
						'<span class="count">(' . $wfcount->wfall . ')</span></a></li>';
				echo ' | <li class="all"><a href="admin.php?page=oasiswf-admin&action=active"' . $active_val . '>' . __('Active') .
						'<span class="count">(' . $wfcount->wfactive . ')</span></a> </li>';
				echo ' | <li class="all"><a href="admin.php?page=oasiswf-admin&action=inactive"' . $inactive_val . '>' . __('Inactive') .
						'<span class="count">(' . $wfcount->wfinactive . ')</span></a> </li>';
				?>
			</ul>
			<div class="tablenav-pages">
				<?php $list_workflow->get_page_link($count_posts,$pagenum, $per_page);?>
			</div>
		</div>
		<table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
			<thead>
				<?php $list_workflow->get_table_header();?>
			</thead>
			<tfoot>
				<?php $list_workflow->get_table_header();?>
			</tfoot>
			<tbody id="coupon-list">
				<?php
					if($workflows):
						$act = array("", "active");
						$count = 0;
						$start = ($pagenum - 1) * $per_page;
						$end = $start + $per_page;
						foreach ($workflows as $wf){
							if ( $count >= $end )
								break;
							if ( $count >= $start )
							{
								$postcount = $list_workflow->get_postcount_in_wf( $wf->ID ) ;
								$valid = ( $wf->is_valid ) ? "Yes" : "No" ;
								/*$autoSubmit = ( $wf->is_auto_submit ) ? "Yes" : "No" ;*/
								echo "<tr class='alternate author-self status-publish format-default iedit'>";
								echo "<th scope='row' class='check-column'><input type='checkbox' name='linkcheck[]' value='1'></th>";
								echo "<td>
										<a href='admin.php?page=oasiswf-admin&wf_id=" . $wf->ID . "'><strong>{$wf->name}</strong></a>
										<div class='row-actions'>
											<span><a href='admin.php?page=oasiswf-admin&wf_id=" . $wf->ID . "'>Edit</a></span>";
											if( !$postcount )
												echo "&nbsp;|&nbsp;<span><a href='admin.php?page=oasiswf-admin&wf_id=" . $wf->ID . "&action=delete'>Delete</span>";
								echo "</div>
									</td>";
								echo "<td>{$wf->version}</td>";
								echo "<td>{$list_workflow->format_date_for_display ( $wf->start_date )}</td>";
								echo "<td>{$list_workflow->format_date_for_display( $wf->end_date)}</td>";
								echo "<td>{$postcount}</td>";
								echo "<td>{$valid}</td>";
								/*echo "<td>{$autoSubmit}</td>";*/
								echo "</tr>";
							}
							$count++;
						}
					else:
						if( $action == "all" ){
							$msg = "<label>" . __("You don't have any workflows. Let's go ") ."</label>
								<a href='admin.php?page=oasiswf-add'>" . __("create one"). "</a> !" ;
						}else{
							$msg = __("You don't have $action workflows") ;
						}
						echo "<tr>" ;
						echo "<td colspan='8' class='no-found-lbl'>$msg</td>" ;
						echo "</tr>" ;
					endif;
				?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php $list_workflow->get_page_link($count_posts,$pagenum, $per_page);?>
			</div>
		</div>
	</div>
</div>
