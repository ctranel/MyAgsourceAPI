var el_drag_start;
var old_container;
var new_container;

//GENERAL DRAG AND DROP FUNCTIONS
function allow_drop(ev){ //called by event ondragover
	ev.preventDefault();
}

function drag(ev){ //called by event ondragstart
	//ev.originalEvent.dataTransfer.setData("Text",ev.target.id);
	var arr_id = this.id.split('-');
	old_container = arr_id[0];
	el_drag_start = this;
	
}

function drop_sample(ev){ //called by event ondrop
	ev.preventDefault();
	var arr_id = ev.target.id.split('-');
	var new_container = arr_id[0];
	var this_el=el_drag_start.cloneNode(true);//document.getElementById(ev.originalEvent.dataTransfer.getData("Text")).cloneNode(true);
	this_el.id = this_el.id.replace(old_container, new_container);
	var tmp = this_el.outerHTML;
	$(this_el).html(tmp.replace("'" + old_container + "', '" + new_container + "'", "'" + new_container + "', '" + new_container + "'"));
	ev.target.innerHTML = '';
	ev.target.appendChild(this_el);
	el_drag_start = null;old_container=null;new_container=null;
}

function trash(ev){
	ev.preventDefault();
//	var data=ev.originalEvent.dataTransfer.getData("Text");
//	var el = document.getElementById(data);
	el_drag_start.parentNode.innerHTML = "";
	el_drag_start = null;
}

//specific functions
//drop for where, sort by and group by
function drop_input_field(ev){ //called by event ondrop
	ev.preventDefault();
	//set target to containing div and input to input (script was allowing target to be dropped into input in addition to containing div)
	if(ev.target.tagName == "INPUT"){
		var input = $(ev.target);
		var target = $(ev.target).parent();
	}
	else if(ev.target.tagName == "DIV"){
		var input = $(ev.target).children().first();
		var target = $(ev.target);
	}
	else return false;
	drop(input, target);
	el_drag_start = null;old_container=null;new_container=null;
}

function drop(input, target){
	var tmp_arr_id = $(input).attr('id').split('-');
	var new_container = tmp_arr_id[0];
	if(old_container != 'list') return false;
	//create new, blank input element
	var new_field = $(target).clone(true);
	$(target).after(new_field);
	
	//set values according to field being dragged
	tmp_arr_id = el_drag_start.id.split('-');
	var new_id = tmp_arr_id[1];

	$(input).attr('id', el_drag_start.id.replace(old_container, new_container));
	$(input).attr('title', tmp_arr_id[1]);
	$(input).attr('value', el_drag_start.innerHTML);
	$(target).find('input, select').each(function() {
		var arr_name = $(this).attr('name').split('[');
		var base_name = arr_name[0];
		$(this).attr('name', base_name + '[' + new_id + ']');
	});
	$(target).attr('id', 'w_' + $(input).attr('id'));
	$(target).attr('draggable', true);
	$(target).on('dragstart', drag);
	if(new_container.indexOf('where') >= 0 && $(target).html().indexOf('and_or') < 0){
		$(target).append('<select name="and_or[' + new_id + ']"><option value="and">and</option><option value="or">or</option></select>');
	}
	else if((new_container.indexOf('sort') >= 0 || new_container.indexOf('group') >= 0) && $(target).html().indexOf('then by') < 0){
		$(target).append('<p>then by...</p>');
	}
}

// DRAG AND DROP
function drop_table_build_field(ev){ //called by event ondrop
	ev.preventDefault();
	drop_table_field(ev, 'wcolumn');
}

function drop_chart_build_field(ev){ //called by event ondrop
	ev.preventDefault();
	var container_id = $(ev.target).parent("TH").attr('id').split('-')[0];
	drop_table_field(ev, container_id);
}

