<?php
namespace myagsource\Supplemental\Content;

require_once(APPPATH . 'libraries/Supplemental/Content/Supplemental.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalLink.php');
require_once(APPPATH . 'libraries/Supplemental/Content/SupplementalComment.php');

use \myagsource\Supplemental\Content\Supplemental;
use \myagsource\Supplemental\Content\SupplementalLink;
use \myagsource\Supplemental\Content\SupplementalComment;
//use \myagsource\MyaObjectStorage;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Supplemental acts as a factory for supplemental links and supplemental comments.
* 
* @author: ctranel
* @date: February 13, 2015
*
*/

class SupplementalFactory
{
	/**
	 * supplemental_datasource
	 * @var object
	 **/
	protected $datasource;

	/**
	 * site_url
	 * @var string
	 **/
	protected $site_url;

	/**
	 * __construct
	 *
	 * @param: array supplementalLink objects
	 * @param: array supplementalComment objects
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\supplemental_model $datasource, $site_url)
	{
		$this->datasource = $datasource;
		$this->site_url = $site_url;
	}
	
	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for pages

	 *  Factory for supplemental objects for pages

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param: int
	 *  @param: string site url
	 *  @return: Supplemental object
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	public function getPageSupplemental($page_id) {
		$links = $this->datasource->getLinks(2, $page_id);
		$supplemental_links = SupplementalLink::datasetToObjects($this->site_url, $links, $this->datasource);
	
		$comments = $this->datasource->getComments(2, $page_id);
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
	*  @param: string site url
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	public function getBlockSupplemental($block_id) {
		$links = $this->datasource->getLinks(1, $block_id);
		$supplemental_links = SupplementalLink::datasetToObjects($this->site_url, $links, $this->datasource);
	
		$comments = $this->datasource->getComments(1, $block_id);
		$supplemental_comments = SupplementalComment::datasetToObjects($comments);
		
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}
	
	/* -----------------------------------------------------------------
	 *  getHeaderGrpSupplemental
	
	*  Factory for supplemental objects for table headers
	
	*  @since: version
	*  @author: ctranel
	*  @date: March 13, 12015
	*  @param: int field id
	*  @param: string href (anchor tag property)
	*  @param: string rel (anchor tag property)
	*  @param: string title (anchor tag property)
	*  @param: string class (anchor tag property)
	*  @param: string comment to be displayed
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	public function getHeaderGrpSupplemental($supp_id, $href, $rel, $title, $class, $comment) {
		if(!isset($supp_id)){
			return null;
		}

		$supplemental_links = new \SplObjectStorage();
		$supplemental_comments = new \SplObjectStorage();
		
		// Links
		//$links = $this->datasource->getLinks(4, $field_id);
		$tmp = new SupplementalLink($this->site_url, $supp_id, $href, $rel, $title, $class);
		$tmp->setParams($this->datasource);
		$supplemental_links->attach($tmp);
		// Comments
		//$comments = $this->datasource->getComments(4, $field_id);
		//$supplemental_comments = SupplementalComment::datasetToObjects($comment);
		$supplemental_comments->attach(new SupplementalComment($comment));

		//Create and return object
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}

	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for column headers
	
	*  Factory for supplemental objects for column headers
	
	*  @since: version
	*  @author: ctranel
	*  @date: Nov 5, 2014
	*  @param: int field id
	*  @param: string href (anchor tag property)
	*  @param: string rel (anchor tag property)
	*  @param: string title (anchor tag property)
	*  @param: string class (anchor tag property)
	*  @param: string comment to be displayed
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	public function getColHeaderSupplemental($supp_id, $href, $rel, $title, $class, $comment) {
		$supplemental_links = new \SplObjectStorage();
		$supplemental_comments = new \SplObjectStorage();
		
		// Links
		//$links = $this->datasource->getLinks(4, $field_id);
		$tmp = new SupplementalLink($this->site_url, $supp_id, $href, $rel, $title, $class);
		$tmp->setParams($this->datasource);
		$supplemental_links->attach($tmp);
		// Comments
		//$comments = $this->datasource->getComments(4, $field_id);
		//$supplemental_comments = SupplementalComment::datasetToObjects($comment);
		$supplemental_comments->attach(new SupplementalComment($comment));

		//Create and return object
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}

	/* -----------------------------------------------------------------
	 *  Factory for supplemental objects for column data
	
	*  Factory for supplemental objects for column data
	
	*  @since: version
	*  @author: ctranel
	*  @date: Nov 6, 2014
	*  @param: int field id
	*  @param: string href (anchor tag property)
	*  @param: string rel (anchor tag property)
	*  @param: string title (anchor tag property)
	*  @param: string class (anchor tag property)
	*  @return: Supplemental object
	*  @throws:
	* -----------------------------------------------------------------*/
	public function getColDataSupplemental($supp_id, $href, $rel, $title, $class) {
		$supplemental_links = new \SplObjectStorage();
		$supplemental_comments = new \SplObjectStorage();
		
		// Links
		//$links = $this->datasource->getLinks(4, $field_id);
		$tmp = new SupplementalLink($this->site_url, $supp_id, $href, $rel, $title, $class);
		$tmp->setParams($this->datasource);
		$supplemental_links->attach($tmp);

		//Create and return object
		$supp = new Supplemental($supplemental_links, $supplemental_comments);
		return $supp;
	}
}