<?php
namespace myagsource\supplemental;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//require_once(APPPATH . 'libraries' . FS_SEP . 'MyaObjectStorage.php');

//use \myagsource\MyaObjectStorage;


/*
*
* Contains properties and methods specific supplemental data links for various sections of the website.
* 
* Supplemental links can be added to any level of the content hierarchy (column data, column headers, blocks, pages or sections).
* They are links to content that is designed to be deliver within another pages as an overlay or callout
* 
* @author: ctranel
* @date: May 9, 2014
*
*/

class SupplementalLinkParam extends \SplObjectStorage
{
	/**
	 * link id
	 * @var int
	 **/
	protected $id;

	/**
	 * param name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * param db field name
	 * @var string
	 **/
	protected $value_db_field_name;

	/**
	 * static param value
	 * @var string
	 **/
	protected $value;

	/**
	 * __construct
	 *
	 * @param: string href
	 * @param: string rel
	 * @param: string title
	 * @param: string class
	 * @param: SupplementalLinkParams
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($name, $value, $value_db_field_name){
		$this->name = $name;
		$this->value_db_field_name = $value_db_field_name;
		$this->value = $value;
	}
	
	/* -----------------------------------------------------------------
	 *  returns href

	 *  returns href

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function name() {
		return $this->name;
	}
	
	/* -----------------------------------------------------------------
	 *  returns rel

	 *  returns rel

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function value_db_field_name() {
		return $this->value_db_field_name;
	}
	
	/* -----------------------------------------------------------------
	 *  returns title

	 *  returns title

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function value() {
		return $this->value;
	}
	
	/* -----------------------------------------------------------------
	 *  returns url text for parameter

	 *  returns url text for parameter

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param bool is external link (uses '?' before param and '&' between k => v pairs)
	 *  @param bool is first param (needed for external links)
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function urlText($external, $is_first_param) {
 		$param_text = '';
 		$value = $this->value;
 		if(isset($this->value_db_field_name) && !empty($this->value_db_field_name)){
 			$value = '{' . $this->value_db_field_name . '}';
 		}
	 	if($external){
			$param_text .= $is_first_param ? '?' : '&';
			$param_text .= $this->name. '=' . $value;
		}
		else{
			$param_text .= '/' . $value;
		}
		return $param_text;
	}
	
	/* -----------------------------------------------------------------
	 *  Factory function, takes a dataset and returns an object storage of Supplemental_link objects
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public static function datasetToObjects($dataset) {
	 	$ret = new \SplObjectStorage();
		if(isset($dataset) && is_array($dataset)){
			foreach($dataset as $r){
				$ret->attach(new SupplementalLinkParam(
					$r['name'],
					$r['value'],
					$r['value_db_field_name']
				));
			}
		}
		return $ret;
	}
}