function drop_table_field(ev, container_id){
	if(ev.target.tagName == "INPUT"){
		var input = $(ev.target);
		var target = $(ev.target).parent("TH");
	}
	else if(ev.target.tagName == "DIV"){
		var target = $(ev.target).parent("TH");
		var input = $(ev.target).parent("TH").find('input').first();
	}
	else return false;

	var tmp_arr_id = $(target).attr('id').split('-');
	var new_container = tmp_arr_id[0];
	var field_id = tmp_arr_id[1];
	if(old_container != 'list' && old_container != container_id) return false;

	//get column index of drop target element
	var drop_index = $(target).parent().children().index($(target));
	var new_value = el_drag_start.innerHTML;
	
	//get drag column index if it exists
	var drag_index;
	if(old_container == new_container){
		var tmp_clone = $("#" + el_drag_start.id);//document.getElementById(ev.originalEvent.dataTransfer.getData("Text"))//.cloneNode(true);
		drag_index = $(tmp_clone).parent().children().index($(tmp_clone));
		new_value = $("#" + el_drag_start.id).find('input').first().attr('value');
		
	}
	
	//create element to be inserted
	var new_th = $(target).clone(true);

	//set values according to field being dragged
	tmp_arr_id = el_drag_start.id.split('-');
	var new_id = tmp_arr_id[1];
	$(new_th).attr('id', el_drag_start.id.replace(old_container, new_container));
	$(new_th).attr('title', tmp_arr_id[1]);
	$(new_th).find('input').first().attr('value', new_value);
	$(new_th).find('input, select').each(function() {
		var arr_name = $(this).attr('name').split('[');
		var base_name = arr_name[0];
		$(this).attr('name', base_name + '[' + new_id + ']');
	});
	$(new_th).find('input').each(function() {
		if($(this).attr('id') !== undefined){
			var arr_id = $(this).attr('id').split('-');
			var base_id = arr_id[0];
			$(this).attr('id', base_id + '-' + new_id);
		}
	});
	
	//attach events
	$(new_th).attr('draggable',true);
	$(new_th).on('dragstart', drag);

	//insert column
	$('#' + container_id + '-' + field_id).parents('table').find('tr').each(function(){
    	if(typeof(drag_index) != 'undefined' && drag_index >= 0){
    		$(this).remove_column(drag_index);
    		//$(this).find('th').eq(drag_index).remove();
    	}
        if($(this).attr('class') == 'fields-in'){
        	$(this).find('th').eq(drop_index).before(new_th);
       }
        else{
        	var colspan_index = $(this).get_colspan_index(drop_index);
        	var old_colspan = $(this).find('th').eq(colspan_index).attr('colspan') ? $(this).find('th').eq(colspan_index).attr('colspan') : 1;
        	$(this).find('th').eq(colspan_index).attr('colspan', (parseInt(old_colspan) + 1));
        }
    });

	//add field to pivot field list
	$("#pivot_db_field").append('<option value="' + new_id + '">' + new_value + '</option>');

	el_drag_start = null;old_container=null;new_container=null;
}

function trash_table_build_column(ev){
	ev.preventDefault();
	trash_column(ev, 'wcolumn');
}

function trash_chart_build_column(ev){
	ev.preventDefault();
	var container_id = $(ev.target).parent("TH").attr('id').split('-')[0];
	trash_column(ev, container_id);
}

function trash_column(ev, container_id){
	field_id = $(ev.target).parents('th').attr('id').split('-')[1];
//alert(field_id);
	/**** remove field from rotate options list  *********/
	$("#pivot_db_field option[value='" + field_id + "']").remove();
	if(field_id == 0) return false;
	var col_index;
	var tmp_clone = $('#' + container_id + '-' + field_id);
	col_index = $(tmp_clone).parent().children().index($(tmp_clone));
	//Remove column in each row of table
	$('#' + container_id + '-' + field_id).parents('table').find('tr').each(function(){
		if(typeof(col_index) != 'undefined' && col_index >= 0){
			$(this).remove_column(col_index);//find('th').eq(col_index).remove();
    	}
		reset_row_index(this);
    });
}

