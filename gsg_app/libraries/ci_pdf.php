<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

# override the default TCPDF config file
if(!defined('K_TCPDF_EXTERNAL_CONFIG')) {	
	define('K_TCPDF_EXTERNAL_CONFIG', TRUE);
}
	
# include TCPDF
//@todo: should actually be app url (not path)
require(APPPATH.'config/tcpdf'.EXT);
require_once($tcpdf['base_directory'].'/tcpdf.php');



/************************************************************
 * TCPDF - CodeIgniter Integration
 * Library file
 * ----------------------------------------------------------
 * @author Jonathon Hill http://jonathonhill.net
 * @version 1.0
 * @package tcpdf_ci
 ***********************************************************/
class Ci_pdf extends TCPDF {
	
	
	/**
	 * TCPDF system constants that map to settings in our config file
	 *
	 * @var array
	 * @access protected
	 */
	protected $cfg_constant_map = array(
		'K_PATH_MAIN'	=> 'base_directory',
		'K_PATH_URL'	=> 'base_url',
		'K_PATH_FONTS'	=> 'fonts_directory',
		'K_PATH_CACHE'	=> 'cache_directory',
		'K_PATH_IMAGES'	=> 'image_directory',
		'K_BLANK_IMAGE' => 'blank_image',
		'K_SMALL_RATIO'	=> 'small_font_ratio',
	);
	
	/**
	 * GSG setting contains filter text used for page header generation
	 *
	 * @var array
	 * @access public
	 */
	public $arr_filter_text;
	
	/**
	 * GSG setting contains herd data used for page header generation
	 *
	 * @var array
	 * @access public
	 */
	public $arr_herd_data;
	
	/**
	 * Name of consultant who is printing the report
	 *
	 * @var string
	 * @access public
	 */
	public $consultant;
	
	/**
	 * Text that describes benchmarks being used
	 *
	 * @var string
	 * @access public
	 */
	public $bench_text;
	
	/**
	 * GSG setting contains RGB fill colors for table data 
	 *
	 * @var array
	 * @access public
	 */
	protected $gsg_fill_color = array(253, 232, 224);
		
	/**
	 * GSG setting contains RGB fill colors for table data 
	 *
	 * @var array
	 * @access public
	 */
	protected $gsg_header_fill_color = array(157, 58, 25);
	/**
	 * GSG setting contains text color for table data (0)
	 *
	 * @var string
	 * @access public
	 */
	protected $gsg_text_color = '0';
	
	/**
	 * GSG setting contains table structure used for table header generation
	 *
	 * @var array
	 * @access public
	 */
	public $header_structure;
	
	/**
	 * GSG setting contains an array of field widths used for table header generation and data table generation
	 *
	 * @var array
	 * @access public
	 */
	public $arr_pdf_width;
	
	/**
	 * GSG setting contains column sort columns used for table header generation
	 *
	 * @var array
	 * @access public
	 */
	public $arr_sort_by;
	
	/**
	 * GSG setting contains sort orders--indexes correspond with $arr_sort_by used for table header generation
	 *
	 * @var array
	 * @access public
	 */
	public $arr_sort_order;
	
	/**
	 * Setting contains orientation for pages
	 *
	 * @var string
	 * @access public
	 */
	public $orientation;
	
	/**
	 * Setting contains text for page title
	 *
	 * @var string
	 * @access public
	 */
	public $page_title;
	
	/**
	 * Setting contains text for table title
	 *
	 * @var string
	 * @access public
	 */
	public $table_title;
	
