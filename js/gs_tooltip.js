$(document).ready(
	function() {
		if ( $('#qtile-table').length ) $('<span class="tip-link"> <a title="Quartile Rankings Tip" id="qtile-table_tip" class="tooltip" rel="<p>Animals are divided into quartiles based on their NetMerit$. The average NM$ for each quartile is listed. One of your goals may be to replace the 25% lowest genetic merit cows in your herd with the highest two quartiles of heifers calving in the next year.</p><p>NetMerit$ values are updated starting a week after each USDA genetic evaluation in April, August and December. Updated NetMerit$ values appear here after the next test day. All other values are updated after each test day&#39;s results are processed.</p>">(tip)</a></span>').appendTo('#qtile-table');  
		if ( $('#file_exports').length ) $('<span class="tip-link"> <a title="File Export Tip" id="file_exports_tip" class="tooltip" rel="<p>A unique feature of the Genetic Selection Guide is the capability to download report data into an Excel spreadsheet by simply clicking the &quot;CSV (Excel)&quot; link. Excel offers you the power to combine both the Cow and Heifer reports into one report. Excel allows more sorting options and the capability to add more data fields and even to make up a new index of your own for breeding and replacement decisions.</p>">(tip)</a></span>').appendTo('#file_exports');  
		if ( $('#set_filters').length ) $('<span class="tip-link"> <a title="Set Filters Tip" id="set_filters_tip" class="tooltip" rel="<p>For many decisions, the entire Genetic Selection Guide may include more animals than needed. If you are looking for cows to cull, you may only need to focus on the 3rd and 4th Quartiles. You can click on them and they will be the only cows listed. If you want only animals that do not have a quartile ranking, click &quot;None&quot; and &quot;Apply Filter&quot;. You can also set the filter to include certain lactation groups by clicking on them and excluding unclicked groups. The last filter is for Days in Milk (DIM). If you want to include all cows over 75 DIM, enter &quot;Between &#39;75&#39; and &#39;999&#39;.&quot; Click &quot;Apply Filter&quot; to generate a list of cows meeting the criteria you just selected. After clicking &quot;Apply Filter,&quot; any subsequent File Exports (to Excel, PDF or to the printer) will only include the cows remaining after the filter was applied. If you want to bring back all the animals on your list, simply click &quot;Reset Filter.&quot;</p>">(tip)</a></span>').appendTo('#set_filters');  

		//instantiate variables for attaching tool tips to headers (initial and fixed)
		var cow_lactation_data_div;
		var pedigree_div;
		var avg_dev_from_herd_305_me_div;
		var dam_production_div;
		var lact_num_div;
		var net_merit_amt_div;
		var decision_guide_qtile_num_div;
		var confirmed_due_date_div;
		var avg_days_open_div;
		var avg_linear_score_div;
		var avg_transition_cow_index_div;
		//cheating here, calling function for fixed table plug-in with qtip
		$('.tbl').fixedtableheader({headerrowsize:3});
		//set up tooltips for original header
		set_tooltips(0);
		attach_tooltips(0);
		//set up tooltips for fixed header
		set_tooltips(1);
		attach_tooltips(1);

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
								color : '#004147'
							},
							width : { // Set the tooltip width
								max : 500
							},
							tip : true, // Apply a speech bubble tip to the
							// tooltip at the designated tooltip
							// corner
							title : {
								background : '#004147',
								color : '#DBFFD4'
							}
						}
					}
				);
			}
		);
	}
);

