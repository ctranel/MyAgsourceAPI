<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 3/31/2016
 */
require_once APPPATH . 'libraries/dhi/Herd.php';
require_once APPPATH . 'libraries/Ion_auth.php';
require_once APPPATH . 'libraries/Settings/Settings.php';
require_once APPPATH . 'libraries/Api/Response/Response.php';
require_once(APPPATH . 'libraries/Api/Response/ResponseMessage.php');
require_once APPPATH . 'libraries/AccessLog.php';
//require_once APPPATH . 'libraries/Site/WebContent/SectionFactory.php';
//require_once APPPATH . 'libraries/Site/WebContent/PageFactory.php';
//require_once APPPATH . 'libraries/Site/WebContent/WebBlockFactory.php';
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/as_ion_auth.php');
require_once(APPPATH . 'libraries/Products/Products/Products.php');
require_once(APPPATH . 'libraries/Permissions/Permissions/ProgramPermissions.php');

use \myagsource\AccessLog;
//use \myagsource\Site\WebContent\SectionFactory;
use \myagsource\Settings\Settings;
use \myagsource\Api\Response\Response;
//use \myagsource\Site\WebContent\PageFactory;
//use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;
use \myagsource\as_ion_auth;
use \myagsource\Products\Products\Products;
use \myagsource\Permissions\Permissions\ProgramPermissions;



class MY_Api_Controller extends CI_Controller
{
    /**
     * herd
     *
     * Herd object
     * @var Herd
     **/
    protected $herd;

    /**
     * settings
     *
     * Herd object
     * @var Herd
     **/
    protected $settings;

    /**
     * herd_access
     *
     * @var HerdAccess
     **/
    protected $herd_access;

    public function __construct() {
        // Execute CI_Controller Constructor
        parent::__construct();
        $this->load->library('session');

        header("Content-type: application/json"); //being sent as json
        header("Cache-Control: no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        header("Expires: -1");
        header('Access-Control-Allow-Origin: https://localhost:3000');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Strict-Transport-Security: max-age=31536000; includeSubdomains');
        $this->session->sess_write();


        //if OPTIONS request, header is all we need (pre-flight)
        if($_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
            die();
        }

        $this->load->library('carabiner');
        $this->load->model('Settings/settings_model', null, false, ['user-id' => $this->session->userdata('user_id'), 'herd_code' => $this->session->userdata('herd_code')]);
        $this->load->model('web_content/section_model');
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $this->load->model('dhi/region_model');
        $this->load->model('dhi/herd_model');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('error');
        $this->herd_access = new HerdAccess($this->herd_model);

        $this->settings = new Settings($this->session->userdata('user_id'), $this->session->userdata('herd_code'), $this->settings_model);

        if($this->session->userdata('herd_code')){
            $this->herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));
        }

        if($this->session->userdata('active_group_id')) {
            $this->load->model('permissions_model');
            $this->load->model('product_model');
            $group_permissions = ProgramPermissions::getGroupPermissionsList($this->permissions_model, $this->session->userdata('active_group_id'));
            $products = new Products($this->product_model, $this->herd, $group_permissions);
            $this->permissions = new ProgramPermissions($this->permissions_model, $group_permissions, $products->allHerdProductCodes());
        }
        else{
            $this->permissions = null;
        }
        $this->as_ion_auth = new as_ion_auth($this->permissions, $this->session);
    }

    
    protected function sendResponse($http_code, $messages = null, $payload = null){
        $response = new Response();
        http_response_code($http_code);
        if(isset($messages) && !is_array($messages)){
            $messages = [$messages];
        }

        switch($http_code){
            case 500:
                echo json_encode(['messages'=>$response->errorInternal($messages)]);
                break;
            case 403:
                echo json_encode(['messages'=>$response->errorForbidden($messages)]);
                break;
            case 404:
                echo json_encode(['messages'=>$response->errorNotFound($messages)]);
                break;
            case 401:
                echo json_encode(['messages'=>$response->errorUnauthorized($messages)]);
                break;
            case 400:
                echo json_encode(['messages'=>$response->errorBadRequest($messages)]);
                break;
            case 200:
                $message = $response->message($messages);

                if(is_array($message) && is_array($payload)){
                    echo json_encode(array_merge(['messages'=>$message], $payload));
                    break;
                }
                if(is_array($message)){
                    echo json_encode(['messages'=>$message]);
                    break;
                }
                echo json_encode($payload);
                break;
        }
        exit;
    }
}