<?php
namespace myagsource\supplemental;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries' . FS_SEP . 'iArrayAccessJson.php');
require_once(APPPATH . 'libraries' . FS_SEP . 'MyaObjectStorage.php');

use \myagsource;

/**
* Contains properties and methods common to multiple supplemental-related classes of the website.
* 
* Contains properties and methods common to multiple supplemental-related classes of the website.
* 
* @author: ctranel
* @date: Nov 4, 2014
*
*/

abstract class abSupplemental extends \myagsource\MyaObjectStorage implements \myagsource\iArrayAccessJson
{
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