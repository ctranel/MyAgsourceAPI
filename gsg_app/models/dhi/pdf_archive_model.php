<?php
class Pdf_archive_model extends CI_Model {
    protected $tables;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @method getHerdArchiveData()
     * @param string herd_code
     * @return array of data for the rdf report file
     * @access public
     *
     **/
    function getHerdArchiveData($herd_code){
        $test_input = (int)$herd_code;
        if (!$test_input || strlen($herd_code) != 8){
            throw new Exception('Invalid Herd Code');
        }
        // results query
        $q = $this->db->select("p.id, p.test_date, p.report_code, p.filename, r.report_name")
            ->from('herd.dbo.herd_pdf_report_log p')
            ->join('dhi_tables.dbo.report_catalog r', 'p.report_code=r.report_code')
            ->where('p.herd_code',$herd_code)
            ->distinct()
            ->order_by('p.test_date DESC, r.report_name ASC');
        $ret = $q->get()->result_array();

        return $ret;
    } //end function

    /**
     * @method getPdfData()
     * @param int PDF File id
     * @return array of data for the pdf report file
     * @access public
     *
     **/
    function getPdfData($pdf_id, $herd_code){
        $pdf_id = (int)$pdf_id;
        if (!$pdf_id){
            throw  new Exception('Invalid PDF ID');
        }
        $test_input = (int)$herd_code;
        if (!$test_input || strlen($herd_code) != 8){
            throw  new Exception('Invalid Herd Code');
        }
        // results query
        $q = $this->db->select("p.herd_code, p.test_date, p.report_code, p.filename, r.report_name")
            ->from('herd.dbo.herd_pdf_report_log p')
            ->join('dhi_tables.dbo.report_catalog r', 'p.report_code=r.report_code')
            ->where('p.id',$pdf_id)
            ->where('p.herd_code',$herd_code);
        $ret = $q->get()->result_array();
        if(empty($ret)){
            throw new Exception('PDF not found');
        }
        return $ret[0];
    } //end function
}
