<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iPageAccess.php';
//require_once APPPATH . 'libraries/Site/iPage.php';

use myagsource\Site\iPageAccess;
use myagsource\Site\iPage;

/**
* Name:  HerdAccess
*
* Author: ctranel
*  
* Created:  12-12-2014
*
* Description:  Provides information about a user's access to herds.
*
* Requirements: PHP5 or above
*/

class PageAccess implements iPageAccess
{
	/**
	 * datasource
	 * @var object
	protected $datasource;
	 **/

	/**
	 * page
	 * @var iPage
	 **/
	protected $page;

	/**
	 * can_view_all_content
	 * @var boolean
	 **/
	protected $can_view_all_content;

	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(iPage $page, $can_view_all_content) {
//		$this->datasource = $datasource;
		$this->page = $page;
		$this->can_view_all_content = $can_view_all_content;
	}

	/**
	 * @method hasAccess()
	 * @param is_subscribed
	 * @return boolean
	 * @access public
	 **/
	public function hasAccess($is_subscribed){
		if($this->can_view_all_content){
			return true;
		}
		if($this->page->scope() === 'base'){
			return true;
		}
		if($this->page->scope() === 'subscription' && $is_subscribed){
			return true;
		}
		
		return false;
	}

}




