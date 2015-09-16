head.ready(function() {
	$("#cow_fill").on("keyup blur change", function(event) {
	 	if (event.which == "9" || event.which == "13" || event.which == "16" || event.which == "18") {
			event.preventDefault();
			return;
		}
		var textValue = $(this).val();
		$("#cow_ref").attr("selectedIndex", "0");
		$('#cow_ref option').filter(function () {
			return $(this).html().indexOf(textValue) === 0; 
		}).prop("selected",true);
	});
	
	$("#select_cow").on("submit", function(ev) {
		var cow_ref = $("#cow_ref").val();
		if(cow_ref == 0){
			alert('Please select a cow from the dropdown to continue.');
			return false;
		}
	});
});