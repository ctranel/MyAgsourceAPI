var arr_sort_by = new Array();
var arr_sort_order = new Array();
var inline_form_width = 0;

// set all inline forms to be the same width
$('.handle').each(function(){
	//get the width of the section that immediately follows the .handle element
	var new_el = $(this).next();
	var width = new_el.width() + parseInt(new_el.css("border-left-width")) + parseInt(new_el.css("border-right-width"));
	if(width > inline_form_width){
		inline_form_width = width;
	}
});
$('.handle').each(function(){
	$(this).width(inline_form_width);
	$(this).next().width(inline_form_width);
});
// end set all inline forms to be the same width

if($('#filter-form')){ //if there is a filter form (only on pages with one table)
	$('#filter-form').submit(function(ev){
		ev.preventDefault();
		updatePage(this);
	});
}


function assocFormToObject(objForm){
	var elements = objForm.elements;
	var obj_return = {};
	var el_len = elements.length
	
	for(var i=0; i<el_len; i++){
		var e = elements[i];
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
	return obj_return;
	
/*	
	$(objForm).each(function(){
		console.log(dump($(this).attr('name')));
	});
*/
}

if($('#benchmark-form')){ //if there is a filter form (only on pages with one table)
	$('#default').click(function(ev){
		$('#make_default').val('1');
	});
	
	$('#set').click(function(ev){
		$('#make_default').val('0');
	});
	
	$('#benchmark-form').submit(function(ev){
		ev.preventDefault();
		params = assocFormToObject(document.getElementById('benchmark-form'));
		params = encodeURIComponent(JSON.stringify(params));
//		params = encodeURIComponent(JSON.stringify($(this).serializeObject()));
console.log(dump(params));
//console.log(dump($.extend({}, params)));
		$.post(site_url + 'benchmark/ajax_set/' + params)
			.done(function(){
				updatePage(this);
			})
			.fail(function(){});

		
	});
	
	$('#breed').change(function(){
		if($('#breed').val() === 'HO'){
			$('.HO').show();
			$('.HO_JE').show();
		}
		else if($('#breed').val() === 'JE'){
			$('.HO').hide();
			$('.HO_JE').show();
		}
		else{
			$('.HO').hide();
			$('.HO_JE').hide();
		}
	});
	
	$('#breed').trigger("change");
}

$('.handle').click(function(ev){
	ev.preventDefault();
	if($(this).hasClass('expanded')){
		$(this).removeClass('expanded');
		$(this).next().removeClass('expanded');
	}
	else{
		closeExpanded();
		$(this).addClass('expanded');
		$(this).next().addClass('expanded');
	}
});

$('.expand').on('mouseleave', function(ev){
	if(ev.target.tagName.toLowerCase() == "div") {
		$(this).removeClass('expanded');
		$(this).prev().removeClass('expanded');
	}
});

function closeExpanded(){
	$('.expanded').removeClass('expanded');
	$('.download-links').click(function(ev){
		params = encodeURIComponent(JSON.stringify($("#filter-form").serializeObject()));
		ev.target.setAttribute('href', ev.target.getAttribute('href') + '/' + params);
	});
}

function attachDataFieldEvents(){
	//datacell overlay
	$('.ajax-popup').on('click', function(e){e.preventDefault();});
	/*	$('.ajax-popup').qtip({
	    position: {
	    	my: 'center center',
	    	at: 'center center',
	    	target: $(window),
	    	adjust:{
	    		screen: true
	    	}
	    },
	   style: {
		   tip: {
			   corner: false
		   },
		   classes: 'qtip-overlay'
	   },
	   title: {
		   text: 'title',
		   button: true
	   },
	   content: {
	        text: function(event, api) {
	        	$.ajax({
	                url: api.elements.target.attr('href')//event.target.href
	            })
	            .then(function(content) {
	                // Set the tooltip content upon successful retrieval
	                api.set('content.text', content);
	            }, function(xhr, status, error) {
	                // Upon failure... set the tooltip content to the status and error value
	                api.set('content.text', status + ': ' + error);
	            });
	
	            return 'Loading...'; // Set some initial text
	        },
	        title: 'title'
	    },
	    show: {
	    	solo: true,
	    	modal: {
	    		on: true,
	    		blur: false
	    	},
	    	event: 'click'
	    },
	    hide: false
	});
	//$('.qtip').attr('style','');
*/	$('.ajax-popup').magnificPopup({
		type:'ajax'
	});
	
	//header tooltips
	$('.qtip-ajax').on('click', function(e){e.preventDefault();});
	$('.qtip-ajax').qtip({
	    position: {
	    	my: 'bottom left',
	    	at: 'top right',
	    	viewport: $(window)
	    },
	   content: {
	        text: function(event, api) {
	        	$.ajax({
	                url: api.elements.target.attr('href')//event.target.href
	            })
	            .then(function(content) {
	                // Set the tooltip content upon successful retrieval
	                api.set('content.text', content);
	            }, function(xhr, status, error) {
	                // Upon failure... set the tooltip content to the status and error value
	                api.set('content.text', status + ': ' + error);
	            });
	
	            return 'Loading...'; // Set some initial text
	        }
	    },
	    show: {
	    	solo: true,
	    },
	});
}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

(function($) {
	  return $.fn.serializeObject = function() {
	    var json, patterns, push_counters,
	      _this = this;
	    json = {};
	    push_counters = {};
	    patterns = {
	      validate: /^[a-zA-Z][a-zA-Z0-9_]*(?:\[(?:\d*|[a-zA-Z0-9_]+)\])*$/,
	      key: /[a-zA-Z0-9_]+|(?=\[\])/g,
	      push: /^$/,
	      fixed: /^\d+$/,
	      named: /^[a-zA-Z0-9_]+$/
	    };
	    this.build = function(base, key, value) {
	      base[key] = value;
	      return base;
	    };
	    this.push_counter = function(key) {
	      if (push_counters[key] === void 0) {
	        push_counters[key] = 0;
	      }
	      return push_counters[key]++;
	    };
	    $.each($(this).serializeArray(), function(i, elem) {
	      var k, keys, merge, re, reverse_key;
	      if (!patterns.validate.test(elem.name)) {
	        return;
	      }
	      keys = elem.name.match(patterns.key);
	      merge = elem.value;
	      reverse_key = elem.name;
	      while ((k = keys.pop()) !== void 0) {
	        if (patterns.push.test(k)) {
	          re = new RegExp("\\[" + k + "\\]$");
	          reverse_key = reverse_key.replace(re, '');
	          merge = _this.build([], _this.push_counter(reverse_key), merge);
	        } else if (patterns.fixed.test(k)) {
	          merge = _this.build([], k, merge);
	        } else if (patterns.named.test(k)) {
	          merge = _this.build({}, k, merge);
	        }
	      }
	      return json = $.extend(true, json, merge);
	    });
	    return json;
	  };
	})(jQuery);