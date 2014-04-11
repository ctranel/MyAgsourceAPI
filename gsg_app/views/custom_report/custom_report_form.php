<?php if(isset($page_header) !== FALSE) echo $page_header; ?>
<div class='mainInfo'>
	<?php if(isset($page_heading) !== FALSE) echo heading($page_heading); ?>
	<?php echo form_input($block_id); //form_input?>
	<?php echo form_open("custom_report/custom_report/create", $form_attr);?>
	<div id="gen-info-top">
	    <h2>General Information</h2>
	    <p id="p-report-name"><?php echo form_label('Report Name', 'report_name', NULL, $report_name); ?>
	     	<?php echo form_input($report_name);?>
	    </p>
	    <p id="p-report-description"><?php echo form_label('Report Description', 'report_description', NULL, $report_description); ?>
	      	<?php echo form_input($report_description);?>
	    </p>
	    <h2>Placement of Report on Site</h2>  
	    <p id="p-report-super-section"><?php echo form_label('Report Parent Section', 'report_super_section');
			if(!empty($report_super_section_options)):
	      		echo form_dropdown('super_section_id', $report_super_section_options, $report_section_selected, $report_super_section);
			endif; ?>
	    </p>
	    <p id="p-report-section"><?php echo form_label('Report Section', 'report_section');
			if(empty($report_section_options)) $report_section_options = array();
	      	echo form_dropdown('section_id', $report_section_options, $report_page_selected, $report_section); ?>
	    </p>
	    <p id="p-report-page"><?php echo form_label('Report Page', 'report_page');
			if(empty($report_page_options)) $report_page_options = array();
	      	echo form_dropdown('page_id', $report_page_options, $report_page_selected, $report_page); ?>
	    </p>
	    <p id="p-insert-after"><?php echo form_label('Insert After', 'insert_after', NULL, $insert_after);
			if(empty($insert_after_options)) $insert_after_options = array();
	      	echo form_dropdown('insert_after', $insert_after_options, $insert_after_selected, $insert_after); ?>
	    </p>
	    <h2>Display Information</h2><i>add option to include sum / avg / count row at end of table</i>
	    <p id="p-report-display"><?php echo form_label('Report Display', 'report_display');
			if(!empty($report_display_options)):
				foreach($report_display_options as $k=>$v):
					if(!empty($k)): ?>
							<span class="form-radio"><?php echo form_radio('report_display_id', $k, $report_display_selected == $k, 'class = "radio display-options"'); echo $v; ?></span>
					<?php endif;
				endforeach;
			endif; ?>
	    </p>
	    <p id="p-max-rows"><?php echo form_label('Max Rows', 'max_rows', NULL, $max_rows); ?>
	      	<?php echo form_input($max_rows);?>
	      	
	    </p>
	
	    <p id="p-chart-type" class="chart-only"><?php echo form_label('Chart Type', 'chart_type_id');
			if(!empty($chart_type_options)):
	      		echo form_dropdown('chart_type_id', $chart_type_options, NULL, $chart_type);
			endif; ?>
	    </p>
	</div>
	<h2>Report Builder</h2>
	<div id="choose-field" style="overflow: hidden">
		<p id="p-cow-or-summary">
			<span class="form-radio"><?php echo form_radio('cow_or_summary', 'cow', $cow_or_summary_selected == 'cow', 'class = "radio cow_or_summary"'); ?>Cow</span> <span class="form-radio"><?php echo form_radio('cow_or_summary', 'summary', $cow_or_summary_selected == 'summary', 'class = "radio cow_or_summary"'); ?>Summary</span>
		</p>
		<p id="p-choose-table"><?php echo form_label('Choose Table', 'choose_table');
			if(empty($choose_table_options)) $choose_table_options = array();
	      		echo form_dropdown('choose_table_id', $choose_table_options, NULL, $choose_table); ?>
		</p>
		<p id="p-choose-field"><?php echo form_label('Drag fields from this list below to the shaded fields on the report builder to the right', 'choose_field'); ?>
			<div id="field-container">
				<?php if(!empty($choose_field_options)): ?>
		      		<div id="list-<?php echo $choose_field_options['id']; ?>" class="draggable" title="<?php echo $choose_field_options['db_field_name']; ?>" draggable="true"><?php echo $choose_field_options['name']; ?></div>
				<?php endif; ?>
				Select a table to display field options.
			</div>
		</p>
	</div>
	<!-- ids in the sections below use _ and - characters as delimiters.  In id fields that use delimiters, those delimiters cannot be used in the descriptive part of the name -->
	<div id="assign-field-to-table" class="form-grouping table-only">
		<h2>Add Table Header Groupings and Columns</h2><a class="add-header-row link">Add header grouping row</a>
		<table id="table-build">
			<tr id="hgrow-1">
				<th><input name="head_group[1][1]" id="head_group-1-1" value="Enter text here to add a header grouping">
					<a class="split link">split</a>
					<a class="remove-head link">X</a>
					<input name="head_group_parent_index[1][1]" id="head_group_parent_index-1-1" type="hidden">
				</th>
			</tr>
			<tr class="fields-in">
				<th id="wcolumn-0">
					<input name="column[0]" id="column-0" value="Drop New Column Here">
					<div class="column-extra">
						<input name="col_head_group_index[0]" id="col_head_group_index-0" type="hidden">
						<select name="aggregate[0]" class="column-aggregate">
		                    <option value="">Summarization</option>
		                    <option value="COUNT">Count</option>
		                    <option value="SUM">Sum</option>
		                    <option value="AVG">Avg</option>
		                    <option value="MAX">Max</option>
		                    <option value="MIN">Min</option>
						</select>
						<a class="remove-col link">X</a>
					</div>
				</th>
			</tr>
		</table>
	</div>
	<div id="assign-field-to-trend" class="form-grouping trend-only chart-only">
		<h2>Add Axes and Data</h2>
		<div id="yaxis" class="chart-only">
			<div id="wyaxis-0">
				<label for="yaxis_label[0]">Text</label><input name="yaxis_label[0]" id="yaxis_label-0" size="10" maxlength="50"><br>
				<label for="yaxis_min[0]">Min</label><input name="yaxis_min[0]" id="yaxis_min-0" size="4"><br>
				<label for="yaxis_max[0]">Max</label><input name="yaxis_max[0]" id="yaxis_max-0" size="4"><br>
				Opposite side<?php echo form_checkbox('yaxis_opposite[0]', 'asc', FALSE, 'class = "checkbox yaxis"'); ?><br>
				<a class="remove-fld link">X</a>
				<a class="add-fld link">+</a>
			</div>
		</div>
		<table id="trend-build">
			<tr class="fields-in">
				<th id="wtrendfield-0">
					<input name="trendcolumn[0]" id="trendcolumn-0" value="Drop Display Field Here">
					<select name="trendaggregate[0]">
	                    <option value="">Summarization</option>
	                    <option value="COUNT">Count</option>
	                    <option value="SUM">Sum</option>
	                    <option value="AVG">Avg</option>
	                    <option value="MAX">Max</option>
	                    <option value="MIN">Min</option>
					</select>