function trash_head(ev){
	ev.preventDefault();
	if($(ev.target).parent('th').parent('tr').children().length <= 1) return false;
	var remove_colspan = $(ev.target).parent('th').attr('colspan');
	var next_sib_colspan = $(ev.target).parent('th').next('th').attr('colspan');
	var new_colspan = parseInt(remove_colspan) + parseInt(next_sib_colspan);
	$(ev.target).parent('th').next('th').attr('colspan', new_colspan);
	$(ev.target).parent('th').remove();
	
	reset_row_index($(ev.target).parent('tr'));
}

function trash_field(ev){
	ev.preventDefault();
	field_id = $(ev.target).parent('div').attr('id').split('-')[1];
	if(field_id != 0) $(ev.target).parent('div').remove();
}

function add_head_row(){
	var new_row = $('#table-build').find('tr[id^=hgrow]').last().clone(true);
	var row_id = parseInt($(new_row).attr('id').split('-')[1]) + 1;
	var row_base = $(new_row).attr('id').split('-')[0];
	$(new_row).attr('id', row_base + '-' + row_id);
	var cnt = 0;
	
	$(new_row).find('th').each(function(){
		$(this).find('input').each(function(){
			var index = $(this).attr('id').split('-')[2];
			$(this).attr('id', $(this).attr('id').replace('-'+(row_id -1)+'-'+index, '-'+row_id+'-'+cnt));
			$(this).attr('name', $(this).attr('name').replace('['+(row_id -1)+']'+'['+index+']', '['+row_id+']'+'['+cnt+']'));
		});
		cnt++;
	});
	$('#table-build').find('tr[id^=hgrow]').last().after(new_row);
}

function split_head(){
	var old_colspan = $(this).parent('th').attr('colspan');
	
	var colspan1 = prompt('How many columns on left side of split?');
	while(parseInt(colspan1) + 1 > old_colspan){
		colspan1 = prompt('Please enter a number that is less than the number of columns currently spanned.');
	}
	if((colspan1) == 0) return false;
	var colspan2 = old_colspan - colspan1;
	var head_clone = $(this).parent('th').clone(true);
	$(this).parent('th').attr('colspan', colspan1);
	$(head_clone).attr('colspan', colspan2);
	$(this).parent('th').after(head_clone);
	reset_row_index($(this).parent('th').parent('tr'));
}

function reset_row_index(objTr){ //for use with header group rows, relies on having 2 hyphens
	var cnt = 1;
	if(typeof($(objTr).attr('id')) == 'undefined') return false;
	var row_id = $(objTr).attr('id').split('-')[1];
	$(objTr).find('th').each(function(){
		$(this).find('input').each(function(){
			var index = $(this).attr('id').split('-')[2];
			$(this).attr('id', $(this).attr('id').replace('-'+row_id+'-'+index, '-'+row_id+'-'+cnt));
			$(this).attr('name', $(this).attr('name').replace('['+row_id+']'+'['+index+']', '['+row_id+']'+'['+cnt+']'));
		});
		cnt++;
	});
}

function add_aggregate(ev){
	var agg_value = $(ev.target).val();
	var agg_th = $(ev.target).parents('th');
	var field_id = agg_th.attr('id').split('-')[1];
	var base_name;
	$(agg_th).find('input, select').each(function() {
		var base_name = $(this).attr('name').split('[')[0];
		if($('#' + agg_value + '_' + base_name + '-' + field_id).length > 0){
			alert("This field-aggregate combination is already being used, each field-aggregate combination can be used only once per report.");
			$(ev.target).val('');
			return false;
		}
		$(this).attr('name', base_name + '[' + agg_value + '_' + field_id + ']');
		$(this).attr('id', agg_value + '_' + base_name + '-' + field_id);
	});
}

function add_where_grouping(ev){
	var new_grouping = $("#set-where").children('div').last().clone(true, true);
	var grouping_index = $(new_grouping).attr('id').match(/_(.*)/i)[1];
	var new_index = (parseInt(grouping_index) + 1);
	var new_id = $(new_grouping).attr('id').replace('_' + grouping_index, '_' + new_index);
	$(new_grouping).attr('id', new_id);
	$("#set-where").children('div').last().after(new_grouping);
	$("#set-where").children('div').find('div, input').each(function(){if(typeof($(this).attr('id')) != 'undefined') $(this).attr('id', $(this).attr('id').replace('_' + grouping_index, '_' + new_index));});
	$("#set-where").children('div').last().before('<select name="and_or[' + grouping_index + ']"><option value="and">and</option><option value="or">or</option></select>');
}

