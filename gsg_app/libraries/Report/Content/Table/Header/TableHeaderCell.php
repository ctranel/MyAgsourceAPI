<?php
namespace myagsource\Report\Content\Table\Header;

use myagsource\Supplemental\iSupplemental;
use myagsource\Report\iBlock;
use myagsource\Supplemental\Content\Supplemental;
use myagsource\Report\iSort;

/**
* Name:  Table Header Library
*
* Author: ctranel
*
* Created: 2015/03/01
*
* Description:  Logic and service functions to support multi-level table headers.
*
* Requirements: PHP5 or above
* 
* @todo: break into 2 classes (header group, header leaf) with common interface
*
*/

class TableHeaderCell {
	/**
	 * id
	 * 
	 * @var int
	 **/
	protected $id;
	
	/**
	 * parent_id
	 * 
	 * @var int
	 **/
	protected $parent_id;
	
	/**
	 * text
	 * 
	 * @var string
	 **/
	protected $text;
	
	/**
	 * colspan
	 * 
	 * @var int
	 **/
	protected $colspan;
	
	/**
	 * rowspan
	 * 
	 * @var int
	 **/
	protected $rowspan;
/* LEAF PROPERTIES */
	/**
	 * is_sortable
	 *
	 * @var boolean
	 **/
	protected $is_sortable;
	
	/**
	 * is_displayed
	 *
	 * @var boolean
	 **/
	protected $is_displayed;
	
	/**
	 * default_sort_order
	 *
	 * @var string
	 **/
	protected $default_sort_order;
/* LEAF PROPERTIES */
	
	/**
	 * pdf_width
	 *
	 * @var int
	 **/
	protected $pdf_width;
	
	/**
	 * db_field_name
	 *
	 * @var string
	 **/
	protected $db_field_name;
	
	/**
	 * supplemental
	 *
	 * @var iSupplemental
	 **/
	protected $supplemental;
	
	/**
	 * children
	 *
	 * @var \SplObjectStorage of TableHeaderCell objects
	 **/
	protected $children;
	

	public function __construct($id, $parent_id, $db_field_name, $text, $pdf_width, iSupplemental $supplemental = null){
		$this->id = $id;
		$this->parent_id = $parent_id;
		$this->db_field_name = $db_field_name;
		$this->text = $text;
		$this->pdf_width = $pdf_width;
		if(isset($supplemental)){
			$this->supplemental = $supplemental;
		}
	}
	
	public function id(){
		return $this->id;
	}

	public function parentId(){
		return $this->parent_id;
	}
	
	public function dbFieldName(){
		return $this->db_field_name;
	}

	public function text(){
		return $this->text;
	}

	public function defaultSortOrder(){
		return $this->default_sort_order;
	}

	public function colspan(){
		return $this->colspan;
	}

	public function rowspan(){
		return $this->rowspan;
	}

	public function isDisplayed(){
		return (bool)$this->is_displayed;
	}

	public function isSortable(){
		return (bool)$this->is_sortable;
	}

	public function pdfWidth(){
		return $this->pdf_width;
	}

	public function supplementalLink(){
		if($this->supplemental instanceof iSupplemental){
			$tmp = $this->supplemental->getContent();
			return $tmp['links'][0];
		}
	}

	public function children(){
		return $this->children;
	}
	
	public function hasChildren(){
		return (bool)$this->children->count() > 0;
	}

	/**
	 *  @method: setLeafFields()
	 *  @access public
	 *  @param int colspan
	 *  @param int rowspan
	 *  @return void
	 **/
	public function setLeafFields($colspan, $rowspan, $is_sortable, $is_displayed, $default_sort_order){
		$this->colspan = $colspan;
		$this->rowspan = $rowspan;
		$this->is_sortable = $is_sortable;
		$this->is_displayed = $is_displayed;
		$this->default_sort_order = $default_sort_order;
	}
	
	/**
	 *  @method: setSpans()
	 *  @access public
	 *  @param int colspan
	 *  @param int rowspan
	 *  @return void
	 **/
	public function setSpan($colspan, $rowspan){
		$this->colspan = $colspan;
		$this->rowspan = $rowspan;
	}
	
	/**
	 *  @method: setIsSortable()
	 *  @access public
	 *  @param boolean is sortable
	 *  @return void
	public function setIsSortable($is_sortable){
		$this->is_sortable = $is_sortable;
	}
	 **/
	
	/**
	 *  @method: setIsDisplayed()
	 *  @access public
	 *  @param boolean is displayed
	 *  @return void
	public function setIsDisplayed($is_displayed){
		$this->is_displayed = $is_displayed;
	}
	 **/
	
	/**
	 *  @method: setPdfWidth()
	 *  @access public
	 *  @param int pdf width
	 *  @return void
	 **/
	public function setPdfWidth($pdf_width){
		$this->pdf_width = $pdf_width;
	}
	
	/**
	 *  @method: addSupplemental()
	 *  @access public
	 *  @param iSupplemental
	 *  @return void
	 *  
	 *  @todo: will there ever be more than one?
	 **/
	public function addSupplemental(iSupplemental $supplemental){
		$this->supplemental = $supplemental;
	}
	
	/**
	 *  @method: addSort()
	 *  @access public
	 *  @param iSort
	 *  @return void
	 *  
	 *  @todo: will there ever be more than one?
	public function addSort(iSort $sort){
		$this->sort = $sort;
	}
	 **/
	
	/**
	 *  @method: addLink()
	 *  @access public
	 *  @return int
	public function addLink(){ //sort? href, title, rel?

	}
	 **/

	/**
	 *  @method: addChild()
	 *  @access public
	 *  @return int
	 **/
	public function addChild($child){ //sort? href, title, rel?
		$this->children->attach($child);
	}
}
