function add_option_to_select(obj,dt,lbl,vl)
{
	var sel = jQuery("#" + obj).find('option');
	sel.remove();
	var appendStr = "";
	if(numKeys(dt)> 1 && !jQuery("#"+obj).attr("size")) {
		appendStr = "<option></option>";
	}
//	alert(numKeys(dt));
	/*
	If only user found then select it
	*/
	if(numKeys(dt)==1)
	{
		var selected_opt = " selected=selected ";		
	}
	
	
	if(typeof(dt)=="object" && numKeys(dt)>0 && lbl)
	{		
		for(var k in dt)
		{
			if(vl)
			{
				appendStr +="<option value='" + dt[k][vl] + "' " + selected_opt + " >" + dt[k][lbl] + "</option>";
			}
			else
			{
				appendStr +="<option value='" + k + "'>" + dt[k][lbl] + "</option>";
			}
		}
		jQuery('#'+obj).append(appendStr);
		
	}
	
	if(!lbl && !vl)
	{
		for(var k in dt)
		{		
			appendStr +="<option value='" + k + "'>" + dt[k] + "</option>";			
		}
		jQuery('#'+obj).append(appendStr);
	}
	
	if(numKeys(dt)==1)
	{
		jQuery("#assignee-set-point").click();		
	}	
	
}


function numKeys(obj)
{
    var count = 0;
    for(var prop in obj)
    {
        count++;
    }
    return count;
}

function chk_date_input(id1, id2)
{
	var s_d = jQuery("#" + id1).val();
	var e_d = jQuery("#" + id2).val();
	var arr_s_d = s_d.split("/") ;
	var arr_e_d = e_d.split("/") ;
	var startDate = new Date(arr_s_d[0], arr_s_d[1], arr_s_d[2]) ; 
	var endDate = new Date(arr_e_d[0], arr_e_d[1], arr_e_d[2]) ;
	if(startDate > endDate){
		return false ;
	}
	return true;
}

function chk_due_date(id1)
{
	var d_date = jQuery("#" + id1).val();
	if(!d_date){
		jQuery("#" + id1).css({"background-color":"#FBF3F3"});
		return;
	}
	var c_datetime = new Date();
	var c_d = c_datetime.getDate() ;
	var c_m = c_datetime.getMonth() + 1 ;
	var c_y = c_datetime.getFullYear() ;	
	var arr_date = d_date.split("/") ;	
	if( (c_y*10000 + c_m*100 + c_d*1) > (arr_date[2]*10000 + arr_date[0]*100 + arr_date[1]*1) ){
		alert(owf_workflow_util_vars.dueDateInPast);
		return false ;
	}
	return true;
}