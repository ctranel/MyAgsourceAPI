<?php
namespace myagsource\Api\Response;

require_once(APPPATH . 'libraries/Api/iResponse.php');
require_once(APPPATH . 'libraries/Api/Response/ResponseMessage.php');

use myagsource\Api\iResponse;
use myagsource\Api\iResponseMessage;

/**
 * Name:  Api
 *
 * Author: ctranel
 *		  ctranel@agsource.com
 *
 * Created:  05/10/2016
 *
 * Description:  Library for managing herd data
 *
 * Requirements: PHP5.4 or above
 *
 */

class Response implements iResponse
{
    /**
     * $http_status
     * @var int
     **/
    //protected $http_status;

    /**
     * __construct
     *
     * @return void
     * @author ctranel
     **/
    public function __construct() {
    }

    public function errorInternal($msg = null){
        //$this->http_status = 500;
        if($msg){
            return $this->message($msg);
        }
        return $this->message(new ResponseMessage('Internal Server Error', 'error'));
    }

    public function errorForbidden($msg = null){
        //$this->http_status = 403;
        if($msg){
            return $this->message($msg);
        }
        return $this->message(new ResponseMessage('Content Is Forbidden', 'error'));
    }

    public function errorNotFound($msg = null){
        //$this->http_status = 404;
        if($msg){
            return $this->message($msg);
        }
        return $this->message(new ResponseMessage('Content Not Found', 'error'));
    }

    public function errorUnauthorized($msg = null){
        //$this->http_status = 401;
        if($msg){
            return $this->message($msg);
        }
        return $this->message(new ResponseMessage('Unauthorized', 'error'));
    }

    public function errorBadRequest($msg = null){
        //$this->http_status = 400;
        if($msg){
            return $this->message($msg);
        }
        return $this->message(new ResponseMessage('Request Not Understood', 'error'));
    }

    /*
     * message
     *
     * @param array of ResponseMessage objects
     * @return multi-dimensional array representing an array of Error data
     *
     */
    public function message($msgs = null){
        $ret = [];
        if(is_array($msgs)){
            foreach($msgs as $m){
                if(is_a($m, 'myagsource\Api\iResponseMessage')){
                    $ret[] = $m->toArray();
                }
                else {
                    var_dump($m);
                    throw new \UnexpectedValueException('Expected array of type iResponseMessage');
                }
            }
        }
        return $ret;
    }

    public function redirect($uri){
        return [
            'redirect' => [
                'uri' => $uri,
            ],
        ];
    }

    /*
     * errorResponse
     *
     * @param array of ResponseMessage objects
     * @return multi-dimensional array representing an array of Error data
     *
    protected function errorResponse($msgs){
        $ret = [];
        foreach($msgs as $m){
            $ret[] = $m->toArray();
        }
        return $ret;
    }
    */
}
