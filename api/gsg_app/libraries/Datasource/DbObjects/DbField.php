<?php
namespace myagsource\Datasource\DbObjects;

require_once APPPATH . 'libraries/Datasource/iDataField.php';
require_once APPPATH . 'libraries/Datasource/iDataConversion.php';

use \myagsource\Datasource\iDataField;
use \myagsource\Datasource\iDataConversion;

/**
 * Name:  DbField
 *
 * Author: ctranel
 *
 * Created:  02-03-2015
 *
 * Description:  Metadata typically associated with data storage for data fields.
 *
 */
class DbField implements iDataField {
	/**
	 * id
	 * @var int
	 **/
	protected $id;
	
	/**
	 * db_table_name
	 * @var int
	 **/
	protected $db_table_name;
	
	/**
	 * db field name
	 * @var int
	 **/
	protected $db_field_name;
	
	/**
	 * field name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * field description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * pdf_width
	 * @var int
	 **/
	protected $pdf_width;
	
	/**
	 * default_sort_order
	 * @var string
	 **/
	protected $default_sort_order;
	
	/**
	 * datatype
	 * @var string
	 **/
	protected $datatype;
	
	/**
	 * max_length
	 * @var int
	 **/
	protected $max_length;
	
	/**
	 * decimal_scale
	 * @var int
	 **/
	protected $decimal_scale;
	
	/**
	 * unit_of_measure
	 * @var string
	 **/
	protected $unit_of_measure;
	
	/**
	 * is_timespan
	 * @var boolean
	 **/
	protected $is_timespan;
	
	/**
	 * is_foreign_key
	 * @var boolean
	 **/
	protected $is_foreign_key;
	
	/**
	 * is_nullable
	 * @var boolean
	 **/
	protected $is_nullable;
	
	/**
	 * is_natural_sort
	 * @var boolean
	 **/
	protected $is_natural_sort;

    /**
     * data_conversion
     * @var iDataConversion
     **/
    protected $data_conversion;


    /**
	 */
	function __construct($id, $db_table_name, $db_field_name, $name, $description, $pdf_width, $default_sort_order,
			$datatype, $max_length, $decimal_scale, $unit_of_measure, $is_timespan, $is_foreign_key, $is_nullable, $is_natural_sort, iDataConversion $data_conversion=null) {
		$this->id =  $id;
		$this->db_table_name = $db_table_name;
		$this->db_field_name = $db_field_name;
		$this->name = $name;
		$this->description = $description;
		$this->pdf_width = $pdf_width;
		$this->default_sort_order = $default_sort_order;
		$this->datatype = $datatype;
		$this->max_length = $max_length;
		$this->decimal_scale = $decimal_scale;
		$this->unit_of_measure = $unit_of_measure;
		$this->is_timespan = $is_timespan;
		$this->is_foreign_key = $is_foreign_key;
		$this->is_nullable = $is_nullable;
		$this->is_natural_sort = $is_natural_sort;
        $this->data_conversion = $data_conversion;
	}

	public function isKey(){
        return $this->is_foreign_key;
    }

	public function toArray(){
        $ret = [
//            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
//            'pdf_width' => $this->pdf_width,
            'default_sort_order' => $this->default_sort_order,
            'datatype' => $this->datatype,
            'max_length' => $this->max_length,
            'decimal_scale' => $this->decimal_scale,
            'unit_of_measure' => $this->unit_of_measure,
            'is_timespan' => $this->is_timespan,
            'db_field_name' => $this->db_field_name,
//            'is_nullable' => $this->is_nullable,
            'is_natural_sort' => $this->is_natural_sort,
        ];
        return $ret;
    }
	
	public function dbFieldName(){
		return $this->db_field_name;
	}

    public function fullDbFieldName(){
        if(isset($this->db_table_name) && !empty($this->db_table_name)){
            return $this->db_table_name . '.' . $this->db_field_name;
        }
        return $this->db_field_name;
    }

    public function label(){
        return $this->name;
    }

    protected function setDbFieldName($value){
		$this->db_field_name = $value;
	}

	public function dbTableName(){
		return $this->db_table_name;
	}
	
	public function unitOfMeasure(){
		return $this->unit_of_measure;
	}
	
	public function dataType(){
		return $this->datatype;
	}

	public function decimalScale(){
		return $this->decimal_scale;
	}

	public function pdfWidth(){
		return $this->pdf_width;
	}

	public function defaultSortOrder(){
		return $this->default_sort_order;
	}

	public function isNaturalSort(){
		return (bool)$this->is_natural_sort;
	}

    public function hasMetricConversion(){
        return is_a($this->data_conversion, '\myagsource\Datasource\iDataConversion');
    }

    public function conversionToMetricFactor(){
        return is_a($this->data_conversion, '\myagsource\Datasource\iDataConversion') ? $this->data_conversion->metricFactor() : 1;
    }

    public function isNumeric(){
		//@todo: database neutral
		return (strpos($this->datatype, 'int') !== false) || (strpos($this->datatype, 'money') !== false) || (strpos($this->datatype, 'decimal') !== false) || $this->datatype === 'float' || $this->datatype === 'numeric' || $this->datatype === 'real';
	}

    /**
     * @method selectFieldText()
     *
     * Returns SQL text for select statement
     *
     * @param boolean is report to be displayed as metric?
     * @param string report aggregate
     * @return string of sql select statement text for field
     * @access public
     * @todo: should this be in model?
     * */
    public function selectFieldText($is_metric, $aggregate) {
        $field_text = $this->db_table_name . "." . $this->db_field_name;

        if($is_metric && $this->hasMetricConversion()){
            $alias_field_name = $this->db_field_name;

            $field_text = "(ROUND(" . $this->conversionToMetricFactor() . "*" . $field_text . ", 1))";
        }

        if($this->datatype === "date" || $this->datatype === "smalldatetime"){//in all cases, time was irrelevent for columsn of this datatype
            return "FORMAT(" . $field_text . ",  'yyyy-MM-dd', 'en-US') AS " . $this->db_field_name;
        }
        if($this->datatype === "datetime"){
            return "FORMAT(" . $field_text . ",  'yyyy-MM-dd HH:mm:ss', 'en-US') AS " . $this->db_field_name;
        }
        if(isset($aggregate) && !empty($aggregate)){
            $alias_field_name = strtolower($aggregate) . '_' . $this->db_field_name;
            $ret_val = $aggregate . '(' . $field_text . ') AS ' . $alias_field_name;
            $this->setDbFieldName($alias_field_name);
            return $ret_val;
        }
        return isset($alias_field_name) ? $field_text . " AS " . $alias_field_name : $field_text;
    }
}

?>