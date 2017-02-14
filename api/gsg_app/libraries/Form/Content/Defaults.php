<?php
namespace myagsource\Form\Content;

/**
 * Defaults
 * 
 * Collection of functions that return default values for various misc form entities
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 2017-02-13
 */


class Defaults
{
    /**
     * datasource
     * @var object
     **/
    protected $datasource;

    public function __construct($datasource){
        $this->datasource = $datasource;
    }

    public function etSire($herd_code, $sire_id){
        $ret = $this->datasource->getETSireDefaultValues($herd_code, $sire_id);
        return $ret;
    }

    public function etDonor($herd_code, $animal_id){
        $ret = $this->datasource->getETDonorDefaultValues($herd_code, $animal_id);
        return $ret;
    }

}