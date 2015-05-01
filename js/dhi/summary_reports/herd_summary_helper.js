	/**
	* Code for regression extracted from jqplot.trendline.js
	*
	* Version: 1.0.0a_r701
	*
	* Copyright (c) 2009-2011 Chris Leonello
	* jqPlot is currently available for use in all personal or commercial projects
	* under both the MIT (http://www.opensource.org/licenses/mit-license.php) and GPL
	* version 2.0 (http://www.gnu.org/licenses/gpl-2.0.html) licenses. This means that you can
	* choose the license that best suits your project and use it accordingly.
	*
	**/

	function regression(x, y, typ) {
	  var type = (typ == null) ? 'linear' : typ;
	  var N = x.length;
	  var slope;
	  var intercept;
	  var SX = 0;
	  var SY = 0;
	  var SXX = 0;
	  var SXY = 0;
	  var SYY = 0;
	  var Y = [];
	  var X = [];

	  if (type == 'linear') {
	    X = x;
	    Y = y;
	  }
	  else if (type == 'exp' || type == 'exponential') {
	    for (var i = 0; i < y.length; i++) {
	      // ignore points <= 0, log undefined.
	      if (y[i] <= 0) {
	        N--;
	      }
	      else {
	        X.push(x[i]);
	        Y.push(Math.log(y[i]));
	      }
	    }
	  }

	  for (var i = 0; i < N; i++) {
	    SX = SX + X[i];
	    SY = SY + Y[i];
	    SXY = SXY + X[i] * Y[i];
	    SXX = SXX + X[i] * X[i];
	    SYY = SYY + Y[i] * Y[i];
	  }

	  slope = (N * SXY - SX * SY) / (N * SXX - SX * SX);
	  intercept = (SY - slope * SX) / N;

	  return [slope, intercept];
	}

	function linearRegression(X, Y) {
	  var ret;
	  ret = regression(X, Y, 'linear');
	  return [ret[0], ret[1]];
	}

	function expRegression(X, Y) {
	  var ret;
	  var x = X;
	  var y = Y;
	  ret = regression(x, y, 'exp');
	  var base = Math.exp(ret[0]);
	  var coeff = Math.exp(ret[1]);
	  return [base, coeff];
	}

	/*
	TODO: this function is quite inefficient.
	Refactor it if there is problem with speed.
	*/
	function fitData(data, typ) {
	  var type = (typ == null) ? 'linear' : typ;
	  var ret;
	  var res;
	  var x = [];
	  var y = [];
	  var ypred = [];

	  for (i = 0; i < data.length; i++) {
	    if (data[i] != null && Object.prototype.toString.call(data[i]) === '[object Array]') {
	      if (data[i] != null && data[i][0] != null && data[i][1] != null) {
	        x.push(data[i][0]);
	        y.push(data[i][1]);
	      }
	    }
	    else if(data[i] != null && typeof data[i] === 'number' ){//If type of X axis is category
	      x.push(i);
	      y.push(data[i]);
	    }
	    else if(data[i] != null && Object.prototype.toString.call(data[i]) === '[object Object]'){
	      if (data[i] != null && data[i].x != null && data[i].y != null) {
	        x.push(data[i].x);
	        y.push(data[i].y);
	      }
	    }
	  }

	  if (type == 'linear') {

	    ret = linearRegression(x, y);
	    for (var i = 0; i < x.length; i++) {
	      res = ret[0] * x[i] + ret[1];
	      ypred.push([x[i], res]);
	    }

	    return {
	      data: ypred,
	      slope: ret[0],
	      intercept: ret[1],
	      y: function(x) {
	        return (this.slope * x) + this.intercept;
	      },
	      x: function(y) {
	        return (y - this.intercept) / this.slope;
	      }
	    };
	  }
	  else if (type == 'exp' || type == 'exponential') {

	    ret = expRegression(x, y);
	    for (var i = 0; i < x.length; i++) {
	      res = ret[1] * Math.pow(ret[0], x[i]);
	      ypred.push([x[i], res]);
	    }
	    ypred.sort();

	    return {
	      data: ypred,
	      base: ret[0],
	      coeff: ret[1]
	    };
	  }
	}

// this function allows you to change chart options immediately before chart is rendered
	function pre_render(options_in){
		//if there is a second yAxis, change the color
		if(typeof options_in.yAxis[1] != 'undefined'){
			options_in.yAxis[1].title.style.color = var_arr_graph_colors[1];
		}
		
		if(client_data['block'] == 'peak_milk_trends'){
			
		}
			
		return options_in;
	}
	
	//this function is called in the graph_helper.js file after the JSON data file has loaded.  It can make report specific updates after the data has been loaded (see commented code for example)
	function post_render(app_data){
		if(typeof(chart[app_data['graph_order']]) != 'undefined' && app_data['block'] == 'peak_milk_trends'){
			chart[app_data['graph_order']].addSeries({
				type: 'spline',
				color: chart[app_data['graph_order']].series[0].color,
				marker: {enabled:false},
				enableMouseTracking: false,
				name: '1st Lact Trend',
				data: (function() {return fitData(chart[app_data['graph_order']].series[0].data).data;})()
			});
			chart[app_data['graph_order']].addSeries({
				type: 'spline',
				color: chart[app_data['graph_order']].series[1].color,
				marker: {enabled:false},
				enableMouseTracking: false,
				name: '2+ Lact Trend',
				data: (function() {return fitData(chart[app_data['graph_order']].series[1].data).data;})()
			});
		}
	}
	
