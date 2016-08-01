var arr_sort_by = new Array();
var arr_sort_order = new Array();
var inline_form_width = 0;

head.ready(function() {
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
		//attach filter criteria to csv link
		if($('.download-csv')){
			$('.download-csv').click(function(ev){
				params = encodeURIComponent(JSON.stringify($("#filter-form").serializeObject()));
				this.href += params;
	ev.preventDefault();
			});
		}
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

	if ($('.hasDatepicker').length > 0){
		$('.hasDatepicker').datepick({dateFormat: 'mm-dd-yyyy'});
	}
});

//fixed header
function createFixedHeader(full_table_id){
	//clone each table header
	//$('.tbl').each(function(){
	var $full_table = $('#' + full_table_id);
	var $fh_table = $("#fh-" + full_table_id);
	var $header = $full_table.find("thead:first").clone();

    $fh_table.append($header);
//    $fh_table.css('width', $full_table.css('width'));
//	$fh_table.css('tableLayout', 'fixed');
    //find each th of fixed header and set width equal to that of full table
	$fh_table.find('th').each(function(index){
	    var index2 = index;
	    $(this).css('width', function(index){
	    	return ($full_table.find("th:eq(" + index2 + ")").css('width'));
	    });
	});
	$fh_table.hide();
	
	//});
		
	$(window).bind("scroll", function() {
		var pageOffsetTop = $(this).scrollTop() + $('#header').outerHeight();
		$('.tbl').each(function(){
	    	if(!$('#fh-' + $(this).attr('id')).length){
	    		return;
	    	}
			var tableOffsetTop = $(this).offset().top;
			var tableOffsetBottom = tableOffsetTop + $(this).height();
	    	$fixedHeader = $('#fh-' + $(this).attr('id'));
	    	$fixedHeader.css('width', $full_table.css('width'));
	    	//if the header is above the top of the window but the bottom is not
	    	if (pageOffsetTop >= tableOffsetTop && pageOffsetTop <= (tableOffsetBottom - $fixedHeader.height())) {
		    	if($fixedHeader.is(":hidden")){
		    		$fixedHeader.show();
		    	}
		    	//set horizontal position if there is scrolling
		    	$fixedHeader.offset({left: $(this).offset().left});
		    }
		    //if top of table is within window or the bottom of the table is above the top of the window
		    else if ((pageOffsetTop < tableOffsetTop || pageOffsetTop > (tableOffsetBottom - $fixedHeader.height())) && !$fixedHeader.is(":hidden")) {
		        $fixedHeader.hide();
            }
	    });
	});
	
	//$(window).scroll();
}


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
    var size = 0;
    for (var key in obj) {
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