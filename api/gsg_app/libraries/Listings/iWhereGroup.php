<?php
namespace myagsource\Listings;

/**
 * Name:  iWhereGroup
 *
 * Author: ctranel
 *
 * Created:  2017-08-01
 *
 * Description:  Interface for listing where groups.
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