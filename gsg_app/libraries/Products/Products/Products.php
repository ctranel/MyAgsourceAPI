<?php
namespace myagsource\Products\Products;

require_once(APPPATH . 'libraries/Products/iProducts.php');
require_once(APPPATH . 'libraries/Products/Products/Product.php');

use myagsource\dhi\Herd;
use myagsource\Products\iProducts;
//use myagsource\Products\Products;

/**
 * Constructs permission-and-herd-based navigation
 *
 * @todo: has evolved into more of a Product Access class than Products
 *
 * @name Products
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
     * accessible_products
     * @var Array or Product objects
     **/
    protected $accessible_products;

    /**
     * inaccessible_products
     * @var Array or Product objects
     **/
    protected $inaccessible_products;

    /**
     * accessible_products
     * @var Array or strings
     **/
    protected $accessible_product_codes;

    /**
     * inaccessible_products
     * @var Array or strings
     **/
    protected $inaccessible_product_codes;

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
     * @method allProductsData()
     * @return array of raw product data
     * @access protected
     **/
    protected function allProductsData(){
        $tmp = $this->datasource->getAllProducts();
        return $tmp;
    }

    /**
     * @method setAccessibleProducts()
     * @return void
     * @access protected
     **/
    protected function setAccessibleProducts(){
        $ret = [];
        $tmp = $this->accessibleProductsData();

        if(!isset($tmp) || empty($tmp) || !is_array($tmp)){
            return $ret;
        }

        $this->accessible_product_codes = array_column($tmp, 'product_code');
        $this->accessible_products = $this->datasetToObjects($tmp);
    }
    
    /**
     * @method accessibleProducts()
     * @return array of Product objects
     * @access public
     **/
    public function accessibleProducts(){
        if(!isset($this->accessible_products)){
            $this->setAccessibleProducts();
        }

        return $this->accessible_products;
    }

    /**
     * @method accessibleReportCodes()
     * @return array of strings
     * @access public
     **/
    public function accessibleProductCodes(){
        if(!isset($this->accessible_product_codes)){
            $this->setAccessibleProducts();
        }

        return $this->accessible_product_codes;
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
     * @method setInaccessibleProducts()
     * @return void
     * @access protected
     **/
    protected function setInaccessibleProducts(){
        $ret = [];
        $accessible = $this->accessibleProductsData();
        $accessible_product_codes = array_column($accessible, 'product_code');

        $upsell_data = $this->datasource->getUpsellProducts($accessible_product_codes);

        if(isset($upsell_data) && !empty($upsell_data) && is_array($upsell_data)){
            $this->inaccessible_products = $this->datasetToObjects($upsell_data);
        }

        $this->inaccessible_product_codes = array_column($upsell_data, 'report_code');
        $this->inaccessible_products = $this->datasetToObjects($upsell_data);;
    }


    /**
     * @method inaccessibleProducts()
     * @return array of Product objects
     * @access public
     **/
    public function inaccessibleProducts(){
        if(!isset($this->inaccessible_products)){
            $this->setInaccessibleProducts();
        }

        return $this->inaccessible_products;
    }

    /**
     * @method inaccessibleReportCodes()
     * @return array of strings
     * @access public
     **/
    public function inaccessibleProductCodes(){
        if(!isset($this->inaccessible_product_codes)){
            $this->setInaccessibleProducts();
        }

        return $this->inaccessible_product_codes;
    }

    /**
     * @method datasetToObjectStorage()
     * @return \SplObjectStorage of Product objects
     * @access protected
     **/
    protected function datasetToObjects($dataset){
        $ret = [];
        if(!isset($dataset) || empty($dataset) || !is_array($dataset)){
            return $ret;
        }
        foreach($dataset as $k => $v){
            $ret[] = new Product($this->datasource, $v['product_code'], $v['name'], $v['description']);
        }

        return $ret;
    }
}
