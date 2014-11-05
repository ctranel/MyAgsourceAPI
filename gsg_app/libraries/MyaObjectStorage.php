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

	/* -----------------------------------------------------------------
	 *  Converts object properties to array

	 *  Converts object properties to array

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Nov 4, 2014
	 *  @return: array
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public function toArray() {
		$ret = array();
		$this->rewind();
		if(!is_object($this->current())){
			return;
		}
		$arr_this = get_object_vars($this->current());
		foreach($arr_this as $key => $value) {
			if(is_object($value)){
				$ret[$key] = $value->toArray();
			}
			else{
				$ret[$key] = $value;
			}
		}
		return $ret;
	}
}

?>