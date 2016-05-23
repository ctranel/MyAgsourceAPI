<?php
namespace myagsource\Api\Response;

use myagsource\Api\iResponse;

require_once(APPPATH . 'libraries/Api/iResponse.php');

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
    protected $http_status;

    /**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct() {
	}

    public function errorInternal($msg = null){
        $this->http_status = 500;
        if($msg){
            return $this->errorResponse($msg);
        }
        return $this->errorResponse('Internal Server Error');
    }

    public function errorForbidden($msg = null){
        $this->http_status = 403;
        if($msg){
            return $this->errorResponse($msg);
        }
        return $this->errorResponse('Content Is Forbidden');
    }

    public function errorNotFound($msg = null){
        $this->http_status = 404;
        if($msg){
            return $this->errorResponse($msg);
        }
        return $this->errorResponse('Content Not Found');
    }

    public function errorUnauthorized($msg = null){
        $this->http_status = 401;
        if($msg){
            return $this->errorResponse($msg);
        }
        return $this->errorResponse('Unauthorized');
    }

    public function errorBadRequest($msg = null){
        $this->http_status = 400;
        if($msg){
            return $this->errorResponse($msg);
        }
        return $this->errorResponse('Request Not Understood');
    }
    
    public function message($msg = null){
        return [
            'message' => [
                'text' => $msg,
            ],
        ];
    }

    public function redirect($uri){
        return [
            'redirect' => [
                'uri' => $uri,
            ],
        ];
    }

    protected function errorResponse($msg){
        return [
            'error' => [
                'http_status' => $this->http_status,
                'message' => $msg,
            ],
        ];
    }
}
