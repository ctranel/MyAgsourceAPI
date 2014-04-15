if($('#exp_date').length > 0){
	$('#exp_date').datepick({
		dateFormat: 'mm/dd/yyyy'
	});
}

$('.section-checkbox').bind('click', function(event) {
	var $target = $(event.target);
	var $event_fieldset_id = $target.attr('value');
	if($target.attr('checked')) $('#' + $event_fieldset_id).show();
	else $('#' + $event_fieldset_id).hide();
});

head.ready(function() {
	//confirmation should not be required for denying access
//	$('#deny_access').on('click',function(event){
//		$(event.target.form).off('submit', checkRequired);
	});
})
