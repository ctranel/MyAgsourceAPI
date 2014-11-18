<?php
//namespace myagsource;

require_once(APPPATH . 'libraries' . FS_SEP . 'benchmarks_lib.php');
require_once(APPPATH . 'libraries' . FS_SEP .'dhi' . FS_SEP . 'herd.php');

use \myagsource\settings\Benchmarks_lib;
use \myagsource\dhi\Herd;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/* -----------------------------------------------------------------
 *	CLASS comments
*  @file: demo.php
*  @author: ctranel
*
*  @description: sets session herd to be the default herd and redirects to landing page when navigating to this page.
*
* -----------------------------------------------------------------
*/

class Demo extends CI_Controller {
	
	protected $arr_user_super_sections;
	protected $arr_user_sections;
	/* 
	 * @var Herd object
	 */
	protected $herd;
	
	function index(){
		$this->herd = new Herd($this->config->item('default_herd'), $this->herd_model);
		$this->set_herd_session_data();
		redirect(site_url());
		/* Load the profile.php config file if it exists*/
		if (ENVIRONMENT == 'development' || ENVIRONMENT == 'localhost') {
			$this->config->load('profiler', false, true);
			if ($this->config->config['enable_profiler']) {
				$this->output->enable_profiler(TRUE);
			} 
		}
	}

	protected function set_herd_session_data(){
		$this->session->set_userdata('herd_code', $this->herd->getHerdCode());
//		$this->session->set_userdata('pstring', 0);
//		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->herd->getHerdCode()));
		$this->session->set_userdata('breed_code', 'HO');
//		$this->session->set_userdata('arr_breeds', $this->herd_model->get_breed_array($this->herd->getHerdCode()));
		$this->session->set_userdata('herd_enroll_status_id', 4);//hard-code sample herd id, was: $this->herd->getHerdEnrollStatus(), $this->config->item('product_report_code'));
		$this->session->set_userdata('recent_test_date', $this->herd->getRecentTest());
		//load new benchmarks
		$this->load->model('setting_model');
		$benchmarks_lib = new Benchmarks_lib($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd->header_info($this->input->post('herd_code')), $this->setting_model);
		$this->session->set_userdata('benchmarks', $benchmarks_lib->getSettingKeyValues());
	}
}
