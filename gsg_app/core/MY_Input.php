<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class MY_Input extends CI_Input
{
    public function __construct(){
header("Content-type: application/json"); //being sent as json
header("Cache-Control: no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: -1");
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
parent::__construct();

    }

    /**
     * Sanitize Globals
     *
     * This function does the following:
     *
     * Unsets $_GET data (if query strings are not enabled)
     *
     * Unsets all globals if register_globals is enabled
     *
     * Standardizes newline characters to \n
     *
     * @access	private
     * @return	void
     */
    function _sanitize_globals()
    {
        parent::_sanitize_globals();
        $request_headers = apache_request_headers();
        if(isset($request_headers['Content-Type']) && $request_headers['Content-Type'] === 'application/json'){
            $json_request = json_decode(file_get_contents('php://input'));

            // Clean $json_request Data
            if (is_array($json_request) AND count($json_request) > 0) {
                foreach ($json_request as $key => $val) {
                    echo "$key => $val\n";
                    $json_request[$this->_clean_input_keys($key)] = $this->_clean_input_data($val);
                }
            }
        }
    }
}
