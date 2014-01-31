<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Herd File
*
* Author: ctranel
*		  ctranel@agsource.com
*
* Created:  2.17.2011
*
* Description:  Handles herd file uploads for the Genetic Selection Guide
*
* Requirements: PHP5 or above
*
*/

class Herd_file{
	/**
	 * herd identifier
	 *
	 * @var string
	 **/
	protected $herd_code;
	/**
	 * fields in the files to be processed
	 *
	 * @var array
	 **/
	protected $arr_file_fields;
	/**
	 * quartile breaks
	 *
	 * @var array
	 **/
	protected $arr_quartile_break;
	/**
	 * average for each quartile
	 *
	 * @var array
	 **/
	protected $arr_quartile_avg;
	/**
	 * CodeIgniter global
	 *
	 * @var string
	 **/
	protected $ci;
	
	public function __construct(){
		$this->ci =& get_instance();
	}
	
	/**
	 * read_csv()
	 *
	 * @author ctranel
	 * @param string csv filename
	 * @return boolean
	 **/
	public function read_csv ($filename){
		if (($file_resource = fopen($filename, 'r')) !== FALSE) {
			$arr_tmp = fgetcsv($file_resource, 175); //burn the header row
			if(count($arr_tmp) != count($this->arr_file_fields)) return FALSE;
			while (($data = fgetcsv($file_resource, 175, ",")) !== FALSE) {
				$ret_array[] = array_combine($this->arr_file_fields, $data);
			}
			fclose($file_resource);
			return $ret_array;
		}
		else {
			return FALSE;
		}
	}

	/**
	 * add_quartiles.
	 *
	 * @return Void - Sets the object's $arr_quartile_avg sets and $arr_quartile_data properties)
	 * @author ctranel
	 * @param array file data (by reference)
	 * @param array key of field to be calculate
	 * @param array key of field that stores quartile number
	 * @param array herd data
	 * @param char cow/heifer code
	 **/
	public function add_quartiles (&$arr_data, $key_field, $quartile_field, $herd_data, $cow_heifer_code) {
		$this->herd_code = $herd_data['herd_code'];
		// go through each row once to set est net merit
		foreach ($arr_data as $k => $row){
			$arr_data[$k]['herd_member_code'] = $cow_heifer_code;
			$arr_data[$k]['herd_code'] = $this->herd_code;
			$arr_data[$k]['serial_num'] = $arr_data[$k]['control_num'];
			$arr_data[$k]['cow_id'] = (int)$arr_data[$k]['cow_id'] == 0?'':$arr_data[$k]['cow_id'];
			$est_nm = ((int)$arr_data[$k]['dam_net_merit_amt'] + (int)$arr_data[$k]['sire_net_merit_amt']) / 2;
			if ($cow_heifer_code == 'M' && $row['net_merit_amt'] == ''){
				if($row['dam_net_merit_amt'] != '' || $arr_data[$k]['sire_net_merit_amt'] != ''){
					$arr_data[$k]['net_merit_amt'] = $est_nm;
					$arr_data[$k]['est_net_merit_amt'] = $est_nm;
				}
			}
			if ($cow_heifer_code == 'C' && $row['est_net_merit_amt'] == ''){
				if($row['dam_net_merit_amt'] != '' || $arr_data[$k]['sire_net_merit_amt'] != ''){
					$arr_data[$k]['est_net_merit_amt'] = $est_nm;
				}
			}
			if(!isset($arr_data[$k]['est_net_merit_amt'])) $arr_data[$k]['est_net_merit_amt'] = ''; //the cow file does not have this field, so we need to make sure it is added to all assoc arrays
			if ($arr_data[$k]['calf_due_date'] != '') $arr_data[$k]['calf_due_date'] = $this->_set_due_date($arr_data[$k]['calf_due_date'], $herd_data['test_date']);
		}
		$this->arr_quartile_break = $this->quartiles($arr_data, $key_field); //get field name
		//now that we have quartile numbers for the newly populated net merit field, go through again and populate that data array with the quartile number
		foreach ($arr_data as $k => $row){
			if ($row[$key_field] >= $this->arr_quartile_break['75']){
				$arr_data[$k][$quartile_field] = '1';
				$arr_quartile_data[1][] = $row[$key_field];
			}
			elseif ($row[$key_field] >= $this->arr_quartile_break['50']){
				$arr_data[$k][$quartile_field] = '2';
				$arr_quartile_data[2][] = $row[$key_field];
			}
			elseif ($row[$key_field] >= $this->arr_quartile_break['25']){
				$arr_data[$k][$quartile_field] = '3';
				$arr_quartile_data[3][] = $row[$key_field];
			}
			elseif ($row[$key_field] != ''){
				$arr_data[$k][$quartile_field] = '4';
				$arr_quartile_data[4][] = $row[$key_field];
			}
			else {
				$arr_data[$k][$quartile_field] = '';
			}
		}
		$this->arr_quartile_avg[1] = !empty($arr_quartile_data[1]) ? array_sum($arr_quartile_data[1]) / count($arr_quartile_data[1]) : 0;
		$this->arr_quartile_avg[2] = !empty($arr_quartile_data[2]) ? array_sum($arr_quartile_data[2]) / count($arr_quartile_data[2]) : 0;
		$this->arr_quartile_avg[3] = !empty($arr_quartile_data[3]) ? array_sum($arr_quartile_data[3]) / count($arr_quartile_data[3]) : 0;
		$this->arr_quartile_avg[4] = !empty($arr_quartile_data[4]) ? array_sum($arr_quartile_data[4]) / count($arr_quartile_data[4]) : 0;
	}

