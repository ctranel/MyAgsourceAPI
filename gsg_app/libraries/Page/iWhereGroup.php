<?php
namespace myagsource\Page;

/**
 * Name:  iWhereGroup
 *
 * Author: ctranel
 *
 * Created:  06-04-2015
 *
 * Description:  Interface for report where groups.
 *
 */
interface iWhereGroup {
	/**
	 */
	function __construct($operator, $criteria = null, $child_groups = null);
//	function operator();
	function criteria();
//	function criteriaArray();

}

?>