<?php 				if(!empty($chart_type_options)):
						if(isset($chart_type_options[''])) $chart_type_options[''] = str_replace('Select one', 'Select Display Type', $chart_type_options['']);
	      				echo form_dropdown('trendgraph_type[0]', $chart_type_options, NULL, $series_chart_type);
					endif; ?>
					<select name="trendyaxis[0]">
	                    <option value="0">Y Axis</option>
	                    <option value="0" selected>Default</option>
					</select>
					<a class="remove-trend-col link">X</a>
				</th>
			</tr>
		</table>
		<div id="xaxis-trend" class="chart-only">
			<div id="wxaxis-0">
				<label for="xaxis_label">Text</label><input name="xaxis_label" id="xaxis_label" size="10" maxlength="50"><br>
				<label for="xaxis_field">Timespan Field</label><select name="xaxis_field" id="xaxis_field"></select><br>
				<select name="xaxis_datatype">
	                    <option value="">Select Data Type</option>
	                    <option value="datetime">Date/Time</option>
	                    <option value="linear">Numeric</option>
				</select>
			</div>
		</div>
	</div>
	
	<div id="set-sort-by" class="form-grouping table-only">
		<h2>Sort By</h2>
		<div id="wsortby-0">
			<input name="sort_by[0]" id="sortby-0">
			<span class="form-radio"><?php echo form_radio('sort_order[0]', 'asc', FALSE, 'class = "radio sort-order"'); ?>ASC</span> <span class="form-radio"><?php echo form_radio('sort_order[0]', 'summary', FALSE, 'class = "radio sort-order"'); ?>DESC</span>
			<a class="remove-fld link">X</a>
		</div>
	</div>
	<div id="set-where" class="form-grouping">
		<h2>Include Data Matching This Criteria:</h2>
		<div id="setwheregroup_0" class="form-subgrouping">
			<div id="wwhere_0-0">
				<input name="where[0]" id="where_0-0">
				<select name="operator[0]">
                    <option value="=">Equal</option>
                    <option value="&gt;">Greater than</option>
                    <option value="&gt;=">Greater than or equal</option>
                    <option value="&lt;">Less than</option>
                    <option value="&lt;=">Less than or equa</option>
                    <option value="!=">Not equal</option>
                    <option value="LIKE">Equals (with '%' and '*' wildcards)</option>
                    <option value="NOT LIKE">Not equal (with '%' and '*' wildcards)</option>
                    <option value="IS NULL">Has no value</option>
                    <option value="IS NOT NULL">Has any value</option>
				</select>
				<input name="where_value[0]">
				<a class="remove-fld link">X</a>
			</div>
		</div>
		<input type="button" id="add-where-grouping" value="Add Grouping">
	</div>
	<div id="report-summary" class="report-summary">
	    <div id="p-cnt_row" class="table-only">
	      	<?php echo form_checkbox('cnt_row', '1', FALSE, 'class = "checkbox"'); ?>
	    	<?php echo form_label('Count Row', 'cnt_row', NULL); ?>
	    </div>
	    <div id="p-sum_row" class="table-only">
	      	<?php echo form_checkbox('sum_row', '1', FALSE, 'class = "checkbox"'); ?>
	    	<?php echo form_label('Sum Row', 'sum_row', NULL); ?>
	    </div>
	    <div id="p-avg_row" class="table-only">
	      	<?php echo form_checkbox('avg_row', '1', FALSE, 'class = "checkbox"'); ?>
	    	<?php echo form_label('Average Row', 'avg_row', NULL); ?>
	    </div>
	    <div id="p-bench_row">
	      	<?php echo form_checkbox('bench_row', '1', FALSE, 'class = "checkbox"'); ?>
	    	<?php echo form_label('Benchmark Row', 'bench_row', NULL); ?>
	    </div>
		<div id="wpivot_field" class="table-only">
			<p>To rotate the table, drag the field that should be used as the header here (usually used to make test dates or other dates the table header)</p>
	    	<?php echo form_label('Rotate Field', 'pivot_db_field', NULL); ?>
			<?php if(empty($pivot_db_field_options)) $pivot_db_field_options = array();
	      	echo form_dropdown('pivot_db_field', $pivot_db_field_options, $pivot_db_field_selected, $pivot_db_field); ?>
		</div>
	</div>
	
	




      <p><?php echo form_submit('submit', 'Create Report', 'class="button"');?></p>
   <?php echo form_close();?>

</div>
<?php if(isset($page_footer) !== false) echo $page_footer;