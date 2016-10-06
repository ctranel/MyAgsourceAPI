<?php
namespace myagsource\Listings\Content;

/**
 * Listing
 * 
 * Object representing individual listing
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 11:23 AM
 */

require_once APPPATH . 'libraries/Listings/iListing.php';

use myagsource\Listings\Content\Columns\ListingColumn;
use \myagsource\Listings\iListing;

class Listing implements iListing
{
    /**
     * id
     * @var int
     **/
    protected $id;

    /**
     * isactive
     * @var boolean
     **/
    protected $isactive;

    /**
     * add_presets
     * @var array of
     **/
    protected $add_presets;

    /**
     * dataset
     * @var array
     **/
    protected $dataset;

    /**
     * array of control objects
     * @var ListingColumn[]
     **/
    protected $columns;

    /**
     * form_id
     * @var int
     **/
    protected $form_id;

    public function __construct($id, $form_id, $columns, $dataset, $isactive, $add_presets){
        $this->id = $id;
        $this->form_id = $form_id;
        $this->isactive = $isactive;
        $this->add_presets = $add_presets;
        $this->dataset = $dataset;
        $this->columns = $columns;
    }

    public function toArray(){
        $ret['isactive'] = $this->isactive;
        $ret['form_id'] = $this->form_id;
        $ret['add_presets'] = $this->add_presets;

        if(isset($this->columns) && is_array($this->columns) && !empty($this->columns)){
            $columns = [];
            foreach($this->columns as $c){
                $columns[] = $c->toArray();
            }
            $ret['columns'] = $columns;
            unset($columns);
        }

        if(isset($this->dataset) && is_array($this->dataset) && !empty($this->dataset)) {
            $ds = [];
            foreach($this->dataset as $r) {
                $row = [];
                foreach ($r as $k => $v) {
                    if(isset($this->columns[$k])){
                        $row[$k] = $this->columns[$k]->getDisplayText($v);
                    }
                }
                $ds[] = $row;
            }
            $ret['dataset'] = $ds;
            unset($ds, $row);
        }
        return $ret;
    }
}