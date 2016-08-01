<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/28/2016
 * Time: 2:45 PM
 */

namespace myagsource\Datasource\DbObjects;

class DbTableFactory
{
    /**
     * db_table_model
     *
     * @var db_table_model
     **/
    protected $db_table_model;

    public function __construct(\db_table_model $db_table_model){
        $this->db_table_model = $db_table_model;
    }

    public function getTable($name){
        $ret = new DbTable($name, $this->db_table_model);
        return $ret;
    }
}