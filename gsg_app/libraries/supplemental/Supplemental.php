<?php
namespace myagsource\supplemental;

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
	public function __construct(SplObjectStorage $supplemental_links, SplObjectStorage $supplemental_comments)
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
	public static function getPageSupplemental($page_id, $supplemental_datasource) {
		$links = $supplemental_datasource->getLinks(2, $page_id);
		$supplemental_links = SupplementalLink::datasetToObjects($links);
	
		$comments = $supplemental_datasource->getComments(2, $page_id);
		$supplemental_comments = SupplementalComment::datasetToObjects($comments);
				
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}
}