<?php
namespace myagsource\Report\Content\Table\Header;

use myagsource\Supplemental\iSupplemental;
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
	 * @var TableHeaderCell[]
	 **/
	protected $children;
	

	public function __construct($id, $parent_id, $db_field_name, $text, $pdf_width, iSupplemental $supplemental = null){
		$this->id = $id;
		$this->parent_id = $parent_id;
		$this->db_field_name = $db_field_name;
		$this->text = $text;
		$this->pdf_width = $pdf_width;
		if($supplemental instanceof iSupplemental){
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
		if(isset($this->supplemental)){
			$tmp = $this->supplemental->getContent();
			return isset($tmp['links'][0]) ? $tmp['links'][0] : null;
		}
	}

	public function children(){
		return $this->children;
	}
	
	public function hasChildren(){
		return (bool)(count($this->children) > 0);
	}

	public function toArray(){
        $ret = [
            'db_field_name' => $this->db_field_name,
            'colspan' => $this->colspan,
            'rowspan' => $this->rowspan,
            'text' => $this->text,
            'is_sortable' => $this->is_sortable,
            'default_sort_order' => $this->default_sort_order
        ];

        if(isset($this->supplemental)){
            $tmp = array_filter($this->supplemental->toArray());
            if(is_array($tmp) && !empty($tmp)){
                $ret['supplemental'] = $tmp;
            }
            unset($tmp);
        }

        return $ret;
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
	 *  @method: addChild()
	 *  @access public
	 *  @return int
	 **/
	public function addChild($child){ //sort? href, title, rel?
		$this->children[] = $child;
	}
}
