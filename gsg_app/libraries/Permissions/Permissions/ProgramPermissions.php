<?php
namespace myagsource\Permissions\Permissions;

require_once(APPPATH . 'libraries/Permissions/iPermissions.php');

//use myagsource\dhi\Herd;
//use myagsource\Products\Products;
use myagsource\Permissions\iPermissions;
use myagsource\Products\Products\Products;

/**
 * ProgramPermissions
 *
 * Manages permissions for programm
 *
 *
 * @name Navigation
 * @author ctranel
 *
 *
 */
class ProgramPermissions implements iPermissions{
    /**
     * $datasource
     * @var Permissions_model
     **/
    protected $datasource;

    /**
     * $group_id
     * @var id
    protected $group_id;
**/

    /**
     * $permissions_list
     * @var Array
     **/
    protected $permissions_list;

    /**
     * $products
     * @var Products
    protected $products;
**/

    function __construct(\Permissions_model $datasource, $group_permissions_list, $accessible_product_codes) {
        $this->datasource = $datasource;
        //$this->group_id = $group_id;
        //$this->products = $products;

        $rep = $this->getProductPermissionsList($accessible_product_codes);
        $this->permissions_list = array_unique(array_merge($group_permissions_list, $rep));
    }

    /**
     * @method getGroupPermissionsList()
     * @param int group id
     * @return array of string permission names
     * @access protected
     **/
    public static function getGroupPermissionsList($datasource, $group_id){
        $tmp = $datasource->getGroupPermissionsData($group_id);
        $tmp = array_column($tmp, 'name');
        return $tmp;
    }

    /**
     * @method getProductPermissionsList()
     * @param array of product report codes
     * @return array of string permission task names
     * @access protected
     **/
    protected function getProductPermissionsList($accessible_product_codes){
        if(empty($accessible_product_codes)){
            return [];
        }
        
        $tmp = $this->datasource->getProductPermissionsData($accessible_product_codes);
        $tmp = array_column($tmp, 'name');
        return $tmp;
    }

    /**
     * @method permissionsList()
     * @return array of string permission names
     * @access public
     **/
    public function permissionsList(){
        return $this->permissions_list;
    }

    /**
     * @method hasPermission()
     * @param string permission task name
     * @return boolean
     * @access public
     **/
    public function hasPermission($task_name){
        return in_array($task_name, $this->permissions_list);
    }

}
