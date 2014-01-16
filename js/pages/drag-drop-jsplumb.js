/*
Copyright (c) 2012  John Goodman  www.unverse.net

Permission is hereby granted, free of charge, to any person obtaining a copy of this software 
and associated documentation files (the "Software"), to deal in the Software without restriction, 
including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, 
and/or sell copies of the Software, and to permit persons to whom the Software 
is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included 
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. 

IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, 
DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, 
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE 
OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
/*
Copyright (c) 2013 Simon Porritt, http://jsplumb.org/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/ 
var jQueryCgmp = jQuery.noConflict();
(function (jQuery) {
	jQuery(document).ready(function(jQuery) {	
		//-------------first setting------------------------
		var stepNum = 0;
		var wfConn = {}; // action connection object
		var connSet = { "path": "blue" }; //default success connection
		var selectedStep = {};
		var newconnection = false ; // For checking 'new connection' or 'edit connection'
		jQuery("#workflow-area").css("height", jQuery(".fc_action").css("height"));
		jQuery( ".dropable-area" ).droppable({
			activeClass: "ui-state-hover",
			hoverClass: "ui-state-active",
			drop: function( event, ui ) {
				//if(jQuery(ui.draggable).attr('class') == "w ui-draggable ui-droppable" ){return;} //No workflow info box droppable
				if(ui.draggable.context.className.indexOf("w ui-draggable ui-droppable") >= 0) {return;} //No workflow info box droppable
				stepNum = 0;
				var getNum = get_step_id_num(stepNum);
				var lbl = jQuery(ui.draggable).children().html() ;
				get_step_lbl_inx(lbl);
				var Y = event.originalEvent.pageY - 150 ;
				var X = event.originalEvent.pageX - 170 ;
				var p = {"fc_addid" : "step" + getNum,
						 "fc_label"	: obj_lbl,
						 "fc_dbid" : "nodefine",
						 "fc_process" : lbl,
						 "fc_position" : [Y + "px", X + "px"]};			
				createStep(p, "new");
				set_step_chaned_status() ; // We can know what workflow graphic was changed. 
			}		
		});		
		
		jQuery( "#wfsortable" ).sortable();
		jQuery( "#workflow-info-area" ).sortable();
		//jQuery( "#process-info-div" ).draggable(); 
		window.workflowGraphic = {			
			init :function() {							
				jsPlumb.importDefaults({
					Endpoint : ["Dot", {radius:2}],				
					//overlays:[ ["PlainArrow", {location:1, width:20, length:12} ]],
					connectorStyle:{ strokeStyle:"blue", lineWidth:2 },
					HoverPaintStyle : {strokeStyle:"#42a62c", lineWidth:8 },
					paintStyle:{
						lineWidth:3,
						strokeStyle: "blue"
					},
					anchor:"Continuous",
					ConnectionOverlays : [
						[ "Arrow", { 
							location:1,
							id:"arrow",
		                    length:0,
		                    foldback:0.8
						} ]
					],
					maxConnections:-1
				});		
			}	
		};
		
	    //------------------step creating----------------    
		var get_step_id_num = function(cn){
	    	if (jQuery("#step" + cn).length > 0){
	    		stepNum++;
	    		get_step_id_num(stepNum);
			}
	    	return stepNum;
	    }
		
		var lbl_inx = 0 ;
		var obj_lbl = "" ;
		var get_step_lbl_inx = function(lbl){
			var chk = true;
			jQuery(".fc_action .w").each(function(){
				var obj_lbl = jQuery(this).children("label").html() ;
				if(jQuery.trim(obj_lbl) == jQuery.trim(lbl))chk = false;
			});
			
			if(chk){
				obj_lbl = lbl;
				lbl_inx = 0 ;
				return ;
			}
			lbl_inx++ ;
			var temp = lbl.split("-") ;
			get_step_lbl_inx( temp[0] + "-" + lbl_inx) ;
			
		}
		
		var __createStep = function(objid) {    	
	    	//jsPlumb.draggable(jsPlumb.getSelector("#"+objid));
	    	jsPlumb.draggable(jsPlumb.getSelector("#"+objid),{containment:"parent"});
	    	jsPlumb.makeTarget(jsPlumb.getSelector("#"+objid), { anchor:"Continuous" } );    	
			jsPlumb.makeSource(jQuery('#ep-'+objid), {
				parent:jQuery('#ep-'+objid).parent(),
				anchor:"Continuous",			
				//connectorStyle:{ strokeStyle:"blue", lineWidth:2 },
				maxConnections:-1,			
			});		
	    }    
	    var createStep = function(param, act) {
	    	jQuery("#workflow-area").append(	"<div class='w' id='" + param["fc_addid"] + "'" +
						    				"' real='" + act + "'" +
						    				" db-id=" + param["fc_dbid"] + 
						    				" process-name='" + param["fc_process"] + "'>" +
						    				"<img alt='' src='" + wfPluginUrl + "img/" + param["fc_process"] +".gif' />" +
						    				"<label>" + param["fc_label"] + "</label>" + 
						    			"</div>" );
	    	
			jQuery("#" + param["fc_addid"]).append("<div class='ep' id='ep-" + param["fc_addid"] + "'></div>");
			jQuery("#" + param["fc_addid"]).css({'top': param["fc_position"][0], 'left': param["fc_position"][1]});
			__createStep(param["fc_addid"]);
	    };
	    
	  //-----------workflow info getting----------------- 
	    
	    _get_workflow_info = function(){
	    	if(!check_created_workflow())return false ;
			c = jsPlumb.getConnections();
			if(!c.length){
	//			alert("No connections found.");
	//			return false;
			}
			
			var workflow_datas = {}, steps = {}, conns = {},error_chk = true ;
			
			jQuery(".fc_action .w").each(function(){			
				var iid = jQuery(this).attr('id');
				
	//			if(jQuery("#" + iid).attr("db-id") == "nodefine"){
	//				error_chk = false ;
	//			}
				
				steps[iid] = {"fc_addid" : iid,
							 "fc_label" : jQuery("#" + iid + " label").html(),
							 "fc_dbid" : jQuery("#" + iid).attr("db-id"),
							 "fc_process" : jQuery("#" + iid).attr("process-name"),
							 "fc_position" : [jQuery("#"+iid).css('top'), jQuery("#"+iid).css('left')]
				};
			});
			
			for( var i = 0; i < c.length ; i++ ){
				// connector - StateMachine - specified in jsPlumb lib 
				conns[i] = {
							"sourceId": c[i].sourceId, 
							"targetId": c[i].targetId, 
							"connset": {"connector": "StateMachine", "paintStyle": c[i].paintStyleInUse}
						} ;
			}
			
			workflow_datas["steps"] = steps ;
			workflow_datas["conns"] = conns ;
			workflow_datas["first_step"] = get_first_step() ;
	
			return jQuery.toJSON(workflow_datas);
			//return workflow_datas.toJSON() ;
	   } 
	    
	   get_first_step = function(){
		   var i = 0 ;
		   var first_step = [] ;
		   jQuery(".fc_action .w").each(function(){		  
			   if(jQuery(this).attr("first_step") == "yes"){
				   first_step.push(jQuery(this).attr("id")) ;
				   i++ ;
			   }		  
		   }) ;
		   return first_step ;
	   }
	    
	   check_created_workflow = function(){
		   c = jsPlumb.getConnections();
		   var temp = {} ;
		   var ttemp = {} ;
		   for( var i = 0; i < c.length ; i++ ){
			   temp[c[i].sourceId + "_" + c[i].targetId] = c[i].paintStyleInUse.strokeStyle ;
		   }
		   
		   for( var i = 0; i < c.length ; i++ ){
			   if( temp[c[i].targetId + "_" + c[i].sourceId] && c[i].paintStyleInUse.strokeStyle == temp[c[i].targetId + "_" + c[i].sourceId] ){
				   var slbl = jQuery("#" + c[i].sourceId).children("label").html() ;
				   var tlbl = jQuery("#" + c[i].targetId).children("label").html() ;
				   alert(drag_drop_jsplumb_vars.pathBetween + " " + slbl + " " + drag_drop_jsplumb_vars.stepAnd + " " + tlbl + " " + drag_drop_jsplumb_vars.incorrect) ;
				   return false;
			   }
		   }
		   
		   return true;
	   }
	   //------------workflow making When load -------------------
	   
	   _graphic_make = function(param){    	
		   	var wfinfo = {};
		   	wfinfo = jQuery.parseJSON(param);
		   	if(typeof(wfinfo) != 'object'){
		   		alert("graphic data is bad");
		   		return;
		   	}
		   	for( var w in wfinfo["steps"]){
		   		createStep(wfinfo["steps"][w],"old");
		   	}
		   	
		   	for( var k in wfinfo["conns"]){
		   		jsPlumb.connect({
						source:wfinfo["conns"][k]["sourceId"],
						target:wfinfo["conns"][k]["targetId"]
					}, wfinfo["conns"][k]["connset"]);
		   	}
	   }
	   
	   set_first_step = function(param){
		   var wfinfo = {};
		   var first_step = [];
		   wfinfo = jQuery.parseJSON(param);
		   if( wfinfo && wfinfo.first_step )first_step = wfinfo.first_step ;
		   if( first_step.length ){
			   jQuery(".fc_action .w").each(function(){		  
				   var iid = jQuery(this).attr("id") ;			   
				   if(arr_contains(first_step, iid)) {
					   jQuery(this).attr("first_step", "yes") ;
					   jQuery(this).css("background-color", "#99CCFF");
					   jQuery(this).children("label").css("color", "#000000");					   
				   }
			   }) ;
		   }	  
	   }
	   
	   arr_contains = function(a, obj) {
		   for (var i = 0; i < a.length; i++) {
		        if (a[i] === obj) {
		            return true;
		        }
		    }
		    return false;
	   }
	    //-----------connection setting---------------    
	
	   var edit_conn_setting = function(){
		   newconnection = false ; // We can know what it isn't new the connection .
	   }   
	   var action_after_load = function(){
		   jsPlumb.bind("jsPlumbConnection", function(e){
			   if(!wfeditable){
				   jsPlumb.detach(e);
				   return ;
			   }
			   if(!chk_connection(e)){
				   jsPlumb.detach(e);
				   return;
			   }
			   showConnectionDialog(e, connSet);
			   wfConn = e;
			   newconnection = true ; // We can know what it is new the connection .
		   });    	
	   }
	   
	   chk_connection = function(conn){
		   c = jsPlumb.getConnections();
		   var count = 0 ;
		   for( var i = 0; i < c.length ; i++ ){
			   
			   if(conn.sourceId == c[i].sourceId && conn.targetId == c[i].targetId){
				   count++;
			   }
		   }
		   if(count == 2)return false;
		   return true;
	   }
	   
	   jQuery( document ).on( "click", "#connection-setting-save", function(){
	    	var pathColor = jQuery("input[name=path-opt]:checked").val();
	    	jsPlumb.detach(wfConn);
	    	reconnect(pathColor);
	    	connSet = { "path": pathColor };
	    	
	    	edit_conn_setting() ; //We can know what it isn't new the connection after saving .
	    	set_step_chaned_status() ; // We can know what workflow graphic was changed.
	    	jQuery.modal.close(); 
	    });    
	    var reconnect = function(pColor){
	    	jsPlumb.connect({
				source:wfConn.sourceId,
				target:wfConn.targetId,
				paintStyle:{
					lineWidth:3,
					strokeStyle:pColor							
				},
				connector:"StateMachine"	// only one connection style.
			}); 
	    } 
	    
	    jQuery("#connEdit").click(function(){
	    	connSet = { "path": wfConn.paintStyleInUse.strokeStyle };
	    	jQuery("#connectionMenu").hide();
	    	showConnectionDialog(wfConn, connSet);    	
	    })
	    
	    jQuery("#connDelete").click(function(){
	    	jsPlumb.detach(wfConn);
	    	set_step_chaned_status() ; // We can know what workflow graphic was changed.
	    	jQuery("#connectionMenu").hide();
	    });
	    
	    jQuery( document ).on( "click", "#connection-setting-cancel, .modalCloseImg", function(){       	
	    	if(newconnection)
	    		jsPlumb.detach(wfConn);    	
	    	edit_conn_setting() ; //We can know what it isn't new the connection after cancel .
	    	jQuery.modal.close();
	    });
	     
	    
	    //-------------step setting-------------------
	    
	    setSlectedStep = function(obj){
	    	selectedStep = obj;
	    }
	    
	    jQuery("#stepDelete").click(function(){
	    	var db_id = jQuery(selectedStep).attr("db-id") ;
	    	if( db_id == "nodefine" ){
	    		jsPlumb.detachAllConnections(selectedStep);
	        	jQuery(selectedStep).remove();
	        	jQuery("#stepMenu").hide();
	    	}else{
	    		if(!confirm(drag_drop_jsplumb_vars.removeStep)){
	    			jQuery("#stepMenu").hide();
	    			return ;
	    		}
	    		jsPlumb.detachAllConnections(selectedStep);
	    		jQuery(selectedStep).remove();        	
	    		set_deleted_step(db_id);  //We save stepid after dedeting.
	    		jQuery("#stepMenu").hide();
	    		set_step_chaned_status() ; // We can know what workflow graphic was changed.
	    	}   	
	    });
	    
	    jQuery("#stepEdit a").click(function(){
	    	var g_step_id = jQuery(selectedStep).attr("id");
	    	var step_dbid = jQuery(selectedStep).attr("db-id");
	    	var process_name = jQuery(selectedStep).attr("process-name");
			step_edit_data = {
					action : 'load_step_info' ,
					process_name : process_name,
					step_gpid : g_step_id,
					step_dbid : step_dbid,
					editable : wfeditable
				   };							
			jQuery.post(ajaxurl, step_edit_data, function( response ) {
				jQuery("#step-info-update").html(response);
				jQuery("#step-info-update").modal({
				    containerCss: {
				        padding: 0,
				        width: 650
				    },					
				    onShow: function (dlg) {
				        jQuery(dlg.container).css('height', 'auto');
				        jQuery(dlg.wrap).css('overflow', 'auto'); // or try ;
				        jQuery.modal.update();
				    }					
				});
			   	var step_gpid = jQuery("#step_gpid-hi").val() ;
			   	var lbl = jQuery(document).find( "#" + step_gpid + " label" ).html() ;
			   	jQuery("#step-name").val(lbl) ;
			   	var process_name = jQuery(document).find( "#" + step_gpid ).attr("process-name") ;
			  	jQuery("#step-setting-content").css("height","420px");
			   	if(jQuery("#" + step_gpid).attr("first_step") == "yes") {
			   		jQuery("#first_step_check").attr("checked", true);
				}

			   	makeWhizzyWig("assignment-email-content","all");
			   	makeWhizzyWig("reminder-email-content","all");

			   	jQuery("#TB_window").css({"top":"53%"});
			   	jQuery("#TB_overlay").addClass("hidden");
			   	jQuery("#TB_load").css({"display":"none"});
			});	    	
	    	jQuery("#stepMenu").hide();
	    	return true;
	    });
	    
	    step_attached_data_del = function(stepId){
	    	return stepId ;
	    }
	    
	    //---------delete form-----------------
	    jQuery("#delete-form").click(function(){
	    	if(!confirm(drag_drop_jsplumb_vars.clearAllSteps))return ;
	    	jQuery(".fc_action .w").each(function(){	
	    		if(jQuery(this).attr("db-id") != "nodefine"){
	    			set_deleted_step(jQuery(this).attr("db-id")); //We save stepid after dedeting.
	    		}
	    		jsPlumb.detachAllConnections(this);
	    		jQuery(this).remove();
	    		set_step_chaned_status() ; // We can know what workflow graphic was changed.
	    	});
	    	
	    	return false;
	    });
	    //--------step data control ------------
	    
	    set_step_info_gp = function(gpId, savedata){
			var stepInfo = {} ;
			var stepInfoStr = jQuery("#wf_step_data_hi").val() ;		
			stepInfo = jQuery.parseJSON(stepInfoStr);
			if( savedata == "del" ){			
				stepInfo[gpId] = savedata ;
			}else{
				delete stepInfo[gpId] ;
			}
			jQuery("#wf_step_data_hi").val( jQuery.toJSON(stepInfo) );
		}
	      
	    //-----------------Menus----------------
	    if(wfeditable){
		    jsPlumb.bind("contextmenu", function(c, e) {
		    	wfConn = c;
		    	jQuery("#connectionMenu").show().css({"left": e.pageX + "px", "top": e.pageY + "px"});
		    	e.preventDefault();
		    	jQuery("#stepMenu").hide();
		    	return false;
		    }); 
	    }    
	    jQuery( document ).on( "contextmenu", ".w", function(e){
			jQuery("#stepMenu").show().css({"left": e.pageX + "px", "top": e.pageY + "px"});
			setSlectedStep(this);
			jQuery("#connectionMenu").hide();
			return false;
		});
	    //---------------------------------------
	    jsPlumb.bind("ready", function() {   
	    	// chrome fix.
	    	document.onselectstart = function () { return false; };    	
	    	workflowGraphic.init();
	    	if(wf_structure_data){
	    		_graphic_make(wf_structure_data);
	    		set_first_step(wf_structure_data) ;
	    	}    	
	    	action_after_load();
	    });
	});
}(jQueryCgmp));