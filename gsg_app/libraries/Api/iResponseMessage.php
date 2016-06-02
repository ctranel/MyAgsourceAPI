<?php
namespace myagsource\Api;

/**
 * API Interface
 *
 * Product repository.  Products correspond to internal reports.  The are mapped to web pages to determine what content a user/herd can see.
 *
 * User: ctranel
 * Date: 05/10/2016
 */

interface iResponseMessage {
    public function toArray();
}