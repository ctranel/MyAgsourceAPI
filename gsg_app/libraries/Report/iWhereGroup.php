<?php
namespace myagsource\Report;

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
	function __construct($operator, \SplObjectStorage $criteria = null, \SplObjectStorage $child_groups = null);
//	function operator();
	function criteria();
//	function criteriaArray();

}

?>