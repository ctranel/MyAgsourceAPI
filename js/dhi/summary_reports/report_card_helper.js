	// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in, section_data){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis != 'undefined') {
			if(section_data.block.substring(0, 7) !== 'rc_long'){
				var tmpdata = Array();
				tmpdata[0] = options_in.series[0].data
				tmpdata[1] = '';
				var x = 1;
				while(typeof(options_in.series[x]) != 'undefined'){
					if(typeof(options_in.series[x].data) != 'undefined') tmpdata[1] = tmpdata[1] + options_in.series[x].data;
					x++;
				}
				options_in.series = [
				    {type:"bar",name:"Percentile",data:tmpdata[0]},
				    {type:"scatter",marker:{radius:0,data:tmpdata[1]}}
				];
				Highcharts.setOptions({
					legend: {enabled: false}
				});
			}

			options_in.tooltip.formatter = function(){return this.y + ["th","st","nd","rd"][!(this.y%10>3||Math.floor(this.y%100/10)==1)*this.y%10] + ' Percentile';};
			options_in.plotOptions = {}
			options_in.plotOptions.bar = {
				pointWidth: 20,
	            dataLabels: {
	               enabled: true,
	               align: 'right',
	               x: -8,
	               y: 4,
	               color: '#c0c0c0',
	               formatter:function(){return this.point.value;}
	            }
	        },
	        options_in.plotOptions.scatter = {
	            dataLabels: {
	                enabled: true,
	                align: 'right',
	                y: 22,
	                x: -3,
	                color: '#AA4643',
	                formatter:function(){return this.point.val;}
	            }
	        },

			options_in.yAxis.min = 0;
			options_in.yAxis.categories = null;
			options_in.yAxis.max = 100;
			options_in.yAxis.tickInterval = 10;
			options_in.yAxis.plotLines = [
			    {color:'#ff0000',width:4,value:20},
			    {color:'#194d4b',width:4,value:80}
			];
			options_in.yAxis.plotBands = [
			    {color:'rgba(204,100,100,.1)',from:0,to:20},
			    {color:'rgba(100,204,100,.1)',from:80,to:100}
			];
		}
		$('.chart-container').each(function(){
			$(this).addClass('chart-only').removeClass('chart-odd').removeClass('chart-even').removeClass('chart-last-odd');
		})
	}
