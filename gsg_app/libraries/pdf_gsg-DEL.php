<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'libraries/pdf.php';

/************************************************************
 * Extends TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Chris Tranel
 ***********************************************************/
class pdf_gsg extends pdf {
	/**
	 * make_table() Generates table header if the objects header_structure property is set.
	 * @param array of data to include in the PDF table
	 * @return void
	 * @access public
	 */
	public function make_table($data) {
		$arr_qtile_colors = array(
			1 => array(190, 230, 228),
			2 => array(255, 255, 153),
			3 => array(252, 152, 92),
			4 => array(255, 20, 20)
		);
		list($p1, $p2, $p3) = $this->gsg_fill_color;
		$this->SetFillColor($p1, $p2, $p3);
		$this->SetTextColor($this->gsg_text_color);
		$row_fill = 0;
		$restore = TRUE;
		$border = array('LTRB' => array('width' => .2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 65, 71)));
		if(isset($data) && is_array($data)){
			foreach($data as $row) {
				$row_fill=!$row_fill;
				$num_cols = count($row);
				$ct = 0;
				foreach($row as $f=>$col) {
					if($restore) {
						list($p1, $p2, $p3) = $this->gsg_fill_color;
						$cell_fill = $row_fill;
						$restore = FALSE;
					} 
					elseif(($f == 'net_merit_amt' || $f == 'est_net_merit_amt') && $row['decision_guide_qtile_num'] > 0) {
						list($p1, $p2, $p3) = $arr_qtile_colors[$row['decision_guide_qtile_num']];
						$cell_fill = TRUE;
						$restore = TRUE;
					}
					$this->SetFillColor($p1, $p2, $p3);
					$ln = $ct < $num_cols?'L':'R';
					if (strpos($f,'isnull') === FALSE && ($f == 'net_merit_amt' || $f == 'est_net_merit_amt')){
						$x = $this->GetX();
						$y = $this->GetY();
						$this->writeHTMLCell($this->arr_pdf_width[$f], $this->_config['cell_height'], $x, $y, $col, $border, $ln, $cell_fill, TRUE, 'R');
					}
					elseif (strpos($f,'isnull') === FALSE) $this->Cell($this->arr_pdf_width[$f], $this->_config['cell_height'], $col, $border, $ln, 'R' ,$cell_fill);
					$ct++;
				}
				$this->Ln();
			}
		}
	}
}