//end specific to table#table_build


//DOM MANIPULATION FUNCTIONS
//fills the section page select list based on the value of the report super section form field
function fill_section_select(ev){
	var options_html = '';
	var super_section_id = $(ev.target).val();
	var cache_bust = Math.floor(Math.random()*1000);
	var ajax_url = base_url + '/custom_report/select_section_data/' + super_section_id + '/' + cache_bust;
	$.getJSON(ajax_url, function(result){
		$.each(result, function(id, field){
			options_html += '<option value="' + id + '">' + field + '</option>';
		});
		$("#section_id").html(options_html);
	});
}

//fills the report page select list based on the value of the report section form field
function fill_page_select(ev){
	var options_html = '';
	var section_id = $(ev.target).val();
	var cache_bust = Math.floor(Math.random()*1000);
	var ajax_url = base_url + '/custom_report/select_page_data/' + section_id + '/' + cache_bust;
	$.getJSON(ajax_url, function(result){
		$.each(result, function(id, field){
			options_html += '<option value="' + id + '">' + field + '</option>';
		});
		$("#page_id").html(options_html);
	});
}

//fills the table select list based on the value of the report_page form field
function fill_insert_after(ev){
	var options_html = '<option value="1" selected="selected">First position</option>';
	var page_id = $(ev.target).val();
	var cache_bust = Math.floor(Math.random()*1000);
	var ajax_url = base_url + '/custom_report/insert_after_data/' + page_id + '/' + cache_bust;
	$.getJSON(ajax_url, function(result){
		$.each(result, function(list_order, name){
			options_html += '<option value="' + list_order + '">' + name + '</option>';
		});
		$("#insert_after").html(options_html);
	});
}

//fills the table select list based on the value of the cows_or_summary form field
function fill_table_select(ev){
	var options_html = '<option value="">Select one</option>';
	var cow_or_summary = getRadioValue(ev.target);
	var cache_bust = Math.floor(Math.random()*1000);
	var ajax_url = base_url + '/custom_report/select_table_data/' + cow_or_summary + '/' + cache_bust;
	$.getJSON(ajax_url, function(result){

		$.each(result, function(id, field){
			options_html += '<option value="' + id + '">' + field + '</option>';
		});
		$("#choose_table_id").html(options_html);
		$("#choose_table_id").css('display', 'block');
	});
}

//fills the select field list based on the value of the choose_table_id form field
function fill_fields(ev){
	var options_html = ''; //this var stores data for building list of all field to be dragged from
	var dt_options_html = '<option value="">Select Field</option>'; //this var stores data for the timespan fields
	var table_id = $(ev.target).val();
	if(table_id == null) return false;
	var cache_bust = Math.floor(Math.random()*1000);
	var ajax_url = base_url + '/custom_report/select_field_data/' + table_id + '/' + cache_bust;
	
	$.getJSON(ajax_url, function(result){
		$.each(result, function(id, fields){
			options_html += '<div id="list-' + fields[0] + '" class="draggable" title="' + fields[1] + '" draggable="true">' + fields[2] + '</div>';
			if(fields[3] > 0) dt_options_html += '<option value="' + fields[0] + '">' + fields[2] + '</option>';
		});
		$("#field-container").html(options_html);
		$("#field-container").children().each(function(){$(this).on('dragstart', drag);});// $(this).attr('draggable', true);
		$("#xaxis_field").html(dt_options_html);
	});
}

function toggle_table_chart(ev){
	var checked_val = $('.display-options:checked').val();
	if(checked_val == 1 || checked_val == 3){ //tables
		$('.chart-only').each(function(){$(this).css('display', 'none');});
		$('.table-only').each(function(){$(this).css('display', 'block');});
	}
	else{ //chart
		$('.table-only').each(function(){$(this).css('display', 'none');});
		$('.chart-only').each(function(){$(this).css('display', 'block');});
	}
}

