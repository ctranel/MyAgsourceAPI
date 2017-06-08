<?php
namespace myagsource\Listings\Content\Columns;

require_once APPPATH . 'libraries/Listings/iListingColumn.php';

use \myagsource\Listings\iListingColumn;

/**
 * ListingColumn
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 9/27/2016
 * Time: 11:31 AM
 */


class ListingColumn implements iListingColumn
{
    /**
     * id
     * @var int
     **/
    protected $id;

    /**
     * label
     * @var string
     **/
    protected $label;

    /**
     * name
     * @var string
     **/
    protected $name;

    /**
     * value
     * @var string
     **/
    //protected $value;

    /**
     * description
     * @var string
     **/
    //protected $description;

    /**
     * isactive
     * @var boolean
     **/
    //protected $isactive;

    /**
     * is_displayed
     * @var boolean
     **/
    protected $is_displayed;

    /**
     * display_format
     * @var string
     **/
    protected $display_format;

    /**
     * is_key
     * @var boolean
     **/
    protected $is_key;

    /**
     * control_type
     * @var string (can handle date, datetime, string, int, decimal)
     **/
    protected $control_type;
    /**
     * datasource
     * @var CI_Model
     **/
    //protected $datasource;

    //@todo: implement validators
    public function __construct($column_data){
        //$this->datasource = $datasource;
        $this->id = $column_data['id'];
        $this->label = $column_data['label'];
        $this->name = $column_data['name'];
        //$this->description = $column_data['description'];
        $this->default_value = $column_data['default_value'];
        $this->control_type = $column_data['control_type'];
        $this->is_displayed = $column_data['is_displayed'];
        $this->display_format = $column_data['display_format'];
        $this->is_key = $column_data['is_key'];
        //$this->isactive = $column_data['isactive'];
    }

    /* -----------------------------------------------------------------
    *  Returns control ID

    *  Returns control ID

    *  @author: ctranel
    *  @return int
    *  @throws: 
    * -----------------------------------------------------------------
    */
    public function id(){
        return $this->id;
    }

    public function isKey(){
        return $this->is_key;
    }

    public function toArray(){
        $ret = [
            'label' => $this->label,
            'name' => $this->name,
            //'description' => $this->description,
            'default_value' => $this->default_value,
            'control_type' => $this->control_type,
            'is_displayed' => $this->is_displayed,
            'display_format' => $this->display_format,
            'is_key' => $this->is_key,
        ];

       return $ret;
    }

    /* -----------------------------------------------------------------
     *  Return text used for display
    
    *  Return text used for display
    
    *  @since: version 1
    *  @author: ctranel
    *  @date: Jun 26, 2014
    *  @param: string display text for setting
    *  @return array of key=>value pairs
    *  @throws:
    * -----------------------------------------------------------------
    */
    public function getDisplayText($value){
        if($this->control_type === 'range'){
            if(strpos($value, '|') !== false){
                $tmp = [];
                list($tmp['from'], $tmp['to']) = explode('|', $value);
                return 'between ' . $tmp['from'] . ' and ' . $tmp['to'];
            }
            else{
                $value = null;
            }
        }
        //prep arrays for display
        elseif(strpos($this->control_type, 'array') !== false){
            return str_replace('|', ', ', $value);
        }
        else{
            return $value;
        }
    }
}