<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 9/27/2016
 * Time: 9:44 AM
 */

namespace myagsource\Listings;

require_once(APPPATH . 'libraries/Site/iBlockContent.php');

use myagsource\Site\iBlockContent;

interface iListing extends iBlockContent
{
    public function toArray();
}