	/**
	 * Settings from our APPPATH/config/tcpdf.php file
	 *
	 * @var array
	 * @access protected
	 */
	protected $_config = array();
	
	
	/**
	 * Initialize and configure TCPDF with the settings in our config file
	 *
	 */
	function __construct() {
		
		# load the config file
		require(APPPATH.'config/tcpdf'.EXT);
		$this->_config = $tcpdf;
		unset($tcpdf);
		
		
		
		# set the TCPDF system constants
		foreach($this->cfg_constant_map as $const => $cfgkey) {
			if(!defined($const)) {
				define($const, $this->_config[$cfgkey]);
				#echo sprintf("Defining: %s = %s\n<br />", $const, $this->_config[$cfgkey]);
			}
		}
		
		# initialize TCPDF		
		parent::__construct(
			$this->_config['page_orientation'], 
			$this->_config['page_unit'], 
			$this->_config['page_format'], 
			$this->_config['unicode'], 
			$this->_config['encoding'], 
			$this->_config['enable_disk_cache']
		);
		
		
		# language settings
		if(is_file($this->_config['language_file'])) {
			include($this->_config['language_file']);
			$this->setLanguageArray($l);
			unset($l);
		}
		
		# margin settings
		$this->SetMargins($this->_config['margin_left'], $this->_config['margin_top'], $this->_config['margin_right']);
		
		# header settings
		$this->print_header = $this->_config['header_on'];
		#$this->print_header = FALSE; 
		$this->setHeaderFont(array($this->_config['header_font'], '', $this->_config['header_font_size']));
		$this->setHeaderMargin($this->_config['header_margin']);
		$this->SetHeaderData(
			$this->_config['header_logo'], 
			$this->_config['header_logo_width'], 
			$this->_config['header_title'], 
			$this->_config['header_string']
		);
		
		# footer settings
		$this->print_footer = $this->_config['footer_on'];
		$this->setFooterFont(array($this->_config['footer_font'], '', $this->_config['footer_font_size']));
		$this->setFooterMargin($this->_config['footer_margin']);
		if(isset($this->_config['footer_logo']) && isset($this->_config['footer_logo_width'])){
			$this->SetFooterData(
				$this->_config['footer_logo'], 
				$this->_config['footer_logo_width'] 
			);
		}
		# page break
		$this->SetAutoPageBreak($this->_config['page_break_auto'], $this->_config['footer_margin']);
		
		# cell settings
		$this->cMargin = $this->_config['cell_padding'];
		$this->setCellHeightRatio($this->_config['cell_height_ratio']);
		
		# document properties
		$this->author = $this->_config['author'];
		$this->creator = $this->_config['creator'];
		
		# font settings
		$this->SetFont($this->_config['page_font'], '', $this->_config['page_font_size']);
		# image settings
		$this->imgscale = $this->_config['image_scale'];
		
	}
	
	/**
	 * Set header data.
	 * @param $ln (string) header image logo
	 * @param $lw (string) header image logo width in mm
	 * @param $ht (string) string to print as title on document header
	 * @param $hs (string) string to print on document header
	 * @public
	 */
	public function setFooterData($ln='', $lw=0) {
		$this->footer_logo = $ln;
		$this->footer_logo_width = $lw;
	}

