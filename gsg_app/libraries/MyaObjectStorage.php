<?php
namespace myagsource;

require_once(APPPATH . 'libraries' . FS_SEP . 'iArrayAccessJson.php');


/**
 *
 * @author ctranel
 *        
 */
class MyaObjectStorage extends \SplObjectStorage implements iArrayAccessJson {
	
	/**
	 */
	function __construct() {
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see JsonSerializable::jsonSerialize()
	 *
	 */
	public function jsonSerialize() {
		$ret = array();
		foreach($this as $key => $value) {
			if(is_object($value)){
				$ret[$key] = $value->jsonSerialize();
			}
			else{
				$ret[$key] = $value;
			}
			
		}
		return $ret;
	}
}

?>