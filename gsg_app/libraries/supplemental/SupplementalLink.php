<?php
namespace myagsource\supplemental;
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH . 'libraries' . FS_SEP . 'supplemental' . FS_SEP . 'SupplementalLinkParam.php');
//require_once(APPPATH . 'libraries' . FS_SEP . 'MyaObjectStorage.php');
//use \myagsource\MyaObjectStorage;



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

class SupplementalLink extends \SplObjectStorage
{
	/**
	 * link id
	 * @var int
	 **/
	protected $id;

	/**
	 * link site_url
	 * @var string
	 **/
	protected $site_url;

	/**
	 * link href
	 * @var string
	 **/
	protected $href;

	/**
	 * link title
	 * @var string
	 **/
	protected $title;

	/**
	 * link rel
	 * @var string
	 **/
	protected $rel;

	/**
	 * link class
	 * @var string
	 **/
	protected $a_class;

	/**
	 * collection of supplemental_link_param objects
	 * @var SplObjectStorage
	 **/
	protected $params;

	/**
	 * __construct
	 *
	 * @param: string href
	 * @param: string rel
	 * @param: string title
	 * @param: string class
	 * @param: SupplementalLinkParams
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($site_url, $id, $href, $rel, $title, $class, \SplObjectStorage $params){
		$this->site_url = $site_url;
		$this->id = $id;
		$this->href = $href;
		$this->rel = $rel;
		$this->title = $title;
		$this->a_class = $class;
		$this->params = $params;
	}
	
	/* -----------------------------------------------------------------
	 *  returns href

	 *  returns href

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function href() {
		return $this->href;
	}
	
	/* -----------------------------------------------------------------
	 *  returns rel

	 *  returns rel

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function rel() {
		return $this->rel;
	}
	
	/* -----------------------------------------------------------------
	 *  returns title

	 *  returns title

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function title() {
		return $this->title;
	}
	
	/* -----------------------------------------------------------------
	 *  returns a_class

	 *  returns a_class

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function a_class() {
		return $this->a_class;
	}
	
	/* -----------------------------------------------------------------
	 *  returns params

	 *  returns params

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: SplObjectStorage
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function params() {
		return $this->params;
	}
	
	/* -----------------------------------------------------------------
	 *  returns full anchor tag text

	 *  returns full anchor tag text

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @return: string
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function anchorTag() {
		$ret = '<a';
		$param_text = '';
		if(isset($this->href) && !empty($this->href)){
			$external = (strpos($this->href, $this->site_url) === false && strpos($this->href, 'http') !== false);
			foreach($this->params as $p){
				$param_text .= $p->urlText($external, $param_text === '');
			}
			if(strpos($this->href, 'http') === false){
				$ret .= ' href="' . $this->site_url . $this->href . $param_text . '"';
			}
			else{
				$ret .= ' href="' . $this->href . $param_text . '"';
			}
		}
	 	if(isset($this->a_class) && !empty($this->a_class)){
			$ret .= ' class="' . $this->a_class . '"';
		}
		if(isset($this->rel) && !empty($this->rel)){
			$ret .= ' rel="' . $this->rel . '"';
		}
		if(isset($this->title) && !empty($this->title)){
			$ret .= ' title="' . $this->title . '"';
		}
		$ret .= '>';
		$ret .= (isset($this->title) && !empty($this->title)) ? $this->title : 'title';
		$ret .= '</a>';
		return $ret;
	 }

	/* -----------------------------------------------------------------
	 *  Factory function, takes a dataset and returns supplemental link objects

	 *  Factory function that takes a dataset array and returns object storage of 
	 *  supplemental link objects

	 *  @since: version
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param: array of dataset
	 *  @return: array of Supplemental_link objects
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public static function datasetToObjects($site_url, $dataset, \supplemental_model $supplemental_datasource) {
	 	$ret = new \SplObjectStorage();
		if(isset($dataset) && is_array($dataset)){
			foreach($dataset as $r){
				$param_data = $supplemental_datasource->getLinkParams($r['id']);
				$params = SupplementalLinkParam::datasetToObjects($param_data);
				$ret->attach(new SupplementalLink(
					$site_url,
					$r['id'],
					$r['a_href'],
					$r['a_rel'],
					$r['a_title'],
					$r['a_class'],
					$params
				));
			}
		}
		return $ret;
	}
}