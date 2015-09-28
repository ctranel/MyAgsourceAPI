	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(client_data, block_index){
		if(typeof(client_data) === 'undefined' || typeof(client_data['block']) === 'undefined') return false;
		if(client_data['block'] == 'test_day_results'){
			$('th[id^="scc_cnt"]').each(function(){
				$('#test_day_results').find("tr td:nth-child(" + ($(this).index() + 1) + ")").each(function(){
					if(parseInt($(this).html().replace(/,/g,'')) >= 200){
						$(this).css('color', 'red').css('fontWeight', 'bold');
					}
				});
			});
		}
	}
