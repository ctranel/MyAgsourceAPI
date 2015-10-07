<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/* this page was added to support the dynamic menu generation.  There
 * is not a separate supersection/section for custom reports, so we
 * automatically redirect to the next level.
 * 
 */


class Land extends CI_Controller {
	function __construct(){
		parent::__construct();
	}
	
	function index(){
		$this->session->keep_all_flashdata();
		redirect(site_url('custom_report/custom_report/create'));
	}
}