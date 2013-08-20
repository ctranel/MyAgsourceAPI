<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// ------------------------------------------------------------------------

/**
 * Convert MySQL date (YYYY-MM-DD) to MM/DD/YYYY
 *
 * Reverses the above process
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('mysql_to_human'))
{
	function mysql_to_human($datestr = '')
	{
		if ($datestr == ''){
			return FALSE;
		}

		$datestr = str_replace('/', '-', trim($datestr));

		if ( ! preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}/', $datestr)) {
			return FALSE;
		}

		$ex = explode("-", $datestr);

		$year  = (strlen($ex['0']) == 2) ? '20'.$ex['0'] : $ex['0'];
		$month = (strlen($ex['1']) == 1) ? '0'.$ex['1']  : $ex['1'];
		$day   = (strlen($ex['2']) == 1) ? '0'.$ex['2']  : $ex['2'];

		return "$month/$day/$year";
	}
}

/**
 * Convert MM/DD/YYYY to MySQL date (YYYY-MM-DD)
 *
 * Reverses the above process
 *
 * @access	public
 * @param	string
 * @return	string
 */
if ( ! function_exists('human_to_mysql'))
{
	function human_to_mysql($datestr = '')
	{
		if ($datestr == ''){
			return FALSE;
		}

		$datestr = str_replace('/', '-', trim($datestr));

		if ( ! preg_match('/^[0-9]{1,2}\-[0-9]{1,2}\-[0-9]{2,4}/', $datestr)) {
			return FALSE;
		}

		$ex = explode("-", $datestr);

		$month = (strlen($ex['0']) == 1) ? '0'.$ex['0']  : $ex['0'];
		$day   = (strlen($ex['1']) == 1) ? '0'.$ex['1']  : $ex['1'];
		$year  = (strlen($ex['2']) == 2) ? '20'.$ex['2'] : $ex['2'];

		return "$year-$month-$day";
	}
}


/**
* Convert timestamp to MySQL's DATE or DATETIME (YYYY-MM-DD hh:mm:ss)
*
* Returns the DATE or DATETIME equivalent of a given timestamp
*
* @author Clemens Kofler <clemens.kofler@chello.at>
* @access    public
* @return    string
*/
function timestamp_to_mysqldatetime($timestamp = "", $datetime = true)
{
  if(empty($timestamp) || !is_numeric($timestamp)) $timestamp = time();

    return ($datetime) ? date("Y-m-d H:i:s", $timestamp) : date("Y-m-d", $timestamp);
}

/**
* Convert Human Date to Timestamp
*
* Returns the timestamp equivalent of a given HUMAN DATE/DATETIME
*
* @author    Cleiton Francisco V. Gomes <http://www.cleitonfco.com.br/>
* @access    public
* @param     string
* @return    integer
*/
function date_to_timestamp($datetime = "", $full_day)
{
	if (!preg_match("/^(\d{1,2})[.\- \/](\d{1,2})[.\- \/](\d{2}(\d{2})?)( (\d{1,2}):(\d{1,2})(:(\d{1,2}))?)?$/", $datetime, $date))
    return FALSE;

  $month = $date[1];
  $day = $date[2];
  $year = $date[3];
//set default values for time
  $hour = ($full_day) ? 23 : 0;
  $min = ($full_day) ? 59 : 0;
  $sec = ($full_day) ? 59 : 0;
  //if time is included in the datetime parameter, overwrite the default
  if(!empty($date[6])) $hour = $date[6];
  if(!empty($date[7])) $min = $date[7];
  if(!empty($date[9])) $sec = $date[9];
  return mktime($hour, $min, $sec, $month, $day, $year);
}

/**
* Convert HUMAN DATE to MySQL's DATE or DATETIME (YYYY-MM-DD hh:mm:ss)
*
* Returns the DATE or DATETIME equivalent of a given HUMAN DATE/DATETIME
*
* @author    Cleiton Francisco V. Gomes <http://www.cleitonfco.com.br/>
* @access    public
* @param     string
* @param     boolean
* @param     boolean
* @return    string
*/
function date_to_mysqldatetime($date = "", $full_day = FALSE)
{
  return timestamp_to_mysqldatetime(date_to_timestamp($date, $full_day));
}

/**
* Convert MySQL's DATE (YYYY-MM-DD) or DATETIME (YYYY-MM-DD hh:mm:ss) to timestamp
*
* Returns the timestamp equivalent of a given DATE/DATETIME
*
* @author Clemens Kofler <clemens.kofler@chello.at>
* @access    public
* @return    integer
*/
function mysqldatetime_to_timestamp($datetime = "")
{
  // function is only applicable for valid MySQL DATETIME (19 characters) and DATE (10 characters)
  $l = strlen($datetime);
    if(!($l == 10 || $l == 19))
      return 0;

    //
    $date = $datetime;
    $hours = 0;
    $minutes = 0;
    $seconds = 0;

    // DATETIME only
    if($l == 19)
    {
      list($date, $time) = explode(" ", $datetime);
      list($hours, $minutes, $seconds) = explode(":", $time);
    }

    list($year, $month, $day) = explode("-", $date);

    return mktime($hours, $minutes, $seconds, $month, $day, $year);
}

/**
* Convert MySQL's DATE (YYYY-MM-DD) or DATETIME (YYYY-MM-DD hh:mm:ss) to date using given format string
*
* Returns the date (format according to given string) of a given DATE/DATETIME
*
* @author Clemens Kofler <clemens.kofler@chello.at>
* @access    public
* @return    integer
*/
function mysqldatetime_to_date($datetime = "", $format = "m-d-Y, H:i")
{
    return date($format, mysqldatetime_to_timestamp($datetime));
}


