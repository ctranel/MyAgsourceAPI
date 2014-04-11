$(document).ready(
	function() {
		//if ( $('#select-pstring').length ) $('<span class="tip-link"> <a title="Select PString Tip" id="select-pstring_tip" class="tooltip" rel="">(tip)</a></span>').appendTo('#select-pstring');  
		if ( $('#select-benchmarks').length ) $('<span class="tip-link"> <a title="Select Benchmark Tip" id="select-benchmarks_tip" class="tooltip" rel="<p>In addition to comparing your herd&#39;s to your own cohort, the online Report Card allows you to compare your herd with any other benchmark group.  Simply select an alternative benchmark group to see approximately how your herd compares with that group.</p>">(tip)</a></span>').appendTo('#select-benchmarks');  
		if ( $('#select-chart').length ) $('<span class="tip-link"> <a title="Select Chart Tip" id="select-chart_tip" class="tooltip" rel="<p>The Herd Report Card reports your herd&#39;s performance and shows graphically how it compares to similar herds.  The data is compiled so that higher percentiles always represent what is generally considered the most desirable performance.  Each chart provides a different group of data with which you can compare your herd.</p>">(tip)</a></span>').appendTo('#select-chart');  

		$('#main-content a[title], #fixedtableheader0 a[title]').each(
			function() {
				$(this).qtip(
					{
						content : {
							// Set the text to an image HTML string with the
							// correct src URL to the loading image you want
							// to use
							// text: '<img class="throbber"
							// src="/projects/qtip/images/throbber.gif"
							// alt="Loading..." />',
							// url: $(this).attr('rel'), // Use the rel
							// attribute of each element for the url to load
							text : $(this).attr('rel'),
							title : {
								text : $(this).attr('title'), // Give the  tooltip a title
								button: 'Close' // Show a close link in the title
							}
						},
						position : {
							//target : 'mouse',
							corner : {
								target : 'topLeft', // Position the tooltip
								// above the link
								tooltip : 'bottomRight'
							},
							viewport : $(window),
							adjust : {
								screen : true,
								scroll : false
							// Keep the tooltip on-screen at all times
							}
						},
						show : {
							//delay: 500,
							when : 'click',
							solo : true
						// Only show one tooltip at a time
						},
						hide : 'blur',//'mouseout',
						
						style : {
							padding : 5,
							background : '#F4E8AF',
							color : 'black',
							textAlign : 'left',
							border : {
								width : 1,
								radius : 4,
								color : '#003C39'
							},
							width : { // Set the tooltip width
								max : 500
							},
							tip : true, // Apply a speech bubble tip to the
							// tooltip at the designated tooltip
							// corner
							title : {
								background : '#003C39',
								color : '#DBFFD4'
							}
						}
					}
				);
			}
		);	
	}
);

