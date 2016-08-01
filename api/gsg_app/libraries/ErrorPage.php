<?php 
namespace myagsource;

class ErrorPage {
	
	/**
	 * log_error
	 *
	 * @var boolean
	 **/
	protected $log_error;
	
	/**
	 * template_vars
	 *
	 * @var array
	 **/
	protected $template_vars;
	
	/**
	 * header
	 *
	 * @var string
	 **/
	protected $header;
	
	/**
	 * footer
	 *
	 * @var string
	 **/
	protected $footer;
	
	/**
	 * app_path
	 *
	 * @var string
	 **/
	protected $app_path;
	
	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var int
	 */
	protected $ob_level;
	
		
	public function __construct($app_path, $header, $footer, $template_path = 'error_general', $template_vars = null, $log_error = TRUE){
		$this->app_path = $app_path;
		$this->log_error = $log_error;
		$this->template_path = $template_path;
		$this->template_vars = $template_vars;
		$this->header = $header;
		$this->footer = $footer;
	}

	/**
	 * 404 Page Not Found Handler
	 *
	 * @access	private
	 * @param	string	the page
	 * @param 	bool	log error yes/no
	 * @param	array	template variables (like in a view)
	 * @return	string
	 */
	function show_404($page = '')
	{
//var_dump($template_vars);
		$this->template_vars['page'] = $page;
		
		// By default we log this, but allow a dev to skip it
		if ($this->log_error)
		{
			log_message('error', '404 Page Not Found --> '.$page);
		}

		echo $this->show_error('', '', 404, $this->template_vars);
		exit;
	}

	// --------------------------------------------------------------------

	/**
	 * General Error Page
	 *
	 * This function takes an error message as input
	 * (either as a string or an array) and displays
	 * it using the specified template.
	 *
	 * @access	private
	 * @param	string	the heading
	 * @param	string	the message
	 * @param	string	the template name
	 * @param 	int		the status code
	 * @param	array	template variables (like in a view)
	 * @return	string
	 */
	function show_error($heading, $message, $status_code = 500)
	{
		set_status_header($status_code);

		$message = '<p>'.implode('</p><p>', ( ! is_array($message)) ? array($message) : $message).'</p>';

		if(isset($this->header)){
			$header = $this->header;
		}
		
		if(isset($this->footer)){
			$footer = $this->footer;
		}
		
		
		if(isset($this->template_vars) && is_array($this->template_vars)){
			extract($this->template_vars);
		}
		
		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include($this->app_path . 'errors/'.$this->template_path.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}