function submit_form(ev){
	var cnt_row = 1;
	var cnt_non_colspan = 1;
	var cnt_colspan = 1;
	var colspan;
	var arr_row_col_parent = new Array();
	$('tr[id^=hgrow]').each(function(){
		cnt_non_colspan = 1;
		cnt_colspan = 1;
		var row_id = $(this).attr('id').split('-')[1];
		arr_row_col_parent[row_id] = new Array();
		$(this).find('th').each(function(){
			colspan = $(this).attr('colspan');
	        colspan = colspan ? parseInt(colspan) : 1;
	        for(x=0; x<colspan; x++){
	        	arr_row_col_parent[row_id][cnt_non_colspan] = cnt_colspan;
	        	cnt_non_colspan++;
	        }
			cnt_colspan++;
			$(this).find('input[name^=head_group_parent_index]').each(function(){
				if(typeof(arr_row_col_parent[(cnt_row - 1)]) !== 'undefined'){
					$(this).attr('value', arr_row_col_parent[(cnt_row - 1)][(cnt_non_colspan - 1)]);
				}
			});
		});
		cnt_row++;
	});
	cnt_non_colspan = 1;
	$('input[name^=col_head_group_index]').each(function(){
		$(this).attr('value', arr_row_col_parent[(cnt_row - 1)][cnt_non_colspan]);
		cnt_non_colspan++;
	});

	
//	console.log(arr_row_col_parent);
//	ev.preventDefault();
}

//EVENT HANDLERS
//block-specific
$("#rep-build").on("submit", submit_form);
$("#super_section_id").on("change", fill_section_select);
$("#section_id").on("change", fill_page_select);
$("#page_id").on("change", fill_insert_after);
$(".display-options").on("change", toggle_table_chart);
$(".cow_or_summary").on("click", fill_table_select);
$("#choose_table_id").on("change", fill_fields);

//field-specific
$("#add-where-grouping").on("click", add_where_grouping);
$(".add-header-row").on("click", add_head_row); //table design header rows
$(".split").on("click", split_head); //table design header rows
$(".remove-head").on("click", trash_head); //table design header rows
$(".remove-col").on("click", trash_table_build_column);
$(".remove-trend-col").on("click", trash_chart_build_column);
$(".remove-fld").on("click", trash_field);
$(".column-aggregate").on("change", add_aggregate);

$(".link").on("mouseover", function(){this.style.cursor = 'pointer';});
$(".link").on("mouseout", function(){this.style.cursor = 'auto';});

//drag and drop
//field list
$('#field-container').children().each(function(){$(this).on('dragstart', drag);});// $(this).attr('draggable', true);

//SELECT FIELD AREA
	//select table fields
	$('#wcolumn-0').on('drop', drop_table_build_field);
	$('#wcolumn-0').on('dragover', allow_drop);
	$('#wcolumn-0').addClass('field-target');
	
	//select trend fields
	$('#wtrendfield-0').on('drop', drop_chart_build_field);
	$('#wtrendfield-0').on('dragover', allow_drop);
	$('#wtrendfield-0').addClass('field-target');
	
	//select compare fields
	$('#wcomparefield-0').on('drop', drop_chart_build_field);
	$('#wcomparefield-0').on('dragover', allow_drop);
	$('#wcomparefield-0').addClass('field-target');

	//select boxplot fields
	$('#wboxplotfield-0').on('drop', drop_chart_build_field);
	$('#wboxplotfield-0').on('dragover', allow_drop);
	$('#wboxplotfield-0').addClass('field-target');
// END SELECT FIELD AREA
	
//group by
$('#wgroupby-0').on('drop', drop_input_field);
$('#wgroupby-0').on('dragover', allow_drop);
$('#wgroupby-0').on('dragstart', drag);
$('#wgroupby-0').children('input').first().on('focus', drag_only_alert);
$('#wgroupby-0').children('input').first().addClass('field-target');

