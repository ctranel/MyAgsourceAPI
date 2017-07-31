<?php
namespace myagsource\Listings\Content\Actions;

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

require_once APPPATH . 'libraries/Listings/iListingAction.php';
require_once APPPATH . 'libraries/Listings/iListingActions.php';

use \myagsource\Listings\iListingActions;

class ListingActions implements iListingActions
{
    /**
     * array of action objects
     * @var myagsource\Listings\iListingAction[]
     **/
    protected $actions;

    public function __construct($actions){
        $this->actions = $actions;
    }

    public function toArray(){
        $ret = [];

        if(isset($this->actions) && is_array($this->actions) && !empty($this->actions)){
            foreach($this->actions as $c){
                $ret[] = $c->toArray();
            }
        }
        return $ret;
    }
}