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
	public function set_value_NOTUSED($field = '', $default = '')
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
	public function run_input($group = '') {
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

    /**
     * Set Rules
     *
     * This function takes an array of field names and validation
     * rules as input, validates the info, and stores it
     *
     * @access	public
     * @param	mixed
     * @param	string
     * @return	void
     */
    public function set_rules($field, $label = '', $rules = '')
    {
        $input_data = $this->CI->input->userInputArray();

        // No reason to set rules if we have no POST data
        if (count($input_data) == 0)
        {
            return $this;
        }

        // If an array was passed via the first parameter instead of indidual string
        // values we cycle through it and recursively call this function.
        if (is_array($field))
        {
            foreach ($field as $row)
            {
                // Houston, we have a problem...
                if ( ! isset($row['field']) OR ! isset($row['rules']))
                {
                    continue;
                }

                // If the field label wasn't passed we use the field name
                $label = ( ! isset($row['label'])) ? $row['field'] : $row['label'];

                // Here we go!
                $this->set_rules($row['field'], $label, $row['rules']);
            }
            return $this;
        }

        // No fields? Nothing to do...
        if ( ! is_string($field) OR  ! is_string($rules) OR $field == '')
        {
            return $this;
        }

        // If the field label wasn't passed we use the field name
        $label = ($label == '') ? $field : $label;

        // Is the field name an array?  We test for the existence of a bracket "[" in
        // the field name to determine this.  If it is an array, we break it apart
        // into its components so that we can fetch the corresponding POST data later
        if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
        {
            // Note: Due to a bug in current() that affects some versions
            // of PHP we can not pass function call directly into it
            $x = explode('[', $field);
            $indexes[] = current($x);

            for ($i = 0; $i < count($matches['0']); $i++)
            {
                if ($matches['1'][$i] != '')
                {
                    $indexes[] = $matches['1'][$i];
                }
            }

            $is_array = TRUE;
        }
        else
        {
            $indexes	= array();
            $is_array	= FALSE;
        }

        // Build our master array
        $this->_field_data[$field] = array(
            'field'				=> $field,
            'label'				=> $label,
            'rules'				=> $rules,
            'is_array'			=> $is_array,
            'keys'				=> $indexes,
            'postdata'			=> NULL,
            'error'				=> ''
        );

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @access	public
     * @param	string
     * @param	field
     * @return	bool
     */
    public function matches($str, $field)
    {
        $input_data = $this->CI->input->userInputArray();

        if ( ! isset($input_data[$field]))
        {
            return FALSE;
        }

        $field = $input_data[$field];

        return ($str !== $field) ? FALSE : TRUE;
    }

    // --------------------------------------------------------------------


}