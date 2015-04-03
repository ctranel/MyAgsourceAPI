	$(document).ready(function(){
		var x = 0;
		while($("#downloads-container" + x + " ul li").length){
			$("#downloads-container" + x + " ul li").bind("click", function(){
				window.location = $(this).find("a:first").attr('href');
			});
		
		    $("#downloads-container" + x + "").click(menu_open);
		    //$("#downloads-container" + x).mouseout(menu_timer);
		    $("#downloads-container" + x + " ul:first").mouseover(cancel_timer);
		    $("#downloads-container" + x + " ul:first").mouseout(menu_timer);
		    x++;
		}
	})
	



	//$("ul.dropdown li ul li").bind("click", function(){
	//	window.location = $(this).find("a:first").attr('href');
	//})

//	$("ul.dropdown li").mouseover(menu_open);
//    $("ul.dropdown ul:first").mouseover(cancel_timer);
//    $("ul.dropdown li ul:first").mouseout(menu_timer);
	//document.onclick = menu_close;
    
    $("ul.dropdown li:has(ul)").find("a:first").append(" ▼");
    $("ul.dropdown li ul li:has(ul)").find("a:first").append(" &raquo; ");
    
//    $('.shadow_left').height($('#container').height());
//    $('.shadow_right').height($('#container').height());
//    $('#container').css(maxWidth, $('#container').width());
//    $('#container').css(float, 'none');
//    $('#container').css(margin, '0 auto');


//Implementation: add class "dropdown" to containing UL
    var close_timer;
    var menu_item;

    function menu_open(){
//alert('here');
		cancel_timer();
    	menu_close();
    	menu_item = $(this).find('ul').css('visibility', 'visible');
    	menu_item = $(this).find('ul').css('display', 'block');
    }

    function menu_close(){
    	if(menu_item) menu_item.css('visibility', 'hidden');
    	if(menu_item) menu_item.css('display', 'none');
    }

    function menu_timer(){
    	close_timer = window.setTimeout(menu_close, 200);
    }

    function cancel_timer(){
    	if(close_timer){
    		window.clearTimeout(close_timer);
    		close_timer = null;
    	}
    }

    function submit_table_sort_link(form_id, submit_url, original_url){
    	document.getElementById(form_id).setAttribute('action', submit_url);
    	document.getElementById(form_id).submit();
    	if(original_url) document.getElementById(form_id).setAttribute('action', original_url);
    	return false;
    }

    if(!window.form_reset){
    	function form_reset(){
    		oForm = document.getElementById("filter-form");
    		var frm_elements = oForm.elements;
    		for (i = 0; i < frm_elements.length; i++) {  
    			if(typeof(frm_elements[i].type) == 'undefined'){
    				continue;
    			}
    			field_type = frm_elements[i].type.toLowerCase();  
    	
    		    switch (field_type) {  
    			    case "text":  
    			    	frm_elements[i].value = "";  
    			        break;  
    			    //case "radio":  
    			    case "checkbox":  
    			        if (frm_elements[i].checked)  
    			        {  
    			        	frm_elements[i].checked = false;  
    			        }  
    			        break;  
    			    default:  
    			        break;  
    		    }  
    		}
    		oForm.submit();
    	}
    }


//@todo TEMPORARY CODE FOR RECORDING CLICKS OFF TO AGSOURCE DM (until DM is integrated)
	//attach load event to body
/*	$("#dm-anchor").bind('click', function(event){
		event.preventDefault();
		$.get("/app/dm/log_event");
	    $("#agsourcedm").submit();
	});*/

// END TEMPORARY CODE FOR RECORDING CLICKS OFF TO AGSOURCE DM