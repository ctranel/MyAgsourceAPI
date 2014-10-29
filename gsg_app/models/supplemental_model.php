<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
 *  Supplemental model
 *
 *  Handles supplemental for all levels of web content (section/page comments, column tips, etc)
 *
 *  @category: 
 *  @package: 
 *  @author: ctranel
 *  @date: May 14, 2014
 *  @version: 1.0
 * -----------------------------------------------------------------
 */
 
 class Supplemental_model extends CI_Model{
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(){
		parent::__construct();
	}

 	/* -----------------------------------------------------------------
	 *  getComments

	 *  Retrieves comments from database based

	 *  @since: 1.0
	 *  @author: ctranel
	 *  @date: Oct 28, 2014
	 *  @param: int content_type_id
	 *  @param: int content_id
	 *  @return: array of data or null
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	function getComments($content_type_id, $content_id) {
		$ret = $this->db
			->select('comment')
			->where('content_type_id', $content_type_id)
			->where('content_id', $content_id)
			->get('users.dbo.supp_comments')
			->result_array();
		return $ret;
	}
	
	/* -----------------------------------------------------------------
	 *  getLinks
	
	*  Retrieves comments from database based
	
	*  @since: 1.0
	*  @author: ctranel
	*  @date: Oct 28, 2014
	*  @param: int content_type_id
	*  @param: int content_id
	*  @return: array of data or null
	*  @throws:
	* -----------------------------------------------------------------*/
	function getLinks($content_type_id, $content_id) {
		$ret = $this->db
		->select('a_href, a_title, a_rel, a_class')
		->where('content_type_id', $content_type_id)
		->where('content_id', $content_id)
		->get('users.dbo.supp_links')
		->result_array();
		return $ret;
	}
	
	
	/* -----------------------------------------------------------------
	 *  getComment

	 *  Retrieves comment from database based

	 *  @since: 1.0
	 *  @author: ctranel
	 *  @date: May 14, 2014
	 *  @param: int comment_id
	 *  @return: string comment text
	 *  @throws: 
	 * -----------------------------------------------------------------*/
	function getComment($comment_id) {
		$ret = $this->db
			->select('comment')
			->where('id', $comment_id)
			->get('users.dbo.supp_comments')
			->result_array();
		if(isset($ret[0]) && !empty($ret[0])){
			return $ret[0]['comment'];
		}
		return 'Comment ' . $comment_id . ' not found';
	}
}