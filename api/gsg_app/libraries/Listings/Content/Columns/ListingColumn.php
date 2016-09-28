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
     * field_name
     * @var string
     **/
    protected $field_name;

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
        $this->field_name = $column_data['field_name'];
        //$this->description = $column_data['description'];
        //$this->value = $column_data['value'];
        $this->control_type = $column_data['control_type'];
        $this->is_displayed = $column_data['is_displayed'];
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

    public function toArray(){
        switch($this->control_type){
            case 'herd_lookup':
                $ctl_type = 'data_lookup';
                break;
            case 'herd_lookup_array':
                $ctl_type = 'data_lookup_array';
                break;
            case 'animal_lookup':
                $ctl_type = 'data_lookup';
                break;
            case 'animal_lookup_array':
                $ctl_type = 'data_lookup_array';
                break;
            default:
                $ctl_type = $this->control_type;
                break;
        }

        $ret = [
            'label' => $this->label,
            'field_name' => $this->field_name,
            //'description' => $this->description,
            //'value' => $this->value,
            'control_type' => $ctl_type,
            'is_displayed' => $this->is_displayed,
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
        elseif(strpos($this->control_type, 'array') !== false){
            return str_replace('|', ', ', $value);
        }
        else{
            return $value;
        }
    }
}