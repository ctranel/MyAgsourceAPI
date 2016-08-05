<?php

namespace myagsource\Report\Content\Table;

require_once APPPATH . 'libraries/Report/Content/ReportField.php';
require_once APPPATH . 'libraries/Datasource/DbObjects/DbField.php';

use \myagsource\Report\Content\ReportField;
use \myagsource\Datasource\DbObjects\DbField;

/**
 * Name:  TableField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Contains properties and methods specific to displaying table fields of the website.
 *
 */
class TableField extends ReportField {
	/**
	 * header_group_id
	 * @var int
	 **/
	protected $header_group_id;

	/**
	 * a_title
	 * @var string
	protected $a_title;
	 **/

	/**
	 * a_rel
	 * @var string
	protected $a_rel;
	 **/

	/**
	 * a_class
	 * @var string
	protected $a_class;
	 **/

	/**
	 */
	function __construct($id, $name, DbField $data_field, $category_id, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp, $header_group_id, $field_group = null, $field_group_ref_key = null) {
		parent::__construct($id, $name, $data_field, $category_id, $is_displayed, $display_format, $aggregate, $is_sortable, $header_supp, $data_supp, $field_group, $field_group_ref_key);
		$this->header_group_id = $header_group_id;
	}
	
	public function headerGroupId(){
		return $this->header_group_id;
	}
	
	public function getDataType(){
	    return $this->data_field->datatype();
	}

}



?>