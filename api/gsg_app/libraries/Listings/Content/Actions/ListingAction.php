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

use myagsource\Listings\iListingAction;

class ListingAction implements iListingAction
{
    /**
     * action label
     * @var string
     **/
    protected $label;

    /**
     * action url
     * @var string
     **/
    protected $url;

    public function __construct($data){
        $this->label = $data['label'];
        $this->url = $data['url'];
    }

    public function toArray(){
        return [
            'label' => $this->label,
            'url' => $this->url,
        ];
    }
}