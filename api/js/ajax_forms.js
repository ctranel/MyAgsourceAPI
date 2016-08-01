	$('.ajax-form').submit(function(ev){
		ev.preventDefault();
		var data = assocFormToObject(ev.target);
//		data = encodeURIComponent(JSON.stringify(data));
		var response_obj;
		$.post(ev.target.action, data)
			.done(function(data){
				if(typeof(updatePage) === 'function'){
					updatePage(this);
					$('#info-message').text('Submission was successful.');
				}
				else{
					$('#info-message').text('Submission was successful.');
					//location.reload();
				}
			})
			.fail(function(data){
				response_obj = JSON.parse(data.responseText);
				$('#info-message').text('Submission failed: ' + response_obj.error.message);
			});
	});
	
	function assocFormToObject(objForm){
		var elements = objForm.elements;
		var obj_return = {};
		var el_len = elements.length;
		for(var i=0; i<el_len; i++){
			var e = elements[i];
			if(typeof(e.type) !== 'undefined'){
				if((e.type === 'text' || e.type === 'hidden' || e.type.indexOf('select') >= 0) && typeof(e.value !== 'undefined')){
					if(e.name.indexOf('[') >= 0){
						var name = e.name.substring(0, e.name.indexOf("['"));
						var idx = e.name.substring(e.name.indexOf("['") + 2, e.name.indexOf("']"));
						if(typeof(obj_return[name]) === 'undefined'){
							obj_return[name] = {};
						} 
						obj_return[name][idx] = e.value;
					}
					else {
						obj_return[e.name] = e.value;
					}
				}
			}
		}
		return obj_return;
	}

