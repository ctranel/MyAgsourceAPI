//Implementation: add class "dropdown" to containing UL
//menu functions located in as_app_helper.js
$(document).ready(function(){
	//get column index of NM$ column
	//add color to the nm$ field based on the qtile field value
	var nm_index = 0;
	var arr_qtile_class = new Array('', 'qtile1', 'qtile2', 'qtile3', 'qtile4');
	var table = document.getElementById('main-report'),
	rowLength = table.rows.length;
	for (var i = 1; i < rowLength; i += 1) {
	    var row = table.rows[i];
	    if(i == 1){
	    	colLength = 10;
	    	for (var j = 0; j < colLength; j += 1) {
				if(row.cells[j].id == 'net_merit_amt' || row.cells[j].id == 'est_net_merit_amt') nm_index = j;
			}
	    }
	    else if(i > 2) row.cells[nm_index].className = arr_qtile_class[row.cells[(nm_index + 1)].innerHTML];
	}
});
