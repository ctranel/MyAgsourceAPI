var arr_sort_by = new Array();
var arr_sort_order = new Array();
var inline_form_width = 0;

// set all inline forms to be the same width
$('.handle').each(function(){
	//get the width of the section that immediately follows the .handle element
	var width = $(this).next().width();
	if(width > inline_form_width){
		inline_form_width = width;
	}
});
$('.handle').each(function(){
	$(this).width(inline_form_width);
	$(this).next().width(inline_form_width);
});
// end set all inline forms to be the same width

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
})

function closeExpanded(){
	$('.expanded').removeClass('expanded');
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
*/$('.ajax-popup').magnificPopup({
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
//	    hide: {
//	    	event: false,
//	    	distance: 180,
//	    	leave: false
//	    }
	});
}


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
