<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* -----------------------------------------------------------------
 *  Supplemental Comment model
 *
 *  Handles supplemental comments for all levels of web content (section/page comments, column tips, etc)
 *
 *  @category: 
 *  @package: 
 *  @author: ctranel
 *  @date: May 14, 2014
 *  @version: 1.0
 * -----------------------------------------------------------------
 */
 
 class Supp_comment_model extends CI_Model{
	/**
	 * $comment
	 * @var string
	 **/
	protected $comment;

	/**
	 * $comment
	 * @var string
	 **/
	protected $tables;
	
	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(){
		parent::__construct();
		$this->tables = $this->config->item('tables', 'ion_auth');
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