//$('#access_time_dbfrom').datetimeEntry({datetimeFormat: 'O-D-Y H:M', maxDatetime: '-1M', spinnerImage: ''});
//$('#access_time_dbto').datetimeEntry({datetimeFormat: 'O-D-Y H:M', maxDatetime: '+30M', spinnerImage: ''});

//if($('#access_time_dbfrom').val() == '') $('#access_time_dbfrom').datetimeEntry('setDatetime', '-7d');
//if($('#access_time_dbto').val() == '') $('#access_time_dbto').datetimeEntry('setDatetime', '+1M');

$('#access_time_dbfrom').datepick({
		dateFormat: 'mm-dd-yyyy'
});

$('#access_time_dbto').datepick({
	dateFormat: 'mm-dd-yyyy'
});

$('.section-checkbox').bind('click', function(event) {
	var $target = $(event.target);
	var $event_fieldset_id = $target.attr('value');
	if($target.attr('checked')) $('#' + $event_fieldset_id).show();
	else $('#' + $event_fieldset_id).hide();
});


function reset_events(event){
	var $target = $(event.target);
	var $event_fieldset_id = 'event-' + $target.attr('id');
	if($target.attr('checked')) $('#' + $event_fieldset_id).css('display', 'block');
	else $('#' + $event_fieldset_id).css('display', 'none');
	$('fieldset[id="' + $event_fieldset_id + '"]').attr('id');
}