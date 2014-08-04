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

function pre_render_table(div_id, data){
	var tags = ["h2", "h3"];
	data.html = stripTags(data.html, tags);
}

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
