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

	 *  Retrieves comments from database where this is not a corresponding link 
	 *  (i.e., show one or the other, and links have precendence)

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
			->select('sc.comment')
			->where('sc.content_type_id', $content_type_id)
			->where('sc.content_id', $content_id)
			->join('users.dbo.supp_links sl', 'sl.content_type_id = sc.content_type_id AND sl.content_id = sc.content_id', 'left')
			->where('sl.content_type_id IS NULL')
			->get('users.dbo.supp_comments sc')
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
		->select('id, a_href, a_title, a_rel, a_class')
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
 	
	/* -----------------------------------------------------------------
	 *  getLinkParams
	
	*  Retrieves comments from database based
	
	*  @since: 1.0
	*  @author: ctranel
	*  @date: Oct 28, 2014
	*  @param: int supplemental_link_id
	*  @return: array of data or null
	*  @throws:
	* -----------------------------------------------------------------*/
	function getLinkParams($supplemental_link_id) {
		$ret = $this->db
		->select('p.param_name AS name, p.param_value AS value, f.db_field_name AS value_db_field_name')
		->join('users.dbo.db_fields f', 'p.param_value_field_id = f.id', 'left')
		->where('sl_id', $supplemental_link_id)
		->order_by('p.list_order', 'ASC')
		->get('users.dbo.supp_link_params p')
		->result_array();
		return $ret;
	}
 }