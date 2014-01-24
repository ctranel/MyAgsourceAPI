head.ready(function() {
	$("#herd_code_fill").bind("keyup", function(event) {
	 	if (event.which == "9" || event.which == "13" || event.which == "16" || event.which == "18") {
			event.preventDefault();
		}
		else {
			$("body").css("cursor", "progress");
			var textValue = $(this).val();
			$("#herd_code").attr("selectedIndex", "0");
			var matches = $('#herd_code option[value^="' + $(this).val() + '"]').prop("selected",true);
			$("*").css("cursor", "auto");
		}
	});
}); 