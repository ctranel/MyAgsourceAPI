<?php
namespace myagsource\Supplemental\Content;

require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalLink.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalComment.php');

use \myagsource\Supplemental\Content\SupplementalLink;
use \myagsource\Supplemental\Content\SupplementalComment;
//use \myagsource\MyaObjectStorage;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Contains properties and methods specific supplemental data links for various sections of the website.
* 
* Supplemental links can be added to any level of the content hierarchy (column data, column headers, blocks, pages or sections).
* They are links to content that is designed to be deliver within another pages as an overlay or callout
* 
* @author: ctranel
* @date: May 9, 2014
*
*/

class Supplemental
{
	/**
	 * supplemental link objects
	 * @var SupplementalLink object
	 **/
	protected $link;

	/**
	 * supplemental comment objects
	 * @var SplObjectStorage of Supplemental_comment objects
	 **/
	protected $comment;

	/**
	 * __construct
	 *
	 * @param: SplObjectStorage of supplementalLink objects
	 * @param: SplObjectStorage of supplementalComment objects
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\SplObjectStorage $links, \SplObjectStorage $comments)
	{
		$this->links = $links;
		$this->comments = $comments;
	}
	
	public function supplementalLinks(){
		return $this->links;
	}
	
	public function supplementalComments(){
		return $this->comments;
	}
	
	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for blocks
	
	*  Factory for supplemental objects for blocks
	
	*  @since: version
	*  @author: ctranel
	*  @date: Oct 28, 2014
	*  @return: Array of strings
	*  @throws:
	* -----------------------------------------------------------------*/
	
	public function getContent(){
		$arr_supplemental = [];
		if (isset($this->links) && is_object($this->links)){
			foreach($this->links as $s){
				$arr_supplemental['links'][] = $s->anchorTag();
			}
		}
		if (isset($this->comments) && is_object($this->comments)){
			foreach($this->comments as $s){
				$arr_supplemental['comments'][] = $s->comment();
			}
		}
		return $arr_supplemental;
	}
}