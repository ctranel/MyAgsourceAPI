<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// ------------------------------------------------------------------------

/**
 * Set Redirect URL
 *
 * @access	public
 * @param	string	current controller path
 * @param	string	current redirect path
 * @return	$redirect_path
 */
if ( ! function_exists('set_redirect_url'))
{
	function set_redirect_url($curr_control_path, $redirect_path_in){
		$redirect_path = $curr_control_path;
		//if we don't want to use current path as a redirect, keep the current REDIRECT path
		if(
			$redirect_path === 'auth/login'
			|| strpos($redirect_path, 'change_herd') !== FALSE
			|| $redirect_path === 'auth/logout'
			|| $redirect_path === 'benchmarks'
			|| strpos($redirect_path, 'help') !== FALSE
			|| strpos($redirect_path, 'csv') !== FALSE
			|| strpos($redirect_path, 'pdf') !== FALSE
			|| strpos($redirect_path, 'nav/') !== FALSE
			|| strpos($redirect_path, '/set_role') !== FALSE
			|| strpos($redirect_path, 'setting') !== FALSE
			|| strpos($redirect_path, 'custom_reports/select') !== FALSE
			|| strpos($redirect_path, 'custom_reports/insert') !== FALSE
			|| strpos($redirect_path, 'download/') !== FALSE
			|| strpos($redirect_path, 'ajax') !== FALSE
		){
			$redirect_path = $redirect_path_in;
		}
		return $redirect_path;
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
if ( ! function_exists('prep_href'))
{
	function prep_href($link, $arr_params, &$cr)
	{
		$params = '';
		$site_params = '';
		if(is_array($arr_params) && !empty($arr_params)){
			foreach($arr_params as $k => $v){
				if(isset($cr[$v['field']])){
					$params .= "$k=" . urlencode($cr[$v['field']]) . "&";
					$site_params .= "/" . urlencode($cr[$v['field']]);
				}
				else{
					$params .= "$k=" . urlencode($v['value']) . "&";
					$site_params .= "/" . urlencode($v['value']);
				}
				$params = substr($params, 0, -1);
			}
		}
		
		if(substr($link, 0, 1) == '#'){
			return $link;
		}
		elseif((strpos($link, 'http') === FALSE && !empty($link)) || strpos($link, 'myagsource.com') !== FALSE){
			return site_url($link . $site_params);
		}
		elseif(!empty($link)){
			return site_url($link . '?' . $params);
		}
	}
}

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