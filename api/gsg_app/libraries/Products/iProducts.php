<?php
namespace myagsource\Products;

/**
 * Products
 *
 * Product repository.  Products correspond to internal reports.  The are mapped to web pages to determine what content a user/herd can see.
 *
 * User: ctranel
 * Date: 2/17/2016
 * Time: 11:19 AM
 */

interface iProducts {
    public function accessibleProducts();
    public function accessibleProductCodes();
    public function inaccessibleProducts();
    public function inaccessibleProductCodes();
}