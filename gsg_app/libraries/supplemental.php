<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Contains properties and methods specific supplemental data links for various sections of the website.
* 
* Supplemental links can be added to any level of the content hierarchy (column data, column headers, blocks, pages or sections).
* They are links to content that is designed to be deliver within another pages as an overlay or callout
* 
* @author: ctranel
* @date: May 9, 2014
* Requirements: PHP5 or above
*
*/

class Supplemental
{
	/**
	 * table used in benchmarks
	 * @var string
	 **/
	protected $href;

	/**
	 * date field used in benchmarks (will always be test date?)
	 * @var string
	 **/
	protected $title;

	/**
	 * metric used in benchmarks (avg, qtile, top 10%, etc ...)
	 * @var string
	 **/
	protected $metric;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct()
	{
		//nothing for now
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