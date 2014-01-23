<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * Set Redirect URL
 *
 * @access	public
 * @param	string	current controller path
 * @return	$redirect_url
 */
if ( ! function_exists('set_redirect_url'))
{
	function set_redirect_url($curr_control_path){
		$ci =& get_instance();
		$ci->session->keep_flashdata('redirect_url');
		$tmp = $ci->session->flashdata('redirect_url');
		$redirect_url = $tmp !== FALSE ? str_replace('/csv', '', str_replace('/pdf', '', $tmp)) : $ci->as_ion_auth->referrer;
		if($redirect_url == $curr_control_path || $redirect_url == 'auth/login' || $redirect_url == 'change_herd/select' || $redirect_url == 'auth/logout' || $redirect_url == 'benchmarks') $redirect_url = '';
		$ci->session->set_flashdata('redirect_url', $redirect_url);
		return $redirect_url;
	}
}
// ------------------------------------------------------------------------

/**
 * Anchor Link
 *
 * Creates an anchor based on the local URL.
 *
 * @access	public
 * @param	string	the URL
 * @param	string	the link title
 * @param	mixed	any attributes
 * @return	string
 */
if ( ! function_exists('anchor'))
{
	function anchor($uri = '', $title = '', $attributes = '')
	{
		$title = (string) $title;

		if ( ! is_array($uri))
		{
			if(substr($uri, 0, 1) == '#'){
				$site_url = $uri;
			}
			else
			{
				$site_url = ( ! preg_match('!^\w+://! i', $uri)) ? site_url($uri) : $uri;
			}
		}
		else
		{
			$site_url = site_url($uri);
		}

		if ($title == '')
		{
			$title = 'n/a';
		}

		if ($attributes != '')
		{
			$attributes = _parse_attributes($attributes);
		}

		return '<a href="'.$site_url.'"'.$attributes.'>'.$title.'</a>';
	}
}