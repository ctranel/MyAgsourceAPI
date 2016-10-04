<?php
//namespace myagsource;

require_once(APPPATH . 'libraries/Benchmarks/Benchmarks.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use \myagsource\Benchmarks\Benchmarks;
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

class Demo extends MY_Controller {
	
	/* 
	 * @var Herd object
	 */
	
	function index(){
		//Clear out session
		$this->session->unset_userdata('herd_code');
		$this->session->unset_userdata('arr_pstring');
		$this->session->unset_userdata('pstring');
		$this->session->unset_userdata('arr_tstring');
		$this->session->unset_userdata('tstring');
		
		$this->session->keep_all_flashdata();
		
		if($this->as_ion_auth->login('support@myagsource.com', 'AQECTGBUZI', false)){ //if the login is successful
			//$this->_record_access(1); //1 is the page code for login for the user management section
			$this->session->set_flashdata('message', [$this->as_ion_auth->messages()]);
			redirect(site_url('dhi/change_herd/select'));
		}
		else{ //if the login was un-successful
			$this->session->set_flashdata('message', ['Sorry, we could not log in the guest user.  Please contact customer service for assistance: 1-800-236-4995']);
			redirect(site_url('auth/login')); //use redirects instead of loading views for compatibility with MY_Controller libraries
		}
	}
/*
	protected function set_herd_session_data(){
		$this->session->set_userdata('herd_code', $this->herd->herdCode());
//		$this->session->set_userdata('pstring', 0);
//		$this->session->set_userdata('arr_pstring', $this->herd_model->get_pstring_array($this->herd->getHerdCode()));
//		$this->session->set_userdata('breed_code', 'HO');
//		$this->session->set_userdata('arr_breeds', $this->herd_model->breedArray($this->herd->getHerdCode()));
		$this->session->set_userdata('recent_test_date', $this->herd->getRecentTest());
		//load new benchmarks
		$this->load->model('setting_form_model');
		$benchmarks_lib = new Benchmarks($this->session->userdata('user_id'), $this->input->post('herd_code'), $this->herd->header_info($this->input->post('herd_code')), $this->setting_form_model);
		$this->session->set_userdata('benchmarks', $benchmarks_lib->getSettingKeyValues());
	} */
}
