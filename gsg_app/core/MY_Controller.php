<?php
/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 3/31/2016
 */
require_once APPPATH . 'libraries/dhi/Herd.php';
require_once APPPATH . 'libraries/Ion_auth.php';
require_once APPPATH . 'libraries/AccessLog.php';
require_once APPPATH . 'libraries/Site/WebContent/Sections.php';
require_once APPPATH . 'libraries/Site/WebContent/Pages.php';
require_once APPPATH . 'libraries/Site/WebContent/Blocks.php';
require_once(APPPATH . 'libraries/dhi/HerdAccess.php');
require_once(APPPATH . 'libraries/as_ion_auth.php');
require_once(APPPATH . 'libraries/Products/Products/Products.php');
require_once(APPPATH . 'libraries/Permissions/Permissions/ProgramPermissions.php');

use \myagsource\AccessLog;
use \myagsource\Site\WebContent\Sections;
use \myagsource\Site\WebContent\Pages;
use \myagsource\Site\WebContent\Blocks;
use \myagsource\dhi\Herd;
use \myagsource\dhi\HerdAccess;
use \myagsource\as_ion_auth;
use \myagsource\Products\Products\Products;
use \myagsource\Permissions\Permissions\ProgramPermissions;



class MY_Controller extends CI_Controller
{
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
        $this->load->library('carabiner');
        $this->load->model('web_content/section_model');
        $this->load->model('web_content/page_model', null, false, $this->session->userdata('user_id'));
        $this->load->model('web_content/block_model');
        $this->load->model('dhi/region_model');
        $this->load->model('dhi/herd_model');
        $this->load->helper('url');
        $this->load->helper('form');
        $this->load->helper('html');
        $this->load->helper('error');
        $this->herd_access = new HerdAccess($this->herd_model);
        $blocks = new Blocks($this->block_model);
        $pages = new Pages($this->page_model, $blocks);
        //$sections = new Sections($this->section_model, $pages);
        $herd = new Herd($this->herd_model, $this->session->userdata('herd_code'));

        if($this->session->userdata('active_group_id')) {
            $this->load->model('permissions_model');
            $this->load->model('product_model');
            $group_permissions = ProgramPermissions::getGroupPermissionsList($this->permissions_model, $this->session->userdata('active_group_id'));
            $products = new Products($this->product_model, $herd, $group_permissions);
            $this->permissions = new ProgramPermissions($this->permissions_model, $group_permissions, $products->accessibleProductCodes());
        }
        else{
            $this->permissions = null;
        }
        $this->as_ion_auth = new as_ion_auth($this->permissions);
        
        //$tmp_uri= $this->uri->uri_string();
    }

    //redirects while retaining message and conditionally setting redirect url
    //@todo: needs to be a part of some kind of authorization class?
    protected function redirect($url, $message = ''){
        if(!is_array($message) && !empty($message)){
            $message = [$message];
        }
        if($this->session->flashdata('message')){
            $message = $message + $this->session->flashdata('message');
            $this->session->set_flashdata('message', $message);
        }
        elseif(isset($message) && !empty($message)){
            $this->session->set_flashdata('message', $message);
        }
        redirect($url);
    }


}