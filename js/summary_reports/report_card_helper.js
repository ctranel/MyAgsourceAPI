	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in, section_data){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis != 'undefined') {
			options_in.yAxis.min = 0;
			options_in.yAxis.categories = null;
			options_in.yAxis.max = 100;
			options_in.yAxis.tickInterval = 10;
			//options_in.yAxis.plotLines.color = '#ff0000';
			options_in.yAxis.plotLines = [
			    {color:'#ff0000',width:4,value:20},
			    {color:'#194d4b',width:4,value:80}
			];
			options_in.yAxis.plotBands = [
			    {color:'rgba(204,100,100,.1)',from:0,to:20},
			    {color:'rgba(100,204,100,.1)',from:80,to:100}
			];
		}
alert(JSON.stringify(options_in));
		$('.chart-container').each(function(){
			$(this).addClass('chart-only').removeClass('chart-odd').removeClass('chart-even').removeClass('chart-last-odd');
		})
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(app_data){
	}