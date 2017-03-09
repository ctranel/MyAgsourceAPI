<?php
namespace myagsource\Listings;

/**
 * iListingColumn
 * 
 * Created by PhpStorm.
 * User: ctranel
 * Date: 9/27/2016
 * Time: 11:31 AM
 */


interface iListingColumn
{
    public function id();
    public function toArray();
    public function isKey();
    public function getDisplayText($value);
}