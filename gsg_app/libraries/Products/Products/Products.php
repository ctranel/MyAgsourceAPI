<?php
namespace myagsource\Products\Products;

//require_once(APPPATH . 'helpers/multid_array_helper.php');
require_once(APPPATH . 'libraries/Products/iProducts.php');
require_once(APPPATH . 'libraries/Products/Products/Product.php');

use myagsource\dhi\Herd;
use myagsource\Products\iProducts;
//use myagsource\Products\Products;

/**
 * Constructs permission-and-herd-based navigation
 *
 *
 * @name Navigation
 * @author ctranel
 *
 *
 */
class Products implements iProducts{
    /**
     * $datasource
     * @var Product_model
     **/
    protected $datasource;

    /**
     * $herd
     * @var Herd
     **/
    protected $herd;

    /**
     * $arr_group_permissions
     * @var Array
     **/
    protected $arr_group_permissions;

    /**
     * $tree
     * @var Array
    protected $tree;
     **/

    function __construct(\Product_model $datasource, Herd $herd, $arr_group_permissions) {
        $this->datasource = $datasource;
        $this->herd = $herd;
        $this->arr_group_permissions = $arr_group_permissions;
    }

    /**
     * @method allProducts()
     * @return \SplObjectStorage of Product objects
     * @access public

    protected function allProducts(){
        $ret = new \SplObjectStorage();
        $tmp = $this->datasource->getAllProducts();

        if(!isset($tmp) || empty($tmp) || !is_array($tmp)){
            return $ret;
        }

        $ret = $this->datasetToObjectStorage($tmp);

        return $ret;
    }
**/
    /**
     * @method allProductsData()
     * @return array of product objects
     * @access protected
     **/
    protected function allProductsData(){
        $tmp = $this->datasource->getAllProducts();
        return $tmp;
    }

    /**
     * @method accessibleProducts()
     * @return \SplObjectStorage of Product objects
     * @access public
     **/
    public function accessibleProducts(){
        $ret = new \SplObjectStorage();
        $tmp = $this->accessibleProductsData();

        if(!isset($tmp) || empty($tmp) || !is_array($tmp)){
            return $ret;
        }

        $ret = $this->datasetToObjectStorage($tmp);

        return $ret;
    }

    /**
     * @method accessibleProductsData()
     * @return array of product data
     * @access protected
     **/
    protected function accessibleProductsData(){
        $scope = ['base'];
        $tmp = [];

        if(in_array('View All Content', $this->arr_group_permissions)){
            $tmp = $this->datasource->getAllProducts();
        }
        else{
            /*
             * subscription is different from other scopes in that it fetches content by herd data (i.e. herd output) for users that
             * have permission only for subscribed content.  All other scopes are strictly users-based
             */
            if(in_array('View Subscriptions', $this->arr_group_permissions)){
                $tmp = array_merge($tmp, $this->datasource->getSubscribedProducts($this->herd->herdCode()));
            }
            if(in_array('View Account', $this->arr_group_permissions)){
                $scope[] = 'account';
            }
            if(in_array('View Admin', $this->arr_group_permissions)){
                $scope[] = 'admin';
            }
            if(!empty($scope)){
                $tmp = array_merge($tmp, $this->datasource->getProductsByScope($scope));
            }

            $tmp = array_map("unserialize", array_unique(array_map("serialize", $tmp)));

            //usort($tmp, \sort_by_key_value_comp('list_order'));
        }
        return $tmp;
    }

    /**
     * @method inaccessibleProducts()
     * @return \SplObjectStorage of Product objects
     * @access public
     **/
    public function inaccessibleProducts(){
        $ret = new \SplObjectStorage();
        $accessible = $this->accessibleProductsData();
        $accessible_report_codes = \array_extract_value_recursive('product_code', $accessible);

        $upsell_data = $this->datasource->getUpsellProducts($accessible_report_codes);

        if(isset($upsell_data) && !empty($upsell_data) && is_array($upsell_data)){
            $ret = $this->datasetToObjectStorage($upsell_data);
        }
        return $ret;
    }

    /**
     * @method datasetToObjectStorage()
     * @return \SplObjectStorage of Product objects
     * @access protected
     **/
    protected function datasetToObjectStorage($dataset){
        $ret = new \SplObjectStorage();
        if(!isset($dataset) || empty($dataset) || !is_array($dataset)){
            return $ret;
        }
        foreach($dataset as $k => $v){
            $ret->attach(new Product($this->datasource, $v['product_code'], $v['name'], $v['description']));
        }

        return $ret;
    }
}
