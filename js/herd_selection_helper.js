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
				if(data === 1){ //not on MyAgSource
					ret_val = confirm("Herd "  + herd_code + " is not enrolled on MyAgSource.  If you choose to continue, you will be billed once for each test period in which you access a report for this herd while the herd is not billed for MyAgSource.  Do you want to continue?");
				}
				else if(data === 2){//on trial
					ret_val = confirm("Herd "  + herd_code + " is in an unpaid trial period on MyAgSource.  If you choose to continue, you will be billed once for each test period in which you access a report for this herd while the herd is not billed for MyAgSource.  Do you want to continue?");
				}
			}
		});
		return ret_val;
	});
});