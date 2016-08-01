head.ready(function() {
	$("#cow_fill").on("keyup blur change", function(event) {
		if (event.which == "9" || event.which == "13" || event.which == "16" || event.which == "18") {
			event.preventDefault();
			return;
		}
		var textValue = $(this).val().toLowerCase();

		$('#cow_ref option').filter(function () {
			return $(this).text().toLowerCase().indexOf(textValue) === 0; 
		}).prop("selected",true);
		
		var sel_index = document.getElementById('cow_ref').selectedIndex;
		var sel_val = document.getElementById('cow_ref').options[sel_index].innerHTML;

		if(sel_val.toLowerCase().indexOf(textValue) !== 0){
			document.getElementById('info-message').innerHTML = 'No cows match ' + $(this).val();
			document.getElementById('cow_ref').options[sel_index].selected = false; 
		}
		else {
			document.getElementById('info-message').innerHTML = '';
		}
	});
	
	$("#select_cow").on("submit", function(ev) {
		var cow_ref = $("#cow_ref").val();
		if(cow_ref == 0){
			alert('Please select a cow from the dropdown to continue.');
			return false;
		}
	});
});