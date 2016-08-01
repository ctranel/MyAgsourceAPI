<?php
namespace myagsource\Supplemental\Content;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Contains properties and methods specific supplemental data comments for various sections of the website.
* 
* Supplemental comments can be added to any level of the content hierarchy (column data, column headers, blocks, pages or sections).
* 
* @author: ctranel
* @date: May 9, 2014
*
*/

class SupplementalComment
{
	/**
	 * comment
	 * @var string
	 **/
	protected $comment;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($comment) {
		$this->comment = $comment;
	}
	
	/* -----------------------------------------------------------------
	 *  returns comment

	 *  returns comment

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function comment() {
		return $this->comment;
	}

	/* -----------------------------------------------------------------
	 *  toArray

	 *  returns array representation of object

	 *  @author: ctranel
	 *  @date: 6/22/2016
	 *  @return: array
	 *  @throws:
	 * -----------------------------------------------------------------*/
	public function toArray(){
		$ret = ['comment' => $this->comment];
		return $ret;
	}


	/* -----------------------------------------------------------------
	 *  Factory function, takes a dataset and returns supplemental comment objects

	 *  Factory function that takes a dataset array and returns an array of 
	 *  supplemental comment objects

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param: array of dataset
	 *  @return: array of SupplementalComment objects
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public static function datasetToObjects($dataset) {
		$ret = [];
	 	if(isset($dataset) && is_array($dataset)){
			foreach($dataset as $r){
				$ret[] = new SupplementalComment($r['comment']);
			}
		}
		return $ret;
	 }
}