function set_tooltips(count){
	cow_lactation_data_div = '<span class="tip-link"> <a title="Report Usage Tip" id="cow_lactation_data_tip' + count + '" class="tooltip" rel="<p>The Genetic Selection Guide has powerful sorting capability. The sorting capability allows you to sort the entire list of animals or the ones left after based on any of the criteria below. To do a sort, click at the top of the column in the teal area on the written text. Sorts are done alphanumerically, or so that the most desirable values are at the top of the list. If you would like to reverse the order, simply click on the text at the top of the column again and the list will resort.</p><p>First four columns – Four types of identification are listed. Users may choose whether to sort their cows by Visible ID, Barn Name, Control Number or Registration Number, Ear Tag or RFID.</p>">(tip)</a></span>';
	hiefer_data_div = '<span class="tip-link"> <a title="Report Usage Tip" id="heifer_data_tip' + count + '" class="tooltip" rel="<p>The Genetic Selection Guide has powerful sorting capability. The sorting capability allows you to sort the entire list of animals or the ones left after based on any of the criteria below. To do a sort, click at the top of the column in the teal area on the written text. Sorts are done alphanumerically, or so that the most desirable values are at the top of the list. If you would like to reverse the order, simply click on the text at the top of the column again and the list will resort.</p><p>First four columns – Four types of identification are listed. Users may choose whether to sort their cows by Visible ID, Barn Name, Control Number or Registration Number, Ear Tag or RFID.</p>">(tip)</a></span>';
	pedigree_div = '<span class="tip-link"> <a title="Pedigree Tip" id="pedigree_tip' + count + '" class="tooltip" rel="<p>Sire and Maternal Grandsire NAAB numbers and the Dam&#39;s ID are listed. If any of these fields are blank or incorrect, provide your Field Technician with the correct data or call AgSource Customer Service at 800-236-0097.</p>">(tip)</a></span>';
	avg_dev_from_herd_305_me_div = '<div class="tip-link"><a title="Mature Equivalent 305 Day Tip" id="avg_dev_from_herd_305_me_tip' + count + '" class="tooltip" rel="<p>Adjusting production records with Mature Equivalent 305 day factors creates a level playing field for comparing different aged cows with lactations longer than 305 days.</p><p>The Milk, Fat and Protein average deviations from herd 305 day Mature Equivalent are calculated on a lactation by lactation basis. For example, if a cow has three lactations, her first lactation&#39;s 305 day ME production is compared against her herdmates&#39; 305 day ME production in the same year. The same is done on each of her subsequent lactations. Her 305 ME projection is used on lactations in progress. Each lactation&#39;s production deviation is averaged to create the value displayed.</p><p>Due to genetic variation, cows with low NM$ may infrequently beat the odds and receive the correct genetic combination from their parents and be excellent producers. In some cases, a cow may have a high NM$ value and a low or negative production deviation from herdmates. This could be due to calving with twins or some other problem. Again, genetic variation can infrequently result in a cow with a high NM$ value not meeting expectations. The bottom line is that when both production and NM$ information are available, actual production information should outweigh NM$ values in culling decisions.</p>">(tip)</a></div>';
	dam_production_div = '<span class="tip-link"> <a title="Dam Production Tip" id="dam_production_tip' + count + '" class="tooltip" rel="<p>The &quot;Avg Dev From Herd 305 ME&quot; information for dams is calculated in exactly the same way as the Milk, Fat and Protein deviation values for the cow herself. Many times the &quot;Dam Production&quot; columns are blank. Generally, the reason is the cow has not been tested for the past five years and her records have been archived. Another possible reason is the dam or the animal herself was purchased and AgSource never received earlier DHI records. Even though the records are not visible here, if the cow was properly identified on a DHI testing program in the U.S., then USDA received the dam&#39;s records and they are used in calculating the cow&#39;s NetMerit$ value.</p>">(tip)</a></span>';
	lact_num_div = '<div class="tip-link"><a title="Lactation Number Tip" id="lact_num_tip' + count + '" class="tooltip" rel="<p>This is the current lactation number for this cow.</p>">(tip)</a></div>';

	net_merit_amt_div = '<div class="tip-link"><a title="Net Merit Amount Tip" id="net_merit_amt_tip' + count + '" class="tooltip" rel="<p>NM$ is the additional lifetime profit an animal is expected to make compared to an average animal (average animal = 0 NM$). The profit function approach used in deriving NM$ lets breeders select for many traits by combining the incomes and expenses for each trait into an accurate measure of overall profit.</p><p>First lactation heifers will have an Estimated NetMerit$ value until a week after their first USDA genetic evaluation following calving. The actual NetMerit$ value replaces the estimated one on this report after processing their subsequent test day data.</p><p>Estimated NM$ values are calculated as follows:<table><tr><th>If...</th><th>The Estimated NetMerit$ =</th></tr><tr><td>both sire and dam are identified and have NM$ values</td><td>(Sire&#39;s NM$ + Dam&#39;s NM$)/2</td></tr><tr><td>only one parent is identified and has a NM$ value</td><td>Parent&#39;s NM$/2</td></tr><tr><td>neither parent is identified</td><td>No Value</td></tr></table></p><p>*An asterisk next to Net Merit Amount indicates that the number is estimated.</p><h2>Genomic Indicators are as follows:</h2><ul><li><b>G1:</b> Animal was genotyped and evaluation was computed using its genotype.</li><li><b>G2:</b> Evaluation includes information from genotypes (or imputed genotypes) of ancestors, but the animal was not genotyped.</li><li><b>G2:</b> Animal\'s genotype was imputed from the progeny and the imputed genotype was used in the evaluation.</li><li><b>GU:</b> Genomics Unknown.</li></ul>">(tip)</a></div>';
	est_net_merit_amt_div = '<div class="tip-link"><a title="Estimated Net Merit Amount Tip" id="est_net_merit_amt_tip' + count + '" class="tooltip" rel="<p>NM$ is the additional lifetime profit an animal is expected to make compared to an average animal (average animal = 0 NM$). The profit function approach used in deriving NM$ lets breeders select for many traits by combining the incomes and expenses for each trait into an accurate measure of overall profit.</p><p>Estimated NM$ values are calculated as follows:<table><tr><th>If...</th><th>The Estimated NetMerit$ =</th></tr><tr><td>both sire and dam are identified and have NM$ values</td><td>(Sire&#39;s NM$ + Dam&#39;s NM$)/2</td></tr><tr><td>only one parent is identified and has a NM$ value</td><td>Parent&#39;s NM$/2</td></tr><tr><td>neither parent is identified</td><td>No Value</td></tr></table></p><h2>Genomic Indicators are as follows:</h2><ul><li><b>G1:</b> Animal was genotyped and evaluation was computed using its genotype.</li><li><b>G2:</b> Evaluation includes information from genotypes (or imputed genotypes) of ancestors, but the animal was not genotyped.</li><li><b>G2:</b> Animal\'s genotype was imputed from the progeny and the imputed genotype was used in the evaluation.</li><li><b>GU:</b> Genomics Unknown.</li></ul>">(tip)</a></div>';

	decision_guide_qtile_num_div = '<div class="tip-link"><a title="Net Merit Quartile Tip" id="decision_guide_qtile_num_tip' + count + '" class="tooltip" rel="<p>Cows with a &quot;1&quot; in this column are ranked in the 75-99th percentile (the highest NetMerit$ scores of the herd). Those with a &quot;2&quot; are in the 50-74th percentile. Those with a &quot;3&quot; are in the 25-49th percentile. Those with a &quot;4&quot; are in the 1-24th percentile (the lowest scores).</p>">(tip)</a></div>';
	confirmed_due_date_div = '<div class="tip-link"><a title="Confirmed Due Date Tip" id="confirmed_due_date_tip' + count + '" class="tooltip" rel="<p>If AgSource has a recorded pregnancy confirmation, the cow&#39;s due date is displayed.</p>">(tip)</a></div>';
	avg_days_open_div = '<div class="tip-link"><a title="Average Days Open Tip" id="avg_days_open_tip' + count + '" class="tooltip" rel="<p>This column contains the average days open of all lactations for the cow listed. Block I of the Herd Summary has the herd&#39;s current average &quot;Days Open, PG&quot; to use in comparing individual cows. Herd owners not reporting pregnancy confirmations have no data in this column.</p>">(tip)</a></div>';
	avg_linear_score_div = '<div class="tip-link"><a title="Average Linear Score Tip" id="avg_linear_score_tip' + count + '" class="tooltip" rel="<p>Average Linear Scores are calculated by converting each cow&#39;s end of lactation average SCC to a Linear Score and then averaging all the lactation Linear Scores. The industry average Linear Score is very close to 3.0.</p>">(tip)</a></div>';
	avg_transition_cow_index_div = '<div class="tip-link"><a title="Average Transition Cow Index&reg; Tip" id="avg_transition_cow_index_tip' + count + '" class="tooltip" rel="<p>The Transition Cow Index&reg; has a genetic component much like SCC does. It is also a measure of a cow&#39;s resilience. Only those herds on a TCI option will have information in this column. For those members receiving a Fresh Cow Summary, their herd&#39;s average TCI is in Block A.</p>">(tip)</a></div>';
}

