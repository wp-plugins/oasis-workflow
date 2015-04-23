<div id='new-workflow-create-check' class='owf-hidden'>
	<p><?php echo __("The basic version of this plugin supports only one workflow. You can edit the out of the box workflow by going to \"Edit Workflow\".", "oasisworkflow");?></p>
	<p><?php echo __("However, if you are looking to create multiple workflows, check out the \"Pro\" version of this plugin at -", "oasisworkflow");?>
		<a target="_blank" href="https://www.oasisworkflow.com/pricing-purchase"><?php echo __("Oasis Workflow Pro", "oasisworkflow");?></a>
	</p>
	<div class="right button-link-spacing">
		<a href="admin.php?page=oasiswf-admin" id="new_workflow_cancel"><?php echo __("Close", "oasisworkflow"); ?></a>
	</div>
</div>
<script type="text/javascript">
   jQuery(document).ready(function() {
   	//----------loading modal--------------
   	if(!jQuery("#wf_id").val()){
   		if(navigator.appName == "Netscape"){
   			show_workflow_create_modal() ;
   		}else{
   			setTimeout("show_workflow_create_modal()", 500);
   		}
   	}
   });

	function show_workflow_create_modal(){
		jQuery('#new-workflow-create-check').modal({
		    containerCss: {
		        padding: 0,
		        width: 450
		    },
		    onShow: function (dlg) {
		        jQuery(dlg.container).css('height', 'auto');
		        jQuery(dlg.wrap).css('overflow', 'auto'); // or try ;
		        jQuery.modal.update();
		    },
		    onClose: function () {
		    	window.location = jQuery('#new_workflow_cancel').attr('href');
		    }
		});
		jQuery(".modalCloseImg").hide() ;
	}
</script>