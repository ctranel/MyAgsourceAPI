<?php
namespace myagsource\Products\Products;

/**
 * Object representing internal report options
 *
 *
 * @name Navigation
 * @author ctranel
 *
 *
 */
class Product{
    /**
     * $datasource
     * @var \Product_model
     **/
    protected $datasource;

    /**
     * $product_code
     * @var string
     **/
    protected $product_code;

    /**
     * $name
     * @var string
     **/
    protected $name;

    /**
     * $description
     * @var string
     **/
    protected $description;

    function __construct(\Product_model $datasource, $product_code, $name, $description) {
        $this->product_code = $product_code;
        $this->name = $name;
        $this->description = $description;
    }

    /**
     * @method productCode()
     * @return string
     * @access public
     **/
    public function productCode(){
        return $this->product_code;
    }

    /**
     * @method name()
     * @return string
     * @access public
     **/
    public function name(){
        return $this->name;
    }

    /**
     * @method description()
     * @return string
     * @access public
     **/
    public function description(){
        return $this->description;
    }
}
