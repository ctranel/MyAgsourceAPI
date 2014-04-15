head.ready(function() {
	$("#herd_code_fill").on("keyup blur change", function(event) {
	 	if (event.which == "9" || event.which == "13" || event.which == "16" || event.which == "18") {
			event.preventDefault();
		}
		else {
			var textValue = $(this).val();
			$("#herd_code").attr("selectedIndex", "0");
			var matches = $('#herd_code option[value^="' + $(this).val() + '"]').prop("selected",true);
		}
	});
	
	$("#select_herd").on("submit", function() {
		var ret_val = true;
		var herd_code = $("#herd_code").val();
		$.ajax({
			url: "ajax_herd_enrolled/" + herd_code,
			async: false,
			dataType: 'json',
			success: function(data) {
				if(data['new_test'] === true){ 
					if(data['enroll_status'] === 1){ //not on MyAgSource
						ret_val = confirm("Herd "  + herd_code + " is not enrolled on MyAgSource.  If you choose to continue, you will be billed for this access.  Do you want to continue?");
					}
					else if(data['enroll_status'] === 2){//on trial
						ret_val = confirm("Herd "  + herd_code + " is not being billed for MyAgSource.  If you choose to continue, you will be billed for this access.  Do you want to continue?");
					}
				}
			}
		});
		return ret_val;
	});
});