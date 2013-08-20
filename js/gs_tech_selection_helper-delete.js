var path = '';

$("#region_filter").bind("change", call_ajax);
var arr_path = window.location.pathname.split( '/' );
if (arr_path[2] == "index.php") path = arr_path[1] + '/' + arr_path[2];
else path = arr_path[1];
path += "/field_tech/ajax_options/";

function call_ajax(){
	ajax_url = window.location.protocol + "//" + window.location.host + "/" + path + $("#region_filter").val();
	$.ajax( {
		type : "POST",
		success : function(html) {
			$("#supervisor_num option").remove();
			$("#supervisor_num").append(html);
		},
		error : function(xhr) {
			alert("An error occured: " + xhr.status + " " + xhr.statusText);
		},
		url : ajax_url
	});
}