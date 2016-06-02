<?php
namespace myagsource\Api\Response;

use myagsource\Api\iResponseMessage;

require_once(APPPATH . 'libraries/Api/iResponseMessage.php');

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

class ResponseMessage implements iResponseMessage
{
    /**
     * $text
     * @var string
     **/
    protected $text;

    /**
     * $level
     * @var string
     **/
    protected $level;

    /**
     * __construct
     *
     * @return void
     * @author ctranel
     **/
    public function __construct($text, $level) {
        $this->text = $text;
        $this->level = $level;
    }

    public function toArray(){
        return [
                'text' => $this->text,
                'level' => $this->level,
            ];
    }
}
