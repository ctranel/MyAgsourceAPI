<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Contains properties and methods specific supplemental data links for various sections of the website.
* 
* Supplemental links can be added to any level of the content hierarchy (column data, column headers, blocks, pages or sections).
* They are links to content that is designed to be deliver within another pages as an overlay or callout
* 
* @author: ctranel
* @date: May 9, 2014
* Requirements: PHP5.4 or above
*
*/

class Supplemental_link
{
	/**
	 * link id
	 * @var int
	 **/
	protected $id;

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
	protected $class;

	/**
	 * collection of supplemental_link_param objects
	 * @var array
	 **/
	protected $arr_params;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($id, $href, $rel, $title, $class)
	{
		$this->id = $id;
		$this->href = $href;
		$this->rel = $rel;
		$this->title = $title;
		$this->class = $class;
	}
	
	/* -----------------------------------------------------------------
	 *  Short Description

	 *  Long Description

	 *  @since: version
	 *  @author: ctranel
	 *  @date: May 9, 2014
	 *  @param: string
	 *  @param: int
	 *  @param: array
	 *  @return: datatype
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 function function_name() {
		;
	}}