	/**
	 * percentile() - handles all database interactions needed for writing file contents to DB.
	 *
	 * @param array animal data
	 * @param mixed key
	 * @param int percentile
	 * @return float value boundary for the given percentile
	 * @author ctranel
	 **/
	public function percentile($data, $key, $percentile){
		$this->ci->load->helper('multid_array');
		if( 0 < $percentile && $percentile < 1 ) {
			$p = $percentile;
		}else if( 1 < $percentile && $percentile <= 100 ) {
			$p = $percentile * .01;
		}else {
			return "";
		}
		//$data = $this->_get_populated_rows($data, $key); //don't include empty rows in percentile calculations
		$key_data = multid_array_sort($data, $key, FALSE);
		$count = count($key_data);
		$allindex = ($count-1)*$p;
		$intvalindex = intval($allindex);
		$floatval = $allindex - $intvalindex;
		//sort($data);
		if(!is_float($floatval)){
			$result = $key_data[$intvalindex];
		}
		else {
			if($count > $intvalindex+1)
			$result = $floatval * ($key_data[$intvalindex+1][$key] - $key_data[$intvalindex][$key]) + $key_data[$intvalindex][$key];
			else
			$result = $key_data[$intvalindex][$key];
		}
		return $result;
	}

	/**
	 * quartiles() - handles all database interactions needed for writing file contents to DB.
	 *
	 * @param array animal data
	 * @param mixed key
	 * @return array value for each quartile boundary ('25'=>$q1 value)
	 * @author ctranel
	 **/
	public function quartiles($data, $key) {
		$q1 = $this->percentile($data, $key, 25);
		$q2 = $this->percentile($data, $key, 50);
		$q3 = $this->percentile($data, $key, 75);
		$quartile = array ( '25' => $q1, '50' => $q2, '75' => $q3);
		return $quartile;
	}

	/**
	 * write_to_db() - handles all database interactions needed for writing file contents to DB.
	 *
	 * @param array animal data
	 * @param array general herd data
	 * @param array herd data specific to GSG
	 * @param char cow/heifer code
	 * @return mixed number of rows or False
	 * @author ctranel
	 **/
	public function write_to_db($data, $herd_data, $gsg_herd_data, $cow_heifer_code){
		if (is_array($herd_data)){
			$this->ci->load->model('herd_model');
			$this->ci->load->model('gsg/gsg_herd_model');
			$quartile_prefix = ($cow_heifer_code == 'M') ? '10' : 'heifer';
			$gsg_herd_data[$quartile_prefix . '_qt1_avg_pa_net_merit_amt'] = $this->arr_quartile_avg[1];
			$gsg_herd_data[$quartile_prefix . '_qt2_avg_pa_net_merit_amt'] = $this->arr_quartile_avg[2];
			$gsg_herd_data[$quartile_prefix . '_qt3_avg_pa_net_merit_amt'] = $this->arr_quartile_avg[3];
			$gsg_herd_data[$quartile_prefix . '_qt4_avg_pa_net_merit_amt'] = $this->arr_quartile_avg[4];
			if($herd_data['test_date'] != ''){
				$this->ci->load->helper('date');
				$herd_data['test_date'] = human_to_mysql($herd_data['test_date']);
			}
			//general herd data
			if($this->ci->herd_model->get_herds_by_criteria(array('herd_code'=>$herd_data['herd_code']))->num_rows() > 0) {
				$success = $this->ci->herd_model->update_herds_by_criteria($herd_data, array('herd_code'=>$herd_data['herd_code']));
			}
			else {
				$success = $this->ci->herd_model->insert_herd($herd_data);
			}
			if($success){
				//gsg-specific herd data
				if($this->ci->gsg_herd_model->get_herds_by_criteria(array('herd_code'=>$gsg_herd_data['herd_code']))->num_rows() > 0) {
					$success = $this->ci->gsg_herd_model->update_herds_by_criteria($gsg_herd_data, array('herd_code'=>$gsg_herd_data['herd_code']));
				}
				else {
					$success = $this->ci->gsg_herd_model->insert_herd($gsg_herd_data);
				}
			}
		}
		if ($success && is_array($data)){
			$this->ci->load->model('gsg/animal_model');
			$this->ci->animal_model->delete_animals_by_criteria(array('herd_code'=>$herd_data['herd_code'], 'herd_member_code'=>$cow_heifer_code));
			$success = $this->ci->animal_model->write_array($data);
			$this->ci->session->set_userdata(array('herd_code'=>$this->herd_code));
		}
		else return FALSE;
		return $success;
	}
	
	/**
	 * _set_due_date() - handles all database interactions needed for writing file contents to DB.
	 *
	 * @param string due date month and date
	 * @param string test date
	 * @return string - date in mySQL format (inclding year)
	 * @author ctranel
	 * @access private
	 **/
	private function _set_due_date($date_in, $test_date){
		$this->ci->load->helper('date');
		list($m, $d) = explode('-', $date_in);
		list($tm, $td, $ty) = explode('/', $test_date);
		$test_stamp = mktime(0, 0, 0, $tm, $td, $ty);
		$due_stamp = mktime(0, 0, 0, $m, $d, $ty);
		if ($due_stamp < $test_stamp) $due_stamp = mktime(0, 0, 0, $m, $d, $ty + 1);
		$ten_months = (10 * 30 * 24 * 60 * 60);//$max_due_stamp = mktime(0, 0, 0, $tm + 10, $td, $ty);
		if(($due_stamp - $test_stamp) > $ten_months) $due_stamp = mktime(0, 0, 0, $m, $d, $ty);
		return date('Y-m-d', $due_stamp);
	}
}
