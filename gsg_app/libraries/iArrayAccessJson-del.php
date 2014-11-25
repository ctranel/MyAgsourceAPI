<?php

namespace myagsource;

/**
 *
 * @author ctranel
 *        
 */
interface iArrayAccessJson extends \ArrayAccess, \JsonSerializable {
	
	function toArray();
	
	//public static function datasetToObjects($dataset);
	
}

?>