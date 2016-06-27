<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: ctranel
 * Date: 6/20/2016
 * Time: 2:37 PM
 */
class Form_Model extends CI_Model
{
    /**
     * form id
     * @var int
     **/
    protected $form_id;

    /**
     * herd code
     * @var string
     **/
    protected $herd_code;

    /**
     * user id
     * @var int
     **/
    protected $user_id;

    public function __construct($form_id, $herd_code, $user_id){
        $this->form_id = $form_id;
        $this->herd_code = $herd_code;
        $this->user_id = $user_id;
    }

    /**
     * @method getFormData
     * @return array of block data
     * @author ctranel
     **/
    public function getFormData() {
        $this->db
            ->select('b.id, pb.page_id, b.name,b.[description],b.path,dt.name AS display_type,s.name AS scope,ct.name as chart_type,b.max_rows,b.cnt_row,b.sum_row,b.avg_row,b.pivot_db_field,b.bench_row,b.is_summary,b.active,b.keep_nulls')
            ->where('b.active', 1)
            ->join('users.dbo.lookup_display_types dt', 'b.display_type_id = dt.id', 'inner')
            ->join('users.dbo.lookup_scopes s', 'b.scope_id = s.id', 'inner')
            ->join('users.dbo.pages_blocks pb', 'b.id = pb.block_id', 'inner')
            ->join('users.dbo.lookup_chart_types ct', 'b.chart_type_id = ct.id', 'left')
            ->order_by('list_order', 'asc')
            ->from($this->tables['blocks'] . ' b');
        return $this->db->get()->result_array();
    }


}