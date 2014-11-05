<?php
namespace myagsource\supplemental;

require_once(APPPATH . 'libraries' . FS_SEP . 'supplemental' . FS_SEP . 'SupplementalLink.php');
require_once(APPPATH . 'libraries' . FS_SEP . 'supplemental' . FS_SEP . 'SupplementalComment.php');

use \myagsource\supplemental\SupplementalLink;
use \myagsource\supplemental\SupplementalComment;
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
	 * supplemental_datasource
	 * @var object
	 **/
	protected $supplemental_datasource;

	/**
	 * supplemental link objects
	 * @var SplObjectStorage of Supplemental_link object
	 **/
	protected $supplemental_links;

	/**
	 * supplemental comment objects
	 * @var SplObjectStorage of Supplemental_comment objects
	 **/
	protected $supplemental_comments;

	/**
	 * __construct
	 *
	 * @param: array supplementalLink objects
	 * @param: array supplementalComment objects
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\SplObjectStorage $supplemental_links, \SplObjectStorage $supplemental_comments)
	{
		$this->supplemental_links = $supplemental_links;
		$this->supplemental_comments = $supplemental_comments;
	}
	
	public function supplementalLinks(){
		return $this->supplemental_links;
	}
	
	public function supplementalComments(){
		return $this->supplemental_comments;
	}
	
	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for blocks
	
	*  Factory for supplemental objects for blocks
	
	*  @since: version
	*  @author: ctranel
	*  @date: Oct 28, 2014
	*  @param: int
	*  @param: object supplemental_datasource
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	
	public function getContent(){
		$arr_supplemental = [];
		if (isset($this->supplemental_links) && is_object($this->supplemental_links)){
			foreach($this->supplemental_links as $s){
				$arr_supplemental[] = $s->anchorTag();
			}
		}
		if (isset($this->supplemental_comments) && is_object($this->supplemental_comments)){
			foreach($this->supplemental_comments as $s){
				$arr_supplemental[] = $s->comment();
			}
		}
		return $arr_supplemental;
	}
	
	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for pages

	 *  Factory for supplemental objects for pages

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param: int
	 *  @param: object supplemental_datasource
	 *  @return: Supplemental object
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public static function getPageSupplemental($page_id, \supplemental_model $supplemental_datasource, $site_url) {
		$links = $supplemental_datasource->getLinks(2, $page_id);
		$supplemental_links = SupplementalLink::datasetToObjects($site_url, $links, $supplemental_datasource);
	
		$comments = $supplemental_datasource->getComments(2, $page_id);
		$supplemental_comments = SupplementalComment::datasetToObjects($comments);
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}

	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for blocks
	
	*  Factory for supplemental objects for blocks
	
	*  @since: version
	*  @author: ctranel
	*  @date: Oct 28, 2014
	*  @param: int
	*  @param: object supplemental_datasource
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	public static function getBlockSupplemental($block_id, \supplemental_model $supplemental_datasource, $site_url) {
		$links = $supplemental_datasource->getLinks(1, $block_id);
		$supplemental_links = SupplementalLink::datasetToObjects($site_url, $links, $supplemental_datasource);
	
		$comments = $supplemental_datasource->getComments(1, $block_id);
		$supplemental_comments = SupplementalComment::datasetToObjects($comments);
		
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}
	
	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for blocks
	
	*  Factory for supplemental objects for blocks
	
	*  @since: version
	*  @author: ctranel
	*  @date: Nov 5, 2014
	*  @param: int
	*  @param: object supplemental_datasource
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	public static function getColHeaderSupplemental($field_id, \supplemental_model $supplemental_datasource, $site_url) {
		$links = $supplemental_datasource->getLinks(4, $field_id);
		$supplemental_links = SupplementalLink::datasetToObjects($site_url, $links, $supplemental_datasource);
	
		$comments = $supplemental_datasource->getComments(1, $field_id);
		$supplemental_comments = SupplementalComment::datasetToObjects($comments);
	
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}
}