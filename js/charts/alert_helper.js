var base_options = {
	chart : {
		defaultSeriesType : 'spline'
	},
	title : {
		text : "SCC/Milk Graph"
	},
	exporting: {
		filename : 'Alert'
	},
	xAxis : {
		labels : {
			rotation : '30',
			align : 'left',
			x : -3
		},
		title : {
			text : "Days in Milk"
		}
	},
	yAxis : [ {
		title : {
			text : "Milk"
		}
	}, {
		title : {
			text : "SCC"
		},
		opposite : true,
		min : 0
	}, {
		title : {
			text : "Animal Count"
		},
		opposite : true,
		min : 0
	} ],

	tooltip : {
		shared : true,
		crosshairs : true,
		formatter : function() {
			var s = '<b>' + this.x + ' DIM</b>';
			$.each(this.points, function(i, point) {
				s += '<br/>' + point.series.name + ': ' + point.y;
			});
			return s;
		}
	},

	plotOptions : {
		series : {
			cursor : 'pointer',
			pointWidth : 100,
			shadow : false
		},
		column: {
			width: 4
		}
	},
	series : [
		{
			type : "spline",
			name : "Milk",
			yAxis : 0,
			zIndex : 3
		}, {
			type : "spline",
			name : "SCC",
			yAxis : 1,
			zIndex : 2
		}, {
			type : "column",
			name : "Animal Count",
			pointWidth : 10,
			minPointLength : 2,
			yAxis : 2,
			zIndex : 1
		}
	]
};

// var chart = new Highcharts.Chart(options);
