<?php
namespace myagsource\supplemental;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Contains properties and methods specific supplemental data links for various sections of the website.
* 
* Supplemental links can be added to any level of the content hierarchy (column data, column headers, blocks, pages or sections).
* They are links to content that is designed to be deliver within another pages as an overlay or callout
* 
* @author: ctranel
* @date: May 9, 2014
*
*/

class SupplementalLink
{
	/**
	 * link id
	 * @var int
	 **/
	protected $id;

	/**
	 * link href
	 * @var string
	 **/
	protected $href;

	/**
	 * link title
	 * @var string
	 **/
	protected $title;

	/**
	 * link rel
	 * @var string
	 **/
	protected $rel;

	/**
	 * link class
	 * @var string
	 **/
	protected $a_class;

	/**
	 * collection of supplemental_link_param objects
	 * @var array
	 **/
	protected $arr_params;

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
	public function __construct($href, $rel, $title, $class, $arr_params)
	{
		$this->href = $href;
		$this->rel = $rel;
		$this->title = $title;
		$this->a_class = $class;
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
	 public function href() {
		return $this->href;
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
	 public function rel() {
		return $this->rel;
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
	 public function title() {
		return $this->title;
	}
	
	/* -----------------------------------------------------------------
	 *  returns a_class

	 *  returns a_class

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function a_class() {
		return $this->a_class;
	}
	
	/* -----------------------------------------------------------------
	 *  returns full anchor tag text

	 *  returns full anchor tag text

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function anchorTag() {
		$ret = '<a';
		if(isset($this->href) && !empty($this->href)){
			$ret .= ' href="' . $this->href . '"';
		}
	 	if(isset($this->a_class) && !empty($this->a_class)){
			$ret .= ' class="' . $this->a_class . '"';
		}
		if(isset($this->rel) && !empty($this->rel)){
			$ret .= ' rel="' . $this->rel . '"';
		}
		if(isset($this->title) && !empty($this->title)){
			$ret .= ' title="' . $this->title . '"';
		}
		$ret .= '>';
		$ret .= (isset($this->title) && !empty($this->title)) ? $this->title : 'title';
		$ret .= '</a>';
		return $ret;
	 }
	
	/* -----------------------------------------------------------------
	 *  Factory function, takes a dataset and returns supplemental link objects

	 *  Factory function that takes a dataset array and returns an array of 
	 *  supplemental link objects

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param: array of dataset
	 *  @return: array of Supplemental_link objects
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public static function datasetToObjects($dataset) {
		$ret = new SplObjectStorage();
		if(isset($dataset) && is_array($dataset)){
			foreach($dataset as $r){
				$ret->attach(new SupplementalLink(
					$r['a_href'],
					$r['a_title'],
					$r['a_rel'],
					$r['a_class'],
					$arr_params
				));
			}
		}
	}
}