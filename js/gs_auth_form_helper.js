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
	
	//hide all optional sections
	if(document.getElementById('association')) document.getElementById('association').style.display = "none";
	if(document.getElementById('region')) document.getElementById('region').style.display = "none";
	if(document.getElementById('tech')) document.getElementById('tech').style.display = "none";
	if(document.getElementById('herd')) document.getElementById('herd').style.display = "none";
	$('#region').removeClass('required');
	$('#tech').removeClass('required');
	$('#herd').removeClass('required');
	
	//add optional sections back in as appropriate
	if (($.inArray('2', arr_group_id) >= 0 || $.inArray('13', arr_group_id) >= 0) && document.getElementById('herd') != null) {
		document.getElementById('herd').style.display = "block";
		$('#herd').addClass('required');
	}
	if (($.inArray('3', arr_group_id) >= 0 || $.inArray('10', arr_group_id) >= 0) && document.getElementById('association') != null && document.getElementById('region') != null) {
		document.getElementById('association').style.display = "block";
		document.getElementById('region').style.display = "block";
		$('#region').addClass('required');
	}
	if (($.inArray('5', arr_group_id) >= 0 || $.inArray('12', arr_group_id) >= 0 || $.inArray('8', arr_group_id) >= 0) && document.getElementById('region') != null && document.getElementById('tech') != null && document.getElementById('association') != null) {
		document.getElementById('association').style.display = "block";
		document.getElementById('region').style.display = "block";
		document.getElementById('tech').style.display = "block";
		
		$('#region').find('select').change(populate_techs)
		$('#region').addClass('required');
		$('#tech').addClass('required');
	}
	//if the logged in user does not have permission to edit associations, but does have permission to edit techs:
	else if(($.inArray('5', arr_group_id) >= 0 || $.inArray('12', arr_group_id) >= 0 || $.inArray('8', arr_group_id) >= 0) && document.getElementById('tech') != null && document.getElementById('association') != null) {
		document.getElementById('association').style.display = "block";
		document.getElementById('tech').style.display = "block";
		$('#tech').addClass('required');
	}
}

	if($('#region').css('display') == 'block' && $('#tech').css('display') == 'block'){
		$('#region').find('select').change(populate_techs)
	}

function populate_techs(ev){
	$.getJSON("ajax_techs/" + $(this).val(), null, function(data) {
		$('#tech').find('option').remove();
		$.each(data, function(index, item){
	console.log(index + ' - ' + item);
			$('#tech').find('select').append(
				$("<option></option>")
					.text(item)
					.val(index)
			)
		})
	});
}
