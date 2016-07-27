<?php

namespace myagsource\Page\Content;

//@todo: change DBField to factory object
require_once(APPPATH . 'libraries/Datasource/DbObjects/DbField.php');
require_once(APPPATH . 'libraries/Page/iReportBlock.php');
require_once(APPPATH . 'libraries/Page/Content/Sort.php');

use \myagsource\Datasource\DbObjects\DbField;
use \myagsource\Page\iReportBlock;
use \myagsource\Page\Content\Sort;

/**
 *
 * @author ctranel
 *        
 */
class SortBuilder {
	
	protected $block_meta_datasource;
	
	/**
	 */
	function __construct($block_meta_datasource) {
		$this->block_meta_datasource = $block_meta_datasource;
	}
	
	
	public function build(iReportBlock $block, $sort_by, $sort_order){
		$sort_by = urldecode($sort_by);
		if($sort_by != 'null' && $sort_order != 'null' && !empty($sort_by) && !empty($sort_order)) {
			$arr_sort_by = explode('|', $sort_by);
			$arr_sort_order = explode('|', $sort_order);
			if(isset($arr_sort_order) && is_array($arr_sort_order)){
				$block->resetSort();
				foreach($arr_sort_order as $k=>$s){
					$f = $this->block_meta_datasource->getFieldByName($block->id(), $arr_sort_by[$k]);
					$datafield = new DbField($f['db_field_id'], $f['table_name'], $f['db_field_name'], $f['name'], $f['description'], $f['pdf_width'], $f['default_sort_order'],
						 $f['datatype'], $f['max_length'], $f['decimal_scale'], $f['unit_of_measure'], $f['is_timespan'], $f['is_foreign_key'], $f['is_nullable'], $f['is_natural_sort']);
					$block->addSort(new Sort($datafield, $arr_sort_order[$k]));
				}
			}
		}
	}
}

?>