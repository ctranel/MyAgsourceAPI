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

interface iResponse {
    public function errorInternal($msg = null);
    public function errorForbidden($msg = null);
    public function errorNotFound($msg = null);
    public function errorUnauthorized($msg = null);
    public function errorBadRequest($msg = null);
    public function message($msg = null);
    public function redirect($uri);
}