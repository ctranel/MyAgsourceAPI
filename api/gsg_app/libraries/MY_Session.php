<?php
class MY_Session extends CI_Session {
	function __construct(){
        parent::__construct();
    }

    /**
     * Update an existing session
     *
     * @access    public
     * @return    void
     */
    function sess_update() {
        // skip the session update if this is an AJAX call! This is a bug in CI; see:
        // https://github.com/EllisLab/CodeIgniter/issues/154
        // http://codeigniter.com/forums/viewthread/102456/P15
        if ( !($this->CI->input->is_ajax_request()) && strpos($_SERVER['PHP_SELF'], 'nav') === false && strpos($_SERVER['PHP_SELF'], 'is_eligible') === false) {
            parent::sess_update();
        }
    }
}