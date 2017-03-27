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

    public function siteByPen($herd_code, $pen_num){
        $ret = $this->datasource->getSiteByPen($herd_code, $pen_num);
        return $ret;
    }

    public function herdTreatment($herd_rxtx_id){
        $ret = $this->datasource->getHerdTreatmentValues($herd_rxtx_id);
        return $ret;
    }

    public function herdEventTreatment($herd_rxtx_id){
        $ret = $this->datasource->getHerdEventTreatmentValues($herd_rxtx_id);
        return $ret;
    }

    public function etSire($herd_code, $sire_id){
        $ret = $this->datasource->getETSireDefaultValues($herd_code, $sire_id);
        return $ret;
    }

    public function etDonor($herd_code, $animal_id){
        $ret = $this->datasource->getETDonorDefaultValues($herd_code, $animal_id);
        return $ret;
    }

    public function etSireIDData($bull_id){
        $ret = $this->datasource->getETSireIDDefaultValues($bull_id);
        return $ret;
    }

    public function etSireNAABData($naab){
        $ret = $this->datasource->getETSireNAABDefaultValues($naab);
        return $ret;
    }

    public function sireIDData($bull_id){
        $ret = $this->datasource->getSireIDDefaultValues($bull_id);
        return $ret;
    }

    public function sireNAABData($naab){
        $ret = $this->datasource->getSireNAABDefaultValues($naab);
        return $ret;
    }

    public function animalSireIDData($bull_id){
        $ret = $this->datasource->getAnimalSireIDDefaultValues($bull_id);
        return $ret;
    }

    public function animalSireNAABData($naab){
        $ret = $this->datasource->getAnimalSireNAABDefaultValues($naab);
        return $ret;
    }
}