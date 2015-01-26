(function($) {
inlineEditPost = {

	init : function(){
		var t = this, qeRow = $('#inline-edit'), bulkRow = $('#bulk-edit');

		t.type = $('table.widefat').hasClass('pages') ? 'page' : 'post';
		t.what = '#post-';

		// prepare the edit rows
		qeRow.keyup(function(e){
			if (e.which == 27)
				return inlineEditPost.revert();
		});
		bulkRow.keyup(function(e){
			if (e.which == 27)
				return inlineEditPost.revert();
		});

		jQuery('a.cancel', qeRow).click(function(){
			return inlineEditPost.revert();
		});
		jQuery('a.save', qeRow).click(function(){
			return inlineEditPost.save(this);
		});
		jQuery('td', qeRow).keydown(function(e){
			if ( e.which == 13 )
				return inlineEditPost.save(this);
		});

		jQuery('a.cancel', bulkRow).click(function(){
			return inlineEditPost.revert();
		});

		jQuery('#inline-edit .inline-edit-private input[value="private"]').click( function(){
			var pw = $('input.inline-edit-password-input');
			if ( $(this).prop('checked') ) {
				pw.val('').prop('disabled', true);
			} else {
				pw.prop('disabled', false);
			}
		});

		// add events
		jQuery( document ).on( "click", "a.editinline", function(){
			var obj = this;
			if(typenow == $(this).attr("real")){
				inlineEditPost.edit(obj);
				return false;
			}else{
				typenow = t.type = $(this).attr("real");
				data = {
						action: 'get_editinline_html' ,
						post_type: $(this).attr("real")
					   };
				jQuery(this).parent().children(".loading").show();
				jQuery.post(ajaxurl, data, function( response ) {
					jQuery(obj).parent().children(".loading").hide();
					jQuery("#wfeditlinecontent").html(response);
					inlineEditPost.edit(obj);
					jQuery(".inline-edit-status").hide() ;
					return false;					
				});
			}
			
		});

		$('#bulk-title-div').parents('fieldset').after(
			$('#inline-edit fieldset.inline-edit-categories').clone()
		).siblings( 'fieldset:last' ).prepend(
			$('#inline-edit label.inline-edit-tags').clone()
		);

		// hiearchical taxonomies expandable?
		$('span.catshow').click(function(){
			$(this).hide().next().show().parent().next().addClass("cat-hover");
		});

		$('span.cathide').click(function(){
			$(this).hide().prev().show().parent().next().removeClass("cat-hover");
		});

		$('select[name="_status"] option[value="future"]', bulkRow).remove();

		$('#doaction, #doaction2').click(function(e){
			var n = $(this).attr('id').substr(2);
			if ( $('select[name="'+n+'"]').val() == 'edit' ) {
				e.preventDefault();
				t.setBulk();
			} else if ( $('form#posts-filter tr.inline-editor').length > 0 ) {
				t.revert();
			}
		});

		$('#post-query-submit').mousedown(function(e){
			t.revert();
			$('select[name^="action"]').val('-1');
		});
	},

	toggle : function(el){
		var t = this;
		$(t.what+t.getId(el)).css('display') == 'none' ? t.revert() : t.edit(el);
	},

	setBulk : function(){
		var te = '', type = this.type, tax, c = true;
		this.revert();

		$('#bulk-edit td').attr('colspan', $('.widefat:first thead th:visible').length);
		$('table.widefat tbody').prepend( $('#bulk-edit') );
		$('#bulk-edit').addClass('inline-editor').show();

		$('tbody th.check-column input[type="checkbox"]').each(function(i){
			if ( $(this).prop('checked') ) {
				c = false;
				var id = $(this).val(), theTitle;
				theTitle = $('#inline_'+id+' .post_title').text() || inlineEditL10n.notitle;
				te += '<div id="ttle'+id+'"><a id="_'+id+'" class="ntdelbutton" title="'+inlineEditL10n.ntdeltitle+'">X</a>'+theTitle+'</div>';
			}
		});

		if ( c )
			return this.revert();

		$('#bulk-titles').html(te);
		$('#bulk-titles a').click(function(){
			var id = $(this).attr('id').substr(1);

			$('table.widefat input[value="' + id + '"]').prop('checked', false);
			$('#ttle'+id).remove();
		});

		// enable autocomplete for tags
		if ( 'post' == type ) {
			// support multi taxonomies?
			tax = 'post_tag';
			$('tr.inline-editor textarea[name="tax_input['+tax+']"]').suggest( 'admin-ajax.php?action=ajax-tag-search&tax='+tax, { delay: 500, minchars: 2, multiple: true, multipleSep: ", " } );
		}
		$('html, body').animate( { scrollTop: 0 }, 'fast' );
	},

	edit : function(id) {
		var t = this, fields, editRow, rowData, status, pageOpt, pageLevel, nextPage, pageLoop = true, nextLevel, cur_format, f;
		t.revert();

		if ( typeof(id) == 'object' )
			id = t.getId(id);

		fields = ['post_title', 'post_name', 'post_author', '_status', 'jj', 'mm', 'aa', 'hh', 'mn', 'ss', 'post_password', 'post_format'];
		if ( t.type == 'page' )
			fields.push('post_parent', 'menu_order', 'page_template');

		// add the new blank row
		editRow = $('#inline-edit').clone(true);

		$('td', editRow).attr('colspan', $('.widefat:first thead th:visible').length);

		if ( $(t.what+id).hasClass('alternate') )
			$(editRow).addClass('alternate');
		$(t.what+id).hide().after(editRow);

		// populate the data
		rowData = $('#inline_'+id);
		if ( !$(':input[name="post_author"] option[value="' + $('.post_author', rowData).text() + '"]', editRow).val() ) {
			// author no longer has edit caps, so we need to add them to the list of authors
			$(':input[name="post_author"]', editRow).prepend('<option value="' + $('.post_author', rowData).text() + '">' + $('#' + t.type + '-' + id + ' .author').text() + '</option>');
		}
		if ( $(':input[name="post_author"] option', editRow).length == 1 ) {
			$('label.inline-edit-author', editRow).hide();
		}

		// hide unsupported formats, but leave the current format alone
		cur_format = $('.post_format', rowData).text();
		$('option.unsupported', editRow).each(function() {
			var $this = $(this);
			if ( $this.val() != cur_format )
				$this.remove();
		});

		for ( f = 0; f < fields.length; f++ ) {
			$(':input[name="' + fields[f] + '"]', editRow).val( $('.'+fields[f], rowData).text() );
		}

		if ( $('.comment_status', rowData).text() == 'open' )
			$('input[name="comment_status"]', editRow).prop("checked", true);
		if ( $('.ping_status', rowData).text() == 'open' )
			$('input[name="ping_status"]', editRow).prop("checked", true);
		if ( $('.sticky', rowData).text() == 'sticky' )
			$('input[name="sticky"]', editRow).prop("checked", true);

		// hierarchical taxonomies
		$('.post_category', rowData).each(function(){
			var term_ids = $(this).text();

			if ( term_ids ) {
				taxname = $(this).attr('id').replace('_'+id, '');
				$('ul.'+taxname+'-checklist :checkbox', editRow).val(term_ids.split(','));
			}
		});

		//flat taxonomies
		$('.tags_input', rowData).each(function(){
			var terms = $(this).text(),
				taxname = $(this).attr('id').replace('_' + id, ''),
				textarea = $('textarea.tax_input_' + taxname, editRow);

			if ( terms )
				textarea.val(terms);

			//textarea.suggest( 'admin-ajax.php?action=ajax-tag-search&tax='+taxname, { delay: 500, minchars: 2, multiple: true, multipleSep: ", " } );
		});

		// handle the post status
		status = $('._status', rowData).text();
		if ( 'future' != status )
			$('select[name="_status"] option[value="future"]', editRow).remove();

		if ( 'private' == status ) {
			$('input[name="keep_private"]', editRow).prop("checked", true);
			$('input.inline-edit-password-input').val('').prop('disabled', true);
		}

		// remove the current page and children from the parent dropdown
		pageOpt = $('select[name="post_parent"] option[value="' + id + '"]', editRow);
		if ( pageOpt.length > 0 ) {
			pageLevel = pageOpt[0].className.split('-')[1];
			nextPage = pageOpt;
			while ( pageLoop ) {
				nextPage = nextPage.next('option');
				if (nextPage.length == 0) break;
				nextLevel = nextPage[0].className.split('-')[1];
				if ( nextLevel <= pageLevel ) {
					pageLoop = false;
				} else {
					nextPage.remove();
					nextPage = pageOpt;
				}
			}
			pageOpt.remove();
		}

		$(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show();
		$('.ptitle', editRow).focus();			
		return false;
	},

	save : function(id) {
		
		var params, fields, page = $('.post_status_page').val() || '';

		if ( typeof(id) == 'object' )
			id = this.getId(id);

		$('table.widefat .inline-edit-save .waiting').show();

		params = {
			action: 'inline-save',
			post_type: typenow,
			post_ID: id,
			edit_date: 'true',
			post_status: page
		};
		
		fields = $('#edit-'+id+' :input').serialize();
		params = fields + '&' + $.param(params);
		// make ajax request
		$.post(ajaxurl, params,
			function(r) {				
				$('table.widefat .inline-edit-save .waiting').hide();				
				if (r) {
					if ( -1 != r.indexOf('<tr') ) {						
						location.reload();
						//$(inlineEditPost.what+id).remove();
						//$('#edit-'+id).before(r).remove();
						//$(inlineEditPost.what+id).hide().fadeIn();
					} else {
						alert("ccc")
						r = r.replace( /<.[^<>]*?>/g, '' );
						$('#edit-'+id+' .inline-edit-save .error').html(r).show();
					}
				} else {
					$('#edit-'+id+' .inline-edit-save .error').html(inlineEditL10n.error).show();
				}
			}
		, 'html');
		return false;
	},

	revert : function(){
		var id = $('table.widefat tr.inline-editor').attr('id');

		if ( id ) {
			$('table.widefat .inline-edit-save .waiting').hide();

			if ( 'bulk-edit' == id ) {
				$('table.widefat #bulk-edit').removeClass('inline-editor').hide();
				$('#bulk-titles').html('');
				$('#inlineedit').append( $('#bulk-edit') );
			} else {
				$('#'+id).remove();
				id = id.substr( id.lastIndexOf('-') + 1 );
				$(this.what+id).show();
			}
		}

		return false;
	},

	getId : function(o) {
		var id = $(o).closest('tr').attr('id'),
			parts = id.split('-');
		return parts[parts.length - 1];
	}
};

$(document).ready(function(){inlineEditPost.init();});
})(jQuery);

jQuery(document).ready(function() {	
	var inbox_obj = "" ;
	jQuery( document ).on( "click", ".inline-edit-save .save", function(){
		return inlineEditPost.save(this);
	});
	jQuery( document ).on( "click", ".inline-edit-save .cancel", function(){
		return inlineEditPost.revert(this);
	});
	
	jQuery( document ).on( "click", ".quick_sign_off", function(){
		inbox_obj = this ;
		task_user = "";
		if (jQuery('#inbox_filter').length > 0)
		{
			task_user = jQuery('#inbox_filter').val(); 
		}
		data = {
				action: 'get_step_signoff_content',
				oasiswf: jQuery(this).attr("wfid"),
				post:jQuery(this).attr("postid"),
				task_user: task_user,
				parent_page: "inbox"
			   };
		jQuery(this).parent().children(".loading").show();
		jQuery.get(ajaxurl, data, function( response ) {
			jQuery("#step_submit_content").html(response);
			setTimeout("call_modal()", 100);			
		});
		call_modal = function(){
			jQuery(inbox_obj).parent().children(".loading").hide();
			jQuery("#new-step-submit-div").owfmodal({
			    onShow: function (dlg) {
			        jQuery(dlg.container).css('height', 'auto');
			        jQuery(dlg.wrap).css('overflow', 'auto'); // or try ;
			        jQuery.modal.update();
			    }					
			});
			jQuery("#due-date").datepicker({ 
				autoSize: true,
				dateFormat: owf_workflow_inbox_vars.dateFormat
			});
		}
	});
	
	jQuery( document ).on( "click", ".reassign", function(){
		inbox_obj = this ;
		task_user = "";
		if (jQuery('#inbox_filter').length > 0)
		{
			task_user = jQuery('#inbox_filter').val(); 
		}		
		data = {
				action: 'get_reassign_content',
				oasiswf: jQuery(this).attr("wfid"),
				task_user: task_user
			   };
		
		jQuery(this).parent().children(".loading").show();
		
		jQuery.post(ajaxurl, data, function( response ) {
			if( response ){
				jQuery("#reassign-div").html(response) ;
				setTimeout("call_reassign_modal()", 100);				
			}
		});
		
		call_reassign_modal = function(){
			jQuery(inbox_obj).parent().children(".loading").hide();
			jQuery("#reassgn-setting").owfmodal();
		}
	});
	
	jQuery( document ).on( "click", ".claim", function(){
		data = {
				action: 'claim_process',
				actionid: jQuery(this).attr("actionid")
			   };
		
		jQuery(this).parent().children(".loading").show();		
		jQuery.post(ajaxurl, data, function( response ) {
			if( response )
				location.reload();				
		});
	});
	
	jQuery(".post-com-count").click(function(){		
		var inbox_obj = this ;
		if(jQuery(this).children("span").html() == 0)return;
		var page_chk = jQuery(this).attr("real") ;
		data = {
				action: 'get_step_comment',
				actionid: jQuery(this).attr("actionid"),
				page_action: page_chk
			   };
		

		jQuery(this).parent().children(".loading").show();
		jQuery(this).hide();
		jQuery.post(ajaxurl, data, function( response ) {
			jQuery("#post_com_count_content").html(response) ;
			setTimeout("call_comment_modal()", 100);			
		});
		
		call_comment_modal = function(){
			jQuery(inbox_obj).parent().children(".loading").hide();
			jQuery("#stepcomment-setting").owfmodal();
		}
	});
});
