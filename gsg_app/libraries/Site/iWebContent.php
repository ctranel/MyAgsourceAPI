<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
interface iWebContent {
//	public function childKeyValuePairs();
	public function id();
	public function path();
	public function name();
	public function children();
	public function loadChildren(\SplObjectStorage $children);
/*	public function loadChildren(iWebContent $section, iWebContent $pages, $user_id, $herd, $arr_task_permissions);

	public function childrenList(){}
	public function hasChildren(){}
//	public function setParentObj(){}
//	public function parentObj(){}
	public function hasParent(){}
	
	public function getChildrenByScope(){}
	public function getChildrenByHerd(){}
	public function getChildrenByUser(){}
	public function getChildrenByPath(){}
*/
}

?>