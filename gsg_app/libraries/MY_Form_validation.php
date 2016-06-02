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
}