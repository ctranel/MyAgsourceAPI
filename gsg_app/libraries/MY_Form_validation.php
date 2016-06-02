<?php
class MY_Form_validation extends CI_Form_validation {
/**
	 * Get the value from a form
	 *
	 * Permits you to repopulate a form field with the value it was submitted
	 * with, or, if that value doesn't exist, with the default
	 *
	 * @access	public
	 * @param	string	the field name
	 * @param	string
	 * @return	void
	 */
	public function set_value($field = '', $default = '')
	{
		if ( ! isset($this->_field_data[$field]))
		{
			return $default;
		}

		// If the data is an array output them one at a time.
		//     E.g: form_input('name[]', set_value('name[]');
//		if (is_array($this->_field_data[$field]['postdata']))
//		{
//			return array_shift($this->_field_data[$field]['postdata']);
//		}

		return $this->_field_data[$field]['postdata'];
	}

	// --------------------------------------------------------------------

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function run($group = '') {
		// Do we even have any data to process?  Mm?
        $input_data = $this->CI->input->userInputArray();

		if (!isset($input_data) || count($input_data) == 0) {
			return FALSE;
		}

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) == 0) {
			// No validation rules?  We're done...
			if (count($this->_config_rules) == 0) {
				return FALSE;
			}

			// Is there a validation rule for the particular URI being accessed?
			$uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;

			if ($uri != '' AND isset($this->_config_rules[$uri])) {
				$this->set_rules($this->_config_rules[$uri]);
			}
			else {
				$this->set_rules($this->_config_rules);
			}

			// We're we able to set the rules correctly?
			if (count($this->_field_data) == 0) {
				log_message('debug', "Unable to find validation rules");
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('form_validation');

		// Cycle through the rules for each field, match the
		// corresponding $_POST item and test for errors
		foreach ($this->_field_data as $field => $row) {
			// Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.

			if ($row['is_array'] == TRUE) {
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($input_data, $row['keys']);
			}
			else {
				if (isset($input_data[$field]) AND $input_data[$field] != "") {
					$this->_field_data[$field]['postdata'] = $input_data[$field];
				}
			}

			$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);

		if ($total_errors > 0) {
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array();

		// No errors, validation passes!
		if ($total_errors == 0) {
			return TRUE;
		}

		// Validation fails
		return FALSE;
	}

	// --------------------------------------------------------------------


}