//sort by
$('#wsortby-0').on('drop', drop_input_field);
$('#wsortby-0').on('dragover', allow_drop);
$('#wsortby-0').on('dragstart', drag);
$('#wsortby-0').children('input').first().on('focus', drag_only_alert);
$('#wsortby-0').children('input').first().addClass('field-target');

//where
$('#wwhere_0-0').on('drop', drop_input_field);
$('#wwhere_0-0').on('dragover', allow_drop);
$('#wwhere_0-0').on('dragstart', drag);
$('#wwhere_0-0').children('input').first().on('focus', drag_only_alert);
$('#wwhere_0-0').children('input').first().addClass('field-target');

/* $('#rep-build').on('submit', function() {
	var obj_data = $('#rep-build').serializeObject();
	var str_json = JSON.stringify(obj_data);
document.write(str_json);
	//$('#result').text(JSON.stringify($('form').serializeObject()));
    return false;
}); */

//SUPPORTING
function drag_only_alert(ev){
	alert('Information cannot be typed into this field.  Please drag and drop fields from the left panel, or click the "X" to remove this field.');
	this.blur();
}

$.fn.remove_column = function(col_index) {
    if(! $(this).is('tr') || col_index < 0) return -1;

    var allCells = this.children();
    var nonColSpanIndex = 0;
    allCells.each(
        function(i, item)
        {
            var colspan = $(this).attr('colspan');
            colspan = colspan ? parseInt(colspan) : 1;
            if(colspan > 1 && nonColSpanIndex <= col_index && (nonColSpanIndex + colspan) >= col_index){
            	$(this).attr('colspan', (colspan - 1));
            }
            else if(colspan == 1 && nonColSpanIndex == col_index){
            	$(this).remove();
            }
            nonColSpanIndex += colspan;
        }
    );
};

$.fn.get_colspan_index = function(non_colspan_index) {
	if(! $(this).is('tr') || non_colspan_index < 0) return -1;

    var allCells = this.children();
    var colSpanIndex = 0;
    var return_val;

    allCells.each(
        function(i, item)
        {
            var colspan = $(this).attr('colspan');
            colspan = colspan ? parseInt(colspan) : 1;
            if(colSpanIndex <= non_colspan_index && (colSpanIndex + colspan) >= non_colspan_index){
            	return_val = colSpanIndex;
            }
            colSpanIndex += colspan;
       }
    );
    return return_val;
};

$.fn.get_non_colspan_index = function() {
    if(! $(this).is('td') && ! $(this).is('th'))
        return -1;

    var allCells = this.parent('tr').children();
    var normalIndex = allCells.index(this);
    var nonColSpanIndex = 0;

    allCells.each(
        function(i, item)
        {
            if(i == normalIndex)
                return false;

            var colspan = $(this).attr('colspan');
            colspan = colspan ? parseInt(colspan) : 1;
            nonColSpanIndex += colspan;
        }
    );

    return nonColSpanIndex;
};

/*
$.fn.serializeObject = function(){
    var o = {};
    var name_index;
    var root_name;
	var a = this.serializeArray();
    $.each(a, function() {
 		if(this.name.indexOf('[') > 0){
 			name_index = this.name.match(/\[(.*)\]/i)[1];
        	root_name = this.name.split('[');
        	root_name = root_name[0];
 		}
 		else{
 			name_index = null;
 			root_name = this.name;
 		}
 		if (name_index == null) {
	       if(o[root_name] !== undefined){
	    	   if (!o[root_name].push) {
	               o[root_name] = [o[root_name]];
	           }
	           o[this.name].push(this.value || '');
	       }
	       else{
	            o[root_name] = this.value || '';
	       }
	   }
       else if (name_index > 0) {
			if (o[root_name] === undefined) {
				o[root_name] = {};
			}
			if (o[root_name][name_index] !== undefined) {
				if (!o[root_name][name_index].push) {
					o[root_name][name_index] = [ o[root_name][name_index] ];
				}
				o[root_name][name_index].push(this.value || '');
			}
			else {
				o[root_name][name_index] = this.value || '';
			}
		}
    });
    return o;
}; */