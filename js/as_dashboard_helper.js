/*head.ready("twitter", function(){
	if($("#tweet-feed")){
		$("#tweet-feed").jTweetsAnywhere({
			username: 'agsource',
		    count: 20,
		    showTweetFeed: {
		        showProfileImages: true,
		        showUserScreenNames: true,
		        paging: {
		            mode: 'endless-scroll'
		        }
		    },
		    onDataRequestHandler: function(stats) {
		        if (stats.dataRequestCount < 11) {
		            return true;
		        }
		        else {
		            alert("To avoid struggling with Twitter's rate limit, we stop loading data after 10 API calls.");
		        }
		    }
	    }); 
	}
}); */

if($('#view-benchmarks')) {
	$('#view-benchmarks').bind('click', function(){ document.getElementById('benchmark-form').submit(); });
}

if($('#promo-reports')) {
	
}

function zpre_render_table(div_id, data){
	var tags = ["h2", "h3"];
	data.html = stripTags(data.html, tags);
}

function post_render(data, block_index){
	var div_id = data.block;
	//move headers from report block to dashboard widget
	var h2_div = $("#" + div_id).prevAll('h2').clone();
	var h3_div = $("#" + div_id).parent().find('h3').clone();
	$("#" + div_id).closest('.box').find('h2, h3').remove();
	
	$(h3_div).prependTo($("#" + div_id).closest('.box'));
	$(h2_div).prependTo($("#" + div_id).closest('.box'));
	$("#" + div_id).closest('.box').find('h2, h3').removeClass('block');
	
	//add colors to trend column
	$("#" + div_id + " td:nth-child(3)").each(
		function() {
			if($(this).html() == '-'){
				$(this).addClass('b-down');
			}
			if($(this).html() == '+'){
				$(this).addClass('b-up');
			}
			if($(this).html() == ''){
				$(this).addClass('b-same');
			}
		}
	);
}
/*
function stripTags(html, tags) {
	var div = document.createElement('div');
	div.innerHTML = html;
	for(x in tags){
		var t = div.getElementsByTagName(tags[x]);
		var i = t.length;
		while (i--) {
			t[i].parentNode.removeChild(t[i]);
		}
	}
	return div.innerHTML;
}
*/