function attach_tooltips(count){
	if ( $('.heifer_data:eq(' + count + ')').length ) $(hiefer_data_div).appendTo('.heifer_data:eq(' + count + ')');  
	if ( $('.cow_lactation_data:eq(' + count + ')').length ) $(cow_lactation_data_div).appendTo('.cow_lactation_data:eq(' + count + ')');  
	if ( $('.pedigree:eq(' + count + ')').length ) $(pedigree_div).appendTo('.pedigree:eq(' + count + ')');  
	// there can be 2 sections for avg_dev_from_herd_305_me, so we check must accomodate that possibility 
	if (count == 1 && $('.avg_dev_from_herd_305_me').length > 2){
		if ( $('.avg_dev_from_herd_305_me:eq(' + 2 + ')').length ) $(avg_dev_from_herd_305_me_div).prependTo('.avg_dev_from_herd_305_me:eq(' + 2 + ')');  
	}
	else{
		if ( $('.avg_dev_from_herd_305_me:eq(' + count + ')').length ) $(avg_dev_from_herd_305_me_div).prependTo('.avg_dev_from_herd_305_me:eq(' + count + ')');  
	}
	if ( $('.dam_production:eq(' + count + ')').length ) $(dam_production_div).appendTo('.dam_production:eq(' + count + ')'); 
	if ( $('.lact_num:eq(' + count + ')').length) $(lact_num_div).prependTo('.lact_num:eq(' + count + ')');  
	if ( $('.net_merit_amt:eq(' + count + ')').length ) $(net_merit_amt_div).prependTo('.net_merit_amt:eq(' + count + ')');  
	if ( $('.est_net_merit_amt:eq(' + count + ')').length ) $(est_net_merit_amt_div).prependTo('.est_net_merit_amt:eq(' + count + ')');  
	if ( $('.decision_guide_qtile_num:eq(' + count + ')').length ) $(decision_guide_qtile_num_div).prependTo('.decision_guide_qtile_num:eq(' + count + ')');  
	if ( $('.confirmed_due_date:eq(' + count + ')').length ) $(confirmed_due_date_div).prependTo('.confirmed_due_date:eq(' + count + ')');  
	if ( $('.avg_days_open:eq(' + count + ')').length ) $(avg_days_open_div).prependTo('.avg_days_open:eq(' + count + ')');  
	if ( $('.avg_linear_score:eq(' + count + ')').length ) $(avg_linear_score_div).prependTo('.avg_linear_score:eq(' + count + ')');  
	if ( $('.avg_transition_cow_index:eq(' + count + ')').length ) $(avg_transition_cow_index_div).prependTo('.avg_transition_cow_index:eq(' + count + ')');  
	//if ( $('#myDiv').length ) $('<div class="tip-link"><a title=" Tip" id="_tip" class="tooltip" rel="<p></p>">(tip)</a></div>').prependTo('#');  
}