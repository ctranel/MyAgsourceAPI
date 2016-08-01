<?php
namespace myagsource\Page\Content\Table\Header;

use myagsource\Supplemental\iSupplemental;
use myagsource\Supplemental\Content\Supplemental;
use myagsource\Page\iSort;

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

        if($this->supplemental instanceof iSupplemental){
            $tmp = array_filter($this->supplemental->toArray());
            if(is_array($tmp) && !empty($tmp)){
                $ret['supplemental'] = $tmp;
            }
            unset($tmp);
        }

        return $ret;
    }
/*
    protected function childrenToArray($children){
        foreach($children as $k => $v){
            $ret[$k] = [
                'db_field_name' => $v->dbFieldName(),
                'colspan' => $v->colspan(),
                'rowspan' => $v->rowspan(),
                'text' => $v->text(),
                'is_sortable' => $v->isSortable(),
                'default_sort_order' => $v->defaultSortOrder()
            ];

            $tmp = array_filter($v->supplemental()->toArray());
            if(is_array($tmp) && !empty($tmp)){
                $ret[$k]['supplemental'] = $tmp;
            }
            unset($tmp);

            $children = $v->children();
            if(count($children) > 0){
                $ret[$k]['children'] = $this->childrenToArray($children);
            }
        }
        return $ret;
    }
*/
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
		$this->children[] = $child;
	}
}
