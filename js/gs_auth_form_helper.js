if(document.getElementById('group_id')) AddEvent(document.getElementById('group_id'), 'change', function(){toggle_fields();});
toggle_fields();

function AddEvent(html_element, event_name, event_function) {
	if (html_element.attachEvent) // Internet Explorer
		html_element.attachEvent("on" + event_name, function() {
			event_function.call(html_element);
		});
	else if (html_element.addEventListener) // Firefox & company
		html_element.addEventListener(event_name, event_function, false);
}

function toggle_fields() {
	var obj_group = document.getElementById('group_id');
	var arr_group_id = $('#group_id').val();
	
	document.getElementById('region').style.display = "none";
	document.getElementById('tech').style.display = "none";
	document.getElementById('herd').style.display = "none";
	$('#region').removeClass('required');
	$('#tech').removeClass('required');
	$('#herd').removeClass('required');

	if ($.inArray('2', arr_group_id) >= 0) {
		document.getElementById('herd').style.display = "block";
		$('#herd').addClass('required');
	}
	if ($.inArray('3', arr_group_id) >= 0 || $.inArray('6', arr_group_id) >= 0) {
		document.getElementById('region').style.display = "block";
		$('#region').addClass('required');
	}
	if ($.inArray('5', arr_group_id) >= 0 || $.inArray('7', arr_group_id) >= 0 || $.inArray('8', arr_group_id) >= 0) {
		document.getElementById('region').style.display = "block";
		document.getElementById('tech').style.display = "block";
		$('#region').addClass('required');
		$('#tech').addClass('required');
	}
}