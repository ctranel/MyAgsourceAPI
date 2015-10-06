head.ready(function() {
	$("#herd_code_fill").on("keyup blur change", function(event) {
	 	if (event.which == "9" || event.which == "13" || event.which == "16" || event.which == "18") {
			event.preventDefault();
			return;
		}

		var textValue = $(this).val();
		$("#herd_code").attr("selectedIndex", "0");
		$('#herd_code option[value^="' + $(this).val() + '"]').prop("selected",true);
		
		var sel_index = document.getElementById('herd_code').selectedIndex;
		var sel_val = document.getElementById('herd_code').options[sel_index].value;
		if(sel_val.indexOf(textValue) !== 0){
			document.getElementById('info-message').innerHTML = 'No herds match ' + $(this).val();
			document.getElementById('herd_code').options[sel_index].selected = false; 
		}
		else {
			document.getElementById('info-message').innerHTML = '';
		}
	});
	
	$("#select_herd").on("submit", function(ev) {
		var ret_val = true;
		var herd_code = $("#herd_code").val();
		var herd_code_fill = $("#herd_code_fill").val();
		if(herd_code == 0 || (herd_code_fill.length == 8 && herd_code_fill !== herd_code)){
			alert('Please select a herd from the dropdown to continue.');
			return false;
		}
		//@todo: use promises rather than async: false
		$.ajax({
			url: "ajax_herd_enrolled/" + herd_code,
			async: false,
			dataType: 'json',
			success: function(data) {
				if(data['new_test'] === true){ 
					if(data['enroll_status'] === 1){ //not on MyAgSource
						ret_val = confirm("Herd "  + herd_code + " is not enrolled on MyAgSource.  If you choose to continue, you will be billed for all access beyond base reports.  Do you want to continue?");
					}
					else if(data['enroll_status'] === 2){//on trial
						ret_val = confirm("Herd "  + herd_code + " is not being billed for MyAgSource.  If you choose to continue, you will be billed for all access beyond base reports.  Do you want to continue?");
					}
				}
			}
		});
		return ret_val;
	});
});