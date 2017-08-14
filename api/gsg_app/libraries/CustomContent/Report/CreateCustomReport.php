<?php
namespace myagsource\CustomContent\Report;

/**
* Name:  Custom Report Library
*
* Author: ctranel
*
* Created:  06.28.2013
*
* Description:  Logic and service functions to support the custom report model.
*
* Requirements: PHP5 or above
*
*/


class CreateCustomReport
{
    /**
     * datasource
     * @var object
     **/
    protected $datasource;

    /**
     * id of block in which report resides
     * @var int
     **/
    protected $block_id;
    /**
     * id of report
     * @var int
     **/
    protected $report_id;
    /**
     * id of user (optional)
     * @var int
     **/
    protected $user_id;

    /**
     * does the report contain aggregated columns?
     * @var boolean
     **/
	protected $has_aggregates = FALSE;


	public function __construct(\custom_report_model $datasource, $report_id, $user_id=null)
	{
        $this->datasource = $datasource;
        $this->report_id = $report_id;
        $this->user_id = $user_id;
    }

	/**
	 * add_report
	 *
	 * Adds each section of the block report based.
	 *
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function add_report($input){
	    $report_data = $this->datasource->reportMeta($this->report_id);

		$this->datasource->start_transaction();

		//tables
        if($report_data['display_type_id'] == 1 || $report_data['display_type_id'] == 3){
		    if(isset($input['pivot_db_field']) && !empty($input['pivot_db_field'])){
		        $this->updateReport(['pivot_db_field' => $input['pivot_db_field']]);
            }
echo "start <br>";
            $this->header_groups($input['table_header']);
echo "header_groups <br>";
            $header_row_cnt = count($input['table_header']);
			$this->table_columns($input['table_header'][$header_row_cnt - 1]);
echo "table_columns <br>";
            $this->whereGroup($input['where']);
echo "where <br>";
			$this->sort_by($input['sort']);
echo "sort_by <br>";
		}
		//chart displays
		//@todo: add a section for type 5 that will add categories to x axis
		elseif($input['display_type_id'] == 2 || $input['display_type_id'] == 5){
echo "yaxis <br>";
			$this->yaxes();
echo "xaxis <br>";
			$this->xaxis();
echo "trend_columns <br>";
			$this->trend_columns();
//echo "group_by <br>";
//			$this->group_by();
		}
		else{
			die('I do not recognize the display type');
		}
		if ($this->datasource->trans_status() === FALSE){
			die($this->datasource->error());
			return FALSE;
		}

		$this->datasource->complete_transaction();
		return $this->datasource->trans_status();
	}

    //where
    protected function whereGroup($group, $parent_id = null){
        if(!isset($group) || !is_array($group)) {
            return;
        }

        $where_group_data = [
            'report_id' => $this->report_id,
            'operator' => $group['operator'],
            'parent_id' => $parent_id,
        ];

        $where_group_id = $this->datasource->add_where_group($where_group_data);
        if(isset($group['conditions']) && is_array($group['conditions'])){
            $where_condition_data = [];
            foreach($group['conditions'] as $c){
                if($c['field_id'] !== null){
                    $where_condition_data[] = [
                        'where_group_id' => $where_group_id,
                        'field_id' => (int)$c['field_id'],
                        'operator' => $c['operator'],
                        'operand' => $c['operand'],
                    ];
                }
            }
            $this->datasource->add_where_conditions($where_condition_data);
        }
        if(isset($group['conditionGroups']) && is_array($group['conditionGroups'])){
            foreach($group['conditionGroups'] as $g){
                $this->whereGroup($g, $where_group_id);
            }
        }
    }

    //add header groups
	protected function header_groups(&$headers) {
        //if headers is not set, or contains only 1 level, we have no header groups
        if(!isset($headers) || !is_array($headers) || count($headers) < 2){
            return;
        }

        $last_row_index = (count($headers) - 1);
        $header_group_map = [];
        foreach($headers as $ri => &$hr){
            $idx = 0;
            $header_group_map[$ri] = [];
            foreach($hr as $ci => &$hc){
                $hc['parent_id'] = null;
                if(isset($hc['header_text']) || !empty($hc['header_text'])){
                    if($ri > 0){ //first row cannot have a parent
                        $hc['parent_id'] = $header_group_map[($ri - 1)][$idx];
                    }

                    if($ri < $last_row_index){ //last row is column headers, don't want them in header groups
                        $header_data = [
                            'text' => $hc['header_text'],
                            'parent_id' => $hc['parent_id'],
                            'list_order' => ($ci + 1),
                        ];

                       $header_id = $this->datasource->add_header_group($header_data);
                    }
                }
                $header_group_map[$ri] = array_merge($header_group_map[$ri], array_fill($idx, $hc['colspan'], $header_id));
                $idx += $hc['colspan'];
            }
        }
        return;
    }

	//add columns
	protected function table_columns($cols){
        if(!isset($cols) || !is_array($cols)){
            return;
        }

        $column_data = [];
        $cnt = 1;
        foreach($cols as $k=>$v){
            if($v['field_id'] > 0){
                $column_data[] = [
                    'report_id' => $this->report_id,
                    'field_id' => $v['field_id'],
                    'aggregate' => isset($v['aggregate']) ? $v['aggregate'] : null,
                    'list_order' => $cnt,
                    'is_displayed' => 1,
                    'table_header_group_id' => isset($v['parent_id']) ? $v['parent_id'] : null,
                    'user_id' => $this->user_id,
                    'header_text' => $v['header_text'],
                ];

                if(isset($v['aggregate']) && !empty($v['aggregate'])){
                    $this->has_aggregates = true;
                }
                $cnt++;
            }
        }

        return $this->datasource->add_columns($column_data);
	}

	//sort by
	protected function sort_by($sort_cols){
		if(!isset($sort_cols) || !is_array($sort_cols)) {
            return;
        }
        $cnt = 1;
        $sort_data = [];
        foreach($sort_cols as $k=>$v){
             $sort_data[] = [
                'report_id' => $this->report_id,
                'field_id' => $v['field_id'],
                'sort_order' => isset($v['sort_order']) ? $v['sort_order'] : 'ASC', //if no value, default to ASC
                'list_order' => $cnt,
            ];
            $cnt++;
        }
        return $this->datasource->add_sort_by($sort_data);
	}

    //chart columns
    protected function trend_columns(){
        $arr_col_vals = $this->input['trendcolumn'];
        $arr_aggregate_vals = $this->input['trendaggregate'];
        $arr_chart_type = $this->input['trendgraph_type'];
        $arr_axis_index_vals = $this->input['trendyaxis'];
        if(isset($arr_col_vals) && is_array($arr_col_vals)){
            $arr_column_data = array();
            $cnt = 1;
            foreach($arr_col_vals as $k=>$v){
                if(strpos($k, '_') === FALSE) $field_id = $k;
                else{
                    $arr_tmp = explode('_', $k);
                    $field_id = $arr_tmp[1];
                }
                if($field_id > 0){
                    $arr_column_data[] = [
                        'report_id' => $this->report_id,
                        'field_id' => $field_id,
                        'aggregate' => (!empty($arr_aggregate_vals[$k])) ? $arr_aggregate_vals[$k] : null,
                        'chart_type_id' => (!empty($arr_chart_type[$k])) ? $arr_chart_type[$k] : null,
                        'list_order' => $cnt,
                        'is_displayed' => 1,
                        'user_id' => $this->input['user_id'],
                        'axis_index' => $arr_axis_index_vals[$k],
                        'header_text' => $v,
                    ];
                    if($arr_aggregate_vals[$k] != NULL) $this->arr_aggregate_columns[$k] = $arr_aggregate_vals[$k];
                    $this->arr_columns[$k] = $v;
                    $cnt++;
                }
            }
            return $this->datasource->add_columns($arr_column_data);
        }
        return false;
    }

    //yaxes
    protected function yaxes(){
        $arr_yaxes_vals = array_filter($this->input['yaxis_label']);
        $arr_yaxes_min = $this->input['yaxis_min'];
        $arr_yaxes_max = $this->input['yaxis_max'];
        $arr_yaxes_vals = $this->input['yaxis_label'];
        $arr_yaxes_opposite = $this->input['yaxis_opposite'];

        if(isset($arr_yaxes_vals) && is_array($arr_yaxes_vals) && !empty($arr_yaxes_vals)){
            $arr_yaxes_data = array();
            foreach($arr_yaxes_vals as $k=>$v){
                if($k >= 0){
                    $arr_yaxes_data[] = [
                        'report_id' => $this->report_id,
                        'x_or_y' => 'y',
                        //'db_field_id' => $k,
                        'text' => $v,
                        'min' => (!empty($arr_yaxes_min[$k]) || $arr_yaxes_min[$k] === 0) ? $arr_yaxes_min[$k] : null,
                        'max' => (!empty($arr_yaxes_max[$k]) || $arr_yaxes_max[$k] === 0) ? $arr_yaxes_max[$k] : null,
                        'opposite' => isset($arr_yaxes_opposite[$k]) ? 1 : 0,
                        'list_order' => $k,
                    ];
                }
            }
            return $this->datasource->add_yaxes($arr_yaxes_data);
        }
        return false;
    }

    //xaxis
    protected function xaxis(){
        $xaxis_field = $this->input['xaxis_field'];
        $xaxis_label = $this->input['xaxis_label'];
        if(empty($xaxis_field) && empty($xaxis_label)){
            return false;
        }
        $arr_xaxis_data[] = [
            'report_id' => $this->report_id,
            'x_or_y' => 'x',
            'db_field_id' => $this->input['xaxis_field'],
            'text' => $this->input['xaxis_label'],
            'data_type' => $this->input['xaxis_datatype'],
        ];
        return $this->datasource->add_xaxis($arr_xaxis_data);
    }
}
				
