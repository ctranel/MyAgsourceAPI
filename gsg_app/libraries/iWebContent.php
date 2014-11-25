<?php

namespace myagsource\web_content;

/**
 *
 * @author ctranel
 *        
 */
interface iWebContent {
	public function childKeyValuePairs(){}
	public function setChildren(Array $array){} //array directly from data source
	public function children(){}
	public function childrenList(){}
	public function hasChildren(){}
//	public function setParentObj(){}
//	public function parentObj(){}
	public function hasParent(){}
	
	public function getChildrenByScope(){}
	public function getChildrenByHerd(){}
	public function getChildrenByUser(){}
	public function getChildrenByPath(){}
	
	
	
	
}

?>