	/**
	 * make_table() Generates table header if the objects header_structure property is set.
	 * @param array of data to include in the PDF table
	 * @return void
	 * @access public
	 */
	public function make_table($data) {
		list($p1, $p2, $p3) = $this->gsg_fill_color;
		$this->SetFillColor($p1, $p2, $p3);
		$this->SetTextColor($this->gsg_text_color);
		$fill = 0;
		$border = array('LTRB' => array('width' => .2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->gsg_header_fill_color));
		if(isset($data) && is_array($data)){
			foreach($data as $row) {
				$fill=!$fill;
				$num_cols = count($row);
				$ct = 0;
				foreach($row as $f=>$col) {
					$ln = $ct < $num_cols?'L':'R';
					$align = $ct == 0?'R':'C';
					if (strpos($f,'isnull') === FALSE && $f != 'count') $this->Cell($this->arr_pdf_width[$f], $this->_config['cell_height'], $col, $border, $ln, $align,$fill);
					$ct++;
				}
				$this->Ln();
			}
		}
	}
	
	
	/**
	 * make_table_header() Generates table header if the objects header_structure property is set.
	 * @return void
	 * @access public
	 */
	function make_table_header($table_title = ''){
		if(($this->PageBreakTrigger - $this->y) < 25){
			//start new page
			$this->setY($this->y + 26);
			$this->checkPageBreak();
			//prevent duplicate header
			return FALSE;
		}
		if(isset($this->table_title) && !empty($this->table_title)){
			$this->MultiCell(0, 3, '', 0, 'L', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
			$this->Cell(0, 4, $this->table_title, 0, 1, 'L', 0, '', 0, NULL, '', 'B');
		}
			
		$arr_colspan_queue = array();
		$queue_active = FALSE;
		$row_count = 1;
		$border = array('LTRB' => array('width' => .2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->gsg_header_fill_color));
		if(isset($this->header_structure) && is_array($this->header_structure)){
			foreach($this->header_structure as $row){
				$col_index = 0;
				$queue_index = 0;
				$block_increment = 0;
				$max_rowspan = $this->_max_by_array_dimension($row, 'rowspan');
				$max_rowspan = $max_rowspan > 1 ? $max_rowspan:100;
				$curr_queue_width = 0;
				foreach($row as $k => $th){
					//write horizontal spacing for nested headings (rowspans)
					if(isset($arr_colspan_queue[$row_count - 1][0]['start_index'])){
						if ($arr_colspan_queue[$row_count - 1][0]['start_index'] <= $queue_index) {
							$tmp_array = array_shift($arr_colspan_queue[$row_count - 1]);
							$this->Cell($tmp_array['width'], ($this->_config['cell_height'] * $th['rowspan']), '', 0, 0, 'C', FALSE, 0);
							$queue_index = $tmp_array['end_index'] + 1;
						}
					}
					//Denote sort field and order
					$this->SetFont($this->_config['page_font'], 'B');
					list($d1, $d2, $d3) = $this->gsg_header_fill_color;
					list($l1, $l2, $l3) = $this->gsg_fill_color;
					if ($th['colspan'] > '1') {
						$this->SetFillColor($l1, $l2, $l3);
						$this->SetTextColor($d1, $d2, $d3);
					}
					else {
						$this->SetFillColor($d1, $d2, $d3);
						$this->SetTextColor($l1, $l2, $l3);
					}
					$w = isset($th['pdf_width'])?$th['pdf_width']:$this->arr_pdf_width[$th['field_name']];
					$this->MultiCell($w, ($this->_config['cell_height'] * $th['rowspan']), $this->unhtmlentities($th['text']), $border, 'C', TRUE, 0, NULL, NULL, TRUE, 0, FALSE, FALSE, ($this->_config['cell_height'] * $th['rowspan']), 'B', TRUE);
					//write horizontal spacing for nested headings
					//if this cell is part of a block that spans >1 row
					if ($th['rowspan'] == $max_rowspan){
						if (!$queue_active) {
							$queue_active = TRUE;
							$arr_colspan_queue[$row_count][$block_increment]['start_index'] = $queue_index;
						}
						$curr_queue_width += $w;
					}
					// if there is currently a colspan being built, and either the current column is the last column or the next column ends that colspan
					if($queue_active && (!isset($row[($col_index + 1)]) || $row[($col_index + 1)]['rowspan'] < $max_rowspan)){
						$arr_colspan_queue[$row_count][$block_increment]['width'] = $curr_queue_width;
						$arr_colspan_queue[$row_count][$block_increment]['end_index'] = $queue_index;
						$block_increment++;
						$curr_queue_width = 0;
						$queue_active = FALSE;
					}
					$col_index ++;
					$queue_index += $th['colspan'];
				}
				$this->Ln($this->_config['cell_height']);
				$row_count++;
			}
		}
	}
	
	/**
	 * _max_by_array_dimension() 
	 * @param array multidimensional array
	 * @param mixed key for which to return the max value of array in param 1
	 * @return int maximum value for the specified key in the passed array, 0 if specified key is not found or first parameter is not an array.
	 * @access public
	 */
	function _max_by_array_dimension($array, $key){
		$return_val = 0;
		if(is_array($array)){
			foreach($array as $r){
				if($r[$key] > $return_val) $return_val = $r[$key];
			}
		}
		return $return_val;
	}

	//override functions
	/**
	 * Adds a new page to the document. If a page is already present, the Footer() method is called first to output the footer (if enabled). Then the page is added, the current position set to the top-left corner according to the left and top margins (or top-right if in RTL mode), and Header() is called to display the header (if enabled).
	 * The origin of the coordinate system is at the top-left corner (or top-right for RTL) and increasing ordinates go downwards.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $keepmargins (boolean) if true overwrites the default page margins with the current margins
	 * @param $tocpage (boolean) if true set the tocpage state to true (the added page will be used to display Table Of Content).
	 * @public
	 * @since 1.0
	 * @see startPage(), endPage(), addTOCPage(), endTOCPage(), getPageSizeFromFormat(), setPageFormat()
	 */
	public function AddPage($orientation='', $format='', $keepmargins=false, $tocpage=false) {
		if(isset($this->orientation) && !empty($this->orientation)) $orientation = $this->orientation;
		
		if ($this->inxobj) {
			// we are inside an XObject template
			return;
		}
		if (!isset($this->original_lMargin) OR $keepmargins) {
			$this->original_lMargin = $this->lMargin;
		}
		if (!isset($this->original_rMargin) OR $keepmargins) {
			$this->original_rMargin = $this->rMargin;
		}
		// terminate previous page
		$this->endPage();
		// start new page
		$this->startPage($orientation, $format, $tocpage);
	}

	/**
	 * Starts a new page to the document. The page must be closed using the endPage() function.  Overrides parent class function
	 * The origin of the coordinate system is at the top-left corner and increasing ordinates go downwards.
	 * @param $orientation (string) page orientation. Possible values are (case insensitive):<ul><li>P or PORTRAIT (default)</li><li>L or LANDSCAPE</li></ul>
	 * @param $format (mixed) The format used for pages. It can be either: one of the string values specified at getPageSizeFromFormat() or an array of parameters specified at setPageFormat().
	 * @param $tocpage (boolean) if true the page is designated to contain the Table-Of-Content.
	 * @since 4.2.010 (2008-11-14)
	 * @see AddPage(), endPage(), addTOCPage(), endTOCPage(), getPageSizeFromFormat(), setPageFormat()
	 * @access public
	 */
	public function startPage($orientation='', $format='', $tocpage=false) {
		if ($this->numpages > 0) $this->tMargin = 10;
		if ($tocpage) {
			$this->tocpage = true;
		}
		if ($this->numpages > $this->page) {
			// this page has been already added
			$this->setPage($this->page + 1);
			//added conditional to this line, 3/2/11, ctranel
			if($this->page == 1) $this->SetY($this->tMargin);
			return;
		}
		// start a new page
		if ($this->state == 0) {
			$this->Open();
		}
		++$this->numpages;
		$this->swapMargins($this->booklet);
		// save current graphic settings
		$gvars = $this->getGraphicVars();
		// start new page
		$this->_beginpage($orientation, $format);
		// mark page as open
		$this->pageopen[$this->page] = true;
		// restore graphic settings
		$this->setGraphicVars($gvars);
		// mark this point
		$this->setPageMark();
		// print page header
		//added conditional to this line, 3/2/11, ctranel
		if ($this->numpages == 1) $this->setHeader();
		// restore graphic settings
		$this->setGraphicVars($gvars);
		// mark this point
		$this->setPageMark();
		// print table header (if any)
		$this->make_table_header();
		// set mark for empty page check
		$this->emptypagemrk[$this->page]= $this->pagelen[$this->page];
		// Color and font restoration to values used in data table
		list($p1, $p2, $p3) = $this->gsg_fill_color;
		$this->SetFillColor($p1, $p2, $p3);
		$this->SetTextColor($this->gsg_text_color);
		$this->SetFont($this->_config['page_font'], '');
	}
	/**
	 * checkPageBreak Add page if needed.  Overrides parent class
	 * @param $h (float) Cell height. Default value: 0.
	 * @param $y (mixed) starting y position, leave empty for current position.
	 * @param $addpage (boolean) if true add a page, otherwise only return the true/false state
	 * @return boolean true in case of page break, false otherwise.
	 * @access protected
	 */
	protected function checkPageBreak($h=0, $y='', $addpage=true) {
		if ($this->empty_string($y)) {
			$y = $this->y;
		}
		$current_page = $this->page;
		if ((($y + $h) > $this->PageBreakTrigger) AND (!$this->InFooter) AND ($this->AcceptPageBreak())) {
			if ($addpage) {
				//Automatic page break
				$x = $this->x;
				$this->AddPage($this->CurOrientation);
				//added conditional to this line, 3/2/11, ctranel
				if($this->numpages == 1) $this->y = $this->tMargin;
				$oldpage = $this->page - 1;
				if ($this->rtl) {
					if ($this->pagedim[$this->page]['orm'] != $this->pagedim[$oldpage]['orm']) {
						$this->x = $x - ($this->pagedim[$this->page]['orm'] - $this->pagedim[$oldpage]['orm']);
					} else {
						$this->x = $x;
					}
				} else {
					if ($this->pagedim[$this->page]['olm'] != $this->pagedim[$oldpage]['olm']) {
						$this->x = $x + ($this->pagedim[$this->page]['olm'] - $this->pagedim[$oldpage]['olm']);
					} else {
						$this->x = $x;
					}
				}
			}
			return true;
		}
		if ($current_page != $this->page) {
			// account for columns mode
			return true;
		}
		return false;
	}

	/**
	 * Header() This method is used to render the page header.
	 * It is automatically called by AddPage() and overwrites the parent class function.
	 * @access public
	 * @return void
	 */
	public function Header() {
		$ormargins = $this->getOriginalMargins();
		$headerfont = $this->getHeaderFont();
		$headerdata = $this->getHeaderData();
		if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
			$imgtype = $this->getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
			if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
				$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
			} elseif ($imgtype == 'svg') {
				$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
			} else {
				$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
			}
			$imgy = $this->getImageRBY();
		} else {
			$imgy = $this->GetY();
		}
		// modify cell height to be the height of the image cell -- ctranel, 3/2/11
		$cell_height = round(($this->getCellHeightRatio() * $headerfont[2]) / $this->getScaleFactor(), 2);//$imgy - $this->GetY();
		// set starting margin for text data cell
		if ($this->getRTL()) {
			$header_x = $ormargins['right'] + ($headerdata['logo_width'] * 1.1);
		} else {
			$header_x = $ormargins['left'] + ($headerdata['logo_width'] * 1.1);
		}
		$this->SetTextColor($this->gsg_text_color);
		// header title
		$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
		$this->SetX($header_x);
		$this->Cell(0, $cell_height, $this->page_title, 0, 1, 'R', 0, '', 0, NULL, '', 'B');
		// header string
		$this->SetFont($this->_config['page_font'], 'N', ($this->_config['page_font_size'] * 1.25));
		$this->SetX($header_x);
		$header_y = $this->GetY();
		//$herd_text = '';
		$farm_name = '';
		$herd_owner = '';
		$test_date = '';
		//$herd_text = isset($this->arr_herd_data->herd_code) && !empty($this->arr_herd_data->herd_code)?$this->arr_herd_data->herd_code :'';
		if(is_object($this->arr_herd_data)){
			$farm_name = $this->arr_herd_data->farm_name;
			$herd_owner = $this->arr_herd_data->herd_owner;
			$test_date = $this->arr_herd_data->test_date;
		}
		elseif(is_array($this->arr_herd_data)){
			$farm_name = $this->arr_herd_data['farm_name'];
			$herd_owner = $this->arr_herd_data['herd_owner'];
			$test_date = $this->arr_herd_data['test_date'];
		}
		$this->MultiCell(0, 3, 'Farm Name: ' . $farm_name, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
		$this->MultiCell(0, 3, 'Herd Owner: ' . $herd_owner, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
		$this->MultiCell(0, 3, 'Test Date: ' . $test_date, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
		$this->MultiCell(0, 4, 'Consultant: ' . $this->consultant, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
//		$this->MultiCell(0, 3, $this->bench_text, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
		
		$sort_order_text = $this->arr_sort_order[0] == "DESC"?'descending':'ascending';
		//$this->SetY($header_y);
		
		if(isset($this->sort_text) && !empty($this->sort_text)) $this->MultiCell(0, 3, $this->sort_text, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
		if(is_array($this->arr_filter_text)){
			foreach($this->arr_filter_text as $ft){
				$this->MultiCell(0, 3, $ft, 0, 'R', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
			}
		}
		$this->SetY(38);
		$this->SetFont($this->_config['page_font'], 'I', $this->_config['page_font_size']);
		$this->MultiCell(0, 3.5, $this->bench_text, 0, 'C', 0, 1, '', '', TRUE, 0, FALSE, TRUE);
	}


	/**
	 * Footer() This method is used to render the page footer.
	 * It is automatically called by AddPage() and overwrites the parent class function.
	 * @access public
	 * @return void
	 * 
	 */
	public function Footer() {
		$cur_y = $this->GetY();
		$ormargins = $this->getOriginalMargins();
		$this->SetTextColor($this->gsg_text_color);
		//$this->Image(K_PATH_IMAGES.$this->footer_logo, '81', '264', $this->footer_logo_width);
		//$this->Image(K_PATH_IMAGES.'cargill-genex-s.gif',100,270);
		$this->Ln();
		$this->Cell(0, 0, $this->unichr(169) . date('Y'), '0', 0, 'L');
		/*set style for cell border
		$line_width = 0.85 / $this->getScaleFactor();
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0))); */
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->getPageWidth() - $ormargins['left'] - $ormargins['right']) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128B', '', $cur_y + $line_width, '', (($this->getFooterMargin() / 3) - $line_width), 0.3, $style, '');
		}
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->l['w_page'].' '.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $this->l['w_page'].' '.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		//Print page number
		if ($this->getRTL()) {
			$this->SetX($ormargins['right']);
			$this->Cell(0, 0, $pagenumtxt, '0', 0, 'L');
		} else {
			$this->SetX($ormargins['left']);
			$this->Cell(0, 0, $pagenumtxt, '0', 0, 'R');
		}
	}
	
}
