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
	function set_redirect_url($curr_control_path, $redirect_url_in, $referrer_in){
//print('<br>2');
//var_dump($curr_control_path, $redirect_url_in, $referrer_in);
		if(
			$referrer_in == $curr_control_path
			|| $referrer_in == 'auth/login'
			|| strpos($referrer_in, 'change_herd') !== FALSE
			|| $referrer_in == 'auth/logout'
			|| $referrer_in == 'benchmarks'
			|| strpos($referrer_in, 'cow_page') !== FALSE
			|| strpos($referrer_in, 'custom_reports/select') !== FALSE
			|| strpos($referrer_in, 'custom_reports/insert') !== FALSE
		){
			$referrer_in = '';
		}
//print('<br>3' . $referrer_in);
		$redirect_url = $redirect_url_in !== FALSE ? str_replace('/csv', '', str_replace('/pdf', '', $redirect_url_in)) : $referrer_in;
//print('<br>4' . $redirect_url);
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