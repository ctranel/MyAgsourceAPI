<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Sections
*
* Author: ctranel
*  
* Created:  5-8-2014
*
* Description:  Contains properties and methods specific to displaying sections of the website.
*
* Requirements: PHP5 or above
* 
* @todo: this library will be the basis for pages, blocks, etc, and will eventually have an abstract and/or interface to reflect the commonalities
*
*/

class Sections_lib
{
	/**
	 * section id
	 * @var int
	 **/
	protected $section_id;

	/**
	 * section name
	 * @var string
	 **/
	protected $section_name;
	

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($section_id)
	{
		$this->section_id = $section_id;
	}

	/* -----------------------------------------------------------------
	 *  Short Description
	 *
	 *  Long Description
	 *
	 *  @since: 1.0
	 *  @author: ctranel
	 *  @date: May 8, 2014
	 *  @param: string
	 *  @param: int
	 *  @param: array
	 *  @return: array of supplemental objects
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	 public function load_supplemental() {
		;
	}
	
}


