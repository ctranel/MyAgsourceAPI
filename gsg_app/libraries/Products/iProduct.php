<?php
namespace myagsource\Products;
/**
 * Product
 *
 * Products correspond to internal reports.  The are mapped to web pages to determine what content a user/herd can see.
 *
 * @author: ctranel
 * @created: 2/17/2016
 */

interface iProduct{
    public function productCode();
    public function name();
    public function description();
    //public function isSubscribed();
    //public function subscribe();
    //public function unsubscribe();
}