<?php
namespace myagsource;

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
     * header group map
     * @var array
     **/
    protected $header_group_map = [];
    /**
     * does the report contain aggregated columns?
     * @var boolean
     **/
	protected $has_aggregates = FALSE;


	/*
	protected $arr_columns = array(); //key=field_id, value=field_id
	protected $arr_aggregate_columns = array(); //key=field_id, value=header_text
	
	protected $arr_header_groups = array(); //key=field_id, value=header_group_text
	protected $arr_header_groups_data = array();
	*/

	public function __construct(\custom_report_model $datasource, $report_id, $user_id=null)
	{
        $this->datasource = $datasource;
        $this->report_id = $report_id;
        $this->user_id = $user_id;

/*		$this->load->config('ion_auth', TRUE);
		$this->load->library('email');
		$this->load->library('session');
		$this->lang->load('ion_auth');
		$this->load->helper('cookie'); */
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
		$this->datasource->start_transaction();
/*
		$arr_block_data = [
			'user_id' => $this->input['user_id']
			,'name' => $this->input['report_name']
			,'description' => $this->input['report_description']
			,'path' => preg_replace("/\W|/", '', strtolower(str_replace(' ', '_', $this->input['report_name'])))
			,'display_type_id' => $this->input['report_display_id']
			,'scope_id' => 2
        ];

		$this->block_id = $this->datasource->create_block($arr_block_data);
//		if(!$this->block_id) $this->cancel_add();
		$bool_block_on_page = $this->datasource->add_block_to_page(['block_id' => $this->block_id, 'page_id' => $this->input['page_id'], 'list_order' => $this->input['insert_after']]);
//		if(!$bool_block_on_page) $this->cancel_add();
		//table displays
        $arr_report_data = [
            'block_id' => $this->block_id
            ,'chart_type_id' => isset($this->input['chart_type_id']) && !empty($this->input['chart_type_id']) ? $this->input['chart_type_id'] : null
            ,'max_rows' => isset($this->input['max_rows']) && !empty($this->input['chart_type_id']) ? $this->input['max_rows'] : null
            ,'cnt_row' => isset($this->input['cnt_row']) ? $this->input['cnt_row'] : false
            ,'sum_row' => isset($this->input['sum_row']) ? $this->input['sum_row'] : false
            ,'avg_row' => isset($this->input['avg_row']) ? $this->input['avg_row'] : false
            ,'bench_row' => isset($this->input['bench_row']) ? $this->input['bench_row'] : false
            ,'pivot_db_field' => isset($this->input['pivot_db_field']) && !empty($this->input['pivot_db_field']) ? $this->input['pivot_db_field'] : null
            ,'is_summary' => $this->input['cow_or_summary'] === 'summary'
            ,'keep_nulls' => 1
        ];
        $this->report_id = $this->datasource->create_report($arr_report_data);
*/
		//tables
        if($input['display_type_id'] == 1 || $input['display_type_id'] == 3){
		    if(isset($this->input['pivot_db_field']) && !empty($this->input['pivot_db_field'])){
		        $this->updateReport(['pivot_db_field' => $input['pivot_db_field']]);
            }
echo "start <br>";
            $header_group_map = $this->header_groups($input['table_header']);
echo "header_groups <br>";
			$this->table_columns($input['columns'], $header_group_map);
echo "table_columns <br>";
            $this->where($input['where']);
echo "where <br>";
			$this->group_by($input['columns']);
echo "group_by <br>";
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
echo "group_by <br>";
			$this->group_by();
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
    protected function where($where, $parent_id = null){
        if(!isset($where) || !is_array($where)) {
            return;
        }

        foreach($where as $v){
            $where_condition_data = [];
            $where_group_data = [
                'report_id' => $this->report_id,
                'operator' => $v['operator'],
                'parent_id' => $parent_id,
            ];
            $where_group_id = $this->datasource->add_where_group($where_group_data);
            if(isset($v['conditions']) && is_array($v['conditions'])){
                foreach($v['conditions'] as $c){
                    $where_condition_data[] = [
                        'where_group_id' => $where_group_id,
                        'field_id' => $c['field_id'],
                        'condition' => $c['operator'] . ' ' . $c['operand'],
                    ];
                    $this->datasource->add_where_conditions($where_condition_data);
                }
            }
            if(isset($v['groups']) && is_array($v['groups'])){
                foreach($v['groups'] as $g){
                    $this->where($g, $where_group_id);
                }
            }
        }
    }

    //add header groups
	protected function header_groups($headers, $parent_id = null) {
        if(!isset($headers) || !is_array($headers)){
            return;
        }

        foreach($headers as $h){
            $header_data = [
                'text' => $h['text'],
                'index' => $h['index'],
            ];
            $header_id = $this->datasource->add_header_group($header_data);

            $this->header_group_map[$h['index']] = $header_id;

            if(isset($h['children']) && is_array($h['children'])){
                $this->header_groups($h['children'], $header_id);
            }
        }
/*
		$arr_header_groups = $this->input['head_group'];
		$this->arr_header_groups_data = array();
		$arr_head_group_parent_index = $this->input['head_group_parent_index'];
		$arr_header_keys = array();
		if(isset($arr_header_groups) && is_array($arr_header_groups)) $this->add_header_group($arr_header_groups, $this->arr_header_groups_data, $arr_head_group_parent_index);
*/
    }
	
	//add columns
	protected function table_columns($cols, $header_group_map){
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
                    'aggregate' => $v['aggregate'],
                    'list_order' => $cnt,
                    'is_displayed' => 1,
                    'table_header_group_id' => $header_group_map[$v['header_group_id']],
                    'user_id' => $this->user_id,
                    'header_text' => $v['header'],
                ];

                if(isset($v['aggregate']) && !empty($v['aggregate'])){
                    $this->has_aggregates = true;
                }
                $cnt++;
            }
        }
        return $this->datasource->add_columns($column_data);
	}
	
	//group by
	protected function group_by($cols){
        if($this->has_aggregates === false || !isset($cols) || !is_array($cols)){
            return;
        }
	    $group_by_data = [];
        $cnt = 1;
        foreach($cols as $k=>$v) {
            if (empty($v['aggregate'])) {
                $group_by_data[] = [
                    'report_id' => $this->report_id,
                    'field_id' => $v['field_id'],
                    'list_order' => $cnt,
                ];
                $cnt++;
            }
        }
        return $this->datasource->add_group_by($group_by_data);
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
                'report_id' => $this->report_id
                ,'field_id' => $v['field_id']
                ,'sort_order' => isset($v['sort_order']) ? $v['sort_order'] : 'ASC' //if no value, default to ASC
                ,'list_order' => $cnt
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
                    $arr_column_data[] = array(
                        'report_id' => $this->report_id
                    ,'field_id' => $field_id
                    ,'aggregate' => (!empty($arr_aggregate_vals[$k])) ? $arr_aggregate_vals[$k] : null
                    ,'chart_type_id' => (!empty($arr_chart_type[$k])) ? $arr_chart_type[$k] : null
                    ,'list_order' => $cnt
                    ,'is_displayed' => 1 //bit field, yes or no
                    ,'user_id' => $this->input['user_id']
                    ,'axis_index' => $arr_axis_index_vals[$k]
                    ,'header_text' => $v
                    );
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
                    $arr_yaxes_data[] = array(
                        'report_id' => $this->report_id
                    ,'x_or_y' => 'y'
                        //,'db_field_id' => $k
                    ,'text' => $v
                    ,'min' => (!empty($arr_yaxes_min[$k]) || $arr_yaxes_min[$k] === 0) ? $arr_yaxes_min[$k] : null
                    ,'max' => (!empty($arr_yaxes_max[$k]) || $arr_yaxes_max[$k] === 0) ? $arr_yaxes_max[$k] : null
                    ,'opposite' => isset($arr_yaxes_opposite[$k]) ? 1 : 0
                    ,'list_order' => "$k"
                    );
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
        $arr_xaxis_data[] = array(
            'report_id' => $this->report_id
        ,'x_or_y' => 'x'
        ,'db_field_id' => $this->input['xaxis_field']
        ,'text' => $this->input['xaxis_label']
        ,'data_type' => $this->input['xaxis_datatype']
        );
        return $this->datasource->add_xaxis($arr_xaxis_data);
    }

    /* HELPER FUNCTIONS
	protected function add_header_group($arr_header_groups, &$new_arr_header_groups, $arr_head_group_parent_index, $parent_key = NULL){
		foreach($arr_header_groups as $k => $v){
			if(is_array($v)){
				$key_param = ($parent_key) ? $parent_key . '-' . $k : $k;
				$this->add_header_group($v, $new_arr_header_groups, $arr_head_group_parent_index, $key_param);
			}
			else {
				if($v != 'Enter text here to add a header grouping' && $v != '') {
					$parent_array_index = isset($arr_head_group_parent_index[$parent_key]) ? $arr_head_group_parent_index[$parent_key][$k] : NULL;
					$parent_id = isset($new_arr_header_groups[($parent_key - 1)]) ? $new_arr_header_groups[($parent_key - 1)][$parent_array_index]['id'] : NULL;
					$arr_header_group_data = array(
						'parent_id' => $parent_id,
						'text' => $v,
						'list_order' => $k
					);
					$arr_header_group_data['id'] = $this->datasource->add_header_group($arr_header_group_data);
					if($parent_key) $new_arr_header_groups[$parent_key][$k] = $arr_header_group_data;
					else $new_arr_header_groups[$k] = $arr_header_group_data;
				}
			}
		}
	}
*/
}
				
