<?php
require_once APPPATH . 'models/ReportContent/report_data_model.php';

use \myagsource\MssqlUtility;
use \myagsource\Report\iReport;
use \myagsource\Settings\Settings;

class Todo_list_model extends Report_data_model {

    protected $settings;

	public function __construct(Settings $settings){
		parent::__construct();
        $this->settings = $settings;
	}

    /**
     * @method search()

     * overrides parent search function

     * @param iReport
     * @param array select fields
     * @param array filter criteria
     * @return array results of search
     * @author ctranel
     *
     **/

    public function search(iReport $report, $select_fields, $arr_filter_criteria){
        //look ahead days was used to derive other filter values.  We don't want to use it in query
        unset($arr_filter_criteria['look_ahead_days']);

        $this->composeSearch($report, $select_fields, $arr_filter_criteria);

        $report_date = date('Y-m-d');
        $herd_code = $arr_filter_criteria['herd_code']['value'][0];

        $sql = $this->db->get_compiled_select();
//die($sql);
        if(in_array("2", $this->settings->getValue('open_cows'))){
            $sql .= " UNION " . $this->openCowSql($herd_code, $report_date, $this->settings->getValue('open_cows_days'));
        }

        if(in_array("2", $this->settings->getValue('open_heifers'))){
            $sql .= " UNION " . $this->openHeiferSql($herd_code, $report_date, $this->settings->getValue('open_heifers_age'));
        }

        if(in_array("2", $this->settings->getValue('cycle_check'))){
            $sql .= " UNION " . $this->bredCycleSql($herd_code, $report_date, $this->settings->getValue('cycle_days_min'), $this->settings->getValue('cycle_days_max'));
        }

        if(in_array("2", $this->settings->getValue('repeat_breedings'))){
            $sql .= " UNION " . $this->maxTimesBred($herd_code, $report_date, $this->settings->getValue('breeding_number'));
        }

        if(in_array("2", $this->settings->getValue('non_cycling'))){
            $sql .= " UNION " . $this->nonCyclingCows($herd_code, $report_date, $this->settings->getValue('non_cycle_days'));
        }

        $ret = $this->db->query($sql)->result_array();
        return $ret;
    }

    /**
     * @method openCowSql()
     *
     * @param string herd code
     * @param string report date
     * @param int cow open days target
     * @return string sql text
     * @access public
     *
     **/
    public function openCowSql($herd_code, $report_date, $open_cows_days) {
        $sql = "
            SELECT
                serial_num
                , NULL AS seq_satisfying_event_cd
                , chosen_id
                , visible_id
                , pen_num
                , DATEDIFF(DAY, y.TopFreshDate, '" . $report_date . "') AS dim_age
                , curr_lact_num
                ,'Cow is Open' AS attention
                , NULL AS target_date
                , NULL AS expires_date
                , CONCAT(DATEDIFF(DAY, y.TopFreshDate, '" . $report_date . "'), ' Days in Milk') AS comment
            FROM TD.[animal].[vma_vet_check_DAL_data] y
            WHERE
                herd_code = '" . $herd_code . "'
                AND curr_lact_num > 0
                AND TopPregStatusCd != 33
                AND TopStatusDate = TopFreshDate
                AND TopPregStatusDate > TopBredDate
                AND DATEDIFF(DAY, TopFreshDate, '" . $report_date . "') > " . $open_cows_days;

        return $sql;
    }

    /**
     * @method openHeiferSql()
     *
     * @param string herd code
     * @param string report date
     * @param int heifer age target
     * @return string sql text
     * @access public
     *
     **/
    public function openHeiferSql($herd_code, $report_date, $heifer_open_months) {
        $sql = "
            SELECT 
                serial_num
                , NULL AS seq_satisfying_event_cd
                , chosen_id
                , visible_id
                , pen_num
                , DATEDIFF(MONTH, y.birth_dt, '" . $report_date . "') AS dim_age
                , curr_lact_num
                ,'Heifer is Open' AS attention
                , NULL AS target_date
                , NULL AS expires_date
                , CONCAT('OPEN ', DATEDIFF(MONTH, y.birth_dt, '" . $report_date . "'), ' Months') AS comment
            FROM TD.[animal].[vma_vet_check_DAL_data] y
            WHERE
            herd_code = '" . $herd_code . "'
            AND curr_lact_num = 0
            AND sex_cd = 1
            AND TopPregStatusCd != 33
            AND TopPregStatusDate > TopBredDate
            AND DATEDIFF(MONTH, birth_dt, '" . $report_date . "') > " . $heifer_open_months;

        return $sql;
    }

    /**
     * @method bredCycleSql()
     *
     * @param string herd code
     * @param string report date
     * @param int cycle days min
     * @param int cycle days max
     * @return string sql text
     * @access public
     *
     **/
    public function bredCycleSql($herd_code, $report_date, $cycle_days_min, $cycle_days_max) {
        $sql = "
            SELECT 
                serial_num
                , NULL AS seq_satisfying_event_cd
                , chosen_id
                , visible_id
                , pen_num
                , DATEDIFF(DAY, y.TopFreshDate, '" . $report_date . "') AS dim_age
                , curr_lact_num
                ,'Watch Cow for Heat' AS attention
                , NULL AS target_date
                , NULL AS expires_date
                , CASE 
                    WHEN TopReproStatusCd = 31 THEN CONCAT('Last Heat ', DATEDIFF(MONTH, y.TopHeatDate, '" . $report_date . "'), ' days')
                    ELSE CONCAT('Last Breeding ', DATEDIFF(DAY, y.TopBredDate, '" . $report_date . "'), ' days')
                    END AS comment
            FROM TD.[animal].[vma_vet_check_DAL_data] y
            WHERE
            herd_code = '" . $herd_code . "'
            AND TopPregStatusCd != 33
            AND TopReproStatusCd != 39
            AND (
                (
                    TopBredCd IS NOT NULL
                    AND DATEDIFF(DAY, TopBredDate, '" . $report_date . "') > " . $cycle_days_min . "
                    AND DATEDIFF(DAY, TopBredDate, '" . $report_date . "') < " . $cycle_days_max . "
                )
                OR
                (
                    TopReproStatusCd = 31
                    AND DATEDIFF(DAY, TopReproStatusDate, '" . $report_date . "') > " . $cycle_days_min . "
                    AND DATEDIFF(DAY, TopReproStatusDate, '" . $report_date . "') < " . $cycle_days_max . "
                )
            )";
//die($sql);
        return $sql;
    }

    /**
     * @method maxTimesBred()
     *
     * @param string herd code
     * @param string report date
     * @param int max number of breedings
     * @return string sql text
     * @access public
     *
     **/
    public function maxTimesBred($herd_code, $report_date, $breeding_number) {
        $sql = "
            SELECT 
                serial_num
                , NULL AS seq_satisfying_event_cd
                , chosen_id
                , visible_id
                , pen_num
                , DATEDIFF(MONTH, y.birth_dt, '" . $report_date . "') AS dim_age
                , curr_lact_num
                ,'Reproductive Problem' AS attention
                , NULL AS target_date
                , NULL AS expires_date
                , CONCAT('Bred ', curr_lact_bred_cnt, ' times and not preganant') AS comment
            FROM TD.[animal].[vma_vet_check_DAL_data] y
            WHERE
            herd_code = '" . $herd_code . "'
            AND TopPregStatusCd != 33
            AND TopPregStatusDate > TopBredDate
            AND curr_lact_bred_cnt >= " . $breeding_number;

        return $sql;
    }

    /**
     * @method nonCyclingCows()
     *
     * @param string herd code
     * @param string report date
     * @return string sql text
     * @access public
     *
     **/
    public function nonCyclingCows($herd_code, $report_date, $target_dim) {
        $sql = "
            SELECT
                serial_num
                , NULL AS seq_satisfying_event_cd
                , chosen_id
                , visible_id
                , pen_num
                , DATEDIFF(DAY, y.TopFreshDate, '" . $report_date . "') AS dim_age
                , curr_lact_num
                ,'No Breedings or Heats' AS attention
                , NULL AS target_date
                , NULL AS expires_date
                , CONCAT('DIM ', DATEDIFF(DAY, TopFreshDate, '" . $report_date . "'), ' days') AS comment
            FROM TD.[animal].[vma_vet_check_DAL_data] y
            WHERE
                herd_code = '" . $herd_code . "'
                AND curr_lact_num > 0
                AND (TopReproStatusDate < TopFreshDate OR TopReproStatusDate IS NULL)
                AND DATEDIFF(DAY, TopFreshDate, '" . $report_date . "') >= " . $target_dim;

        return $sql;
    }


    /**
     * @method VetCheckDALEventsSql()
     *
     * text of TD.[animal].[vma_vet_check_DAL_data] view

     * @param string herd code
     * @return array of data for determining event eligibility
     * @access public
     *
	public function VetCheckDALEventsSql($herd_code){
        $sql = "
            SELECT 	
            id.herd_code
            ,id.serial_num
            ,id.is_active
            ,id.species_cd
            ,id.sex_cd
            ,id.birth_dt
            ,id.curr_lact_num
            ,te.TopBredDate
            ,te.TopDryDate
            ,te.TopFreshDate
            ,te.TopPregStatusCd
            ,te.TopPregStatusDate
            ,te.TopReproStatusCd
            ,te.TopReproStatusDate
            ,te.TopSoldDiedDate
            ,te.TopStatusCd
            ,te.TopStatusDate
            ,te.TopBredCd
            ,te.TopBredDate
      	    ,te.TopHeatDate
  
             FROM TD.animal.ID id
             LEFT JOIN(
                    SELECT d.herd_code, d.serial_num 
                    ,(SELECT Max(v) FROM (VALUES (d.cat21_event_dt), (d.cat27_event_dt), (d.cat28_event_dt)) AS value(v)) as [TopStatusDate]
                    ,CASE
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat21_event_dt), (d.cat27_event_dt), (d.cat28_event_dt)) AS value(x)) = d.cat21_event_dt) THEN d.cat21_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat21_event_dt), (d.cat27_event_dt), (d.cat28_event_dt)) AS value(x)) = d.cat27_event_dt) THEN d.cat27_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat21_event_dt), (d.cat27_event_dt), (d.cat28_event_dt)) AS value(x)) = d.cat28_event_dt) THEN d.cat28_event_cd
                    END as TopStatusCd
                    ,(SELECT Max(v) FROM (VALUES (d.cat25_event_dt), (d.cat39_event_dt)) AS value(v)) as [TopPregStatusDate]
                    ,CASE 
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat25_event_dt) THEN d.cat25_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat39_event_dt) THEN d.cat39_event_cd
                    END AS TopPregStatusCd 
                    ,(SELECT Max(v) FROM (VALUES (d.cat32_event_dt), (d.cat36_event_dt)) AS value(v)) as [TopBredDate]
                    ,CASE 
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat32_event_dt), (d.cat36_event_dt)) AS value(x)) = d.cat32_event_dt) THEN d.cat32_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat32_event_dt), (d.cat36_event_dt)) AS value(x)) = d.cat36_event_dt) THEN d.cat36_event_cd
                    END AS TopBredCd 
        		    ,d.cat31_event_dt as [TopHeatDate]

        
                    ,(SELECT Max(v) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(v)) as [TopReproStatusDate]
                    ,CASE 
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat25_event_dt) THEN d.cat25_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat30_event_dt) THEN d.cat30_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat31_event_dt) THEN d.cat31_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat32_event_dt) THEN d.cat32_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat36_event_dt) THEN d.cat36_event_cd
                        WHEN ((SELECT Max(x) FROM (VALUES (d.cat25_event_dt), (d.cat30_event_dt), (d.cat31_event_dt), (d.cat32_event_dt), (d.cat36_event_dt), (d.cat39_event_dt)) AS value(x)) = d.cat39_event_dt) THEN d.cat39_event_cd
                    END AS TopReproStatusCd 
        
        
                    ,d.cat28_event_dt as [TopDryDate]
                    ,d.cat27_event_dt as [TopFreshDate]
                    ,d.cat21_event_dt as [TopSoldDiedDate]
                FROM (" . $this->topEventsByAnimalSql($herd_code) . ") d
             ) te ON id.herd_code = te.herd_code AND id.serial_num = te.serial_num
        
        WHERE id.herd_code = '" . $herd_code . "'";

        return $sql;
    }
     **/

    /**
     * @method topEventsByAnimalSql()
     *
     * text of [TD].[animal].[vma_top_events_by_animal] view
     *
     * @param string herd code
     * @param int serial_num
     * @param string event date
     * @return string of SQL
     * @access public
     *
    protected function topEventsByAnimalSql($herd_code, $serial_num = null, $event_date = null){
        $sql = "SELECT a.herd_code, a.serial_num, 
			MAX(CASE WHEN b.event_cat = '1' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat1_event_cd , MAX(CASE WHEN b.event_cat = '1' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat1_event_dt 
			,MAX(CASE WHEN b.event_cat = '2' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat2_event_cd , MAX(CASE WHEN b.event_cat = '2' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat2_event_dt 
			,MAX(CASE WHEN b.event_cat = '3' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat3_event_cd , MAX(CASE WHEN b.event_cat = '3' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat3_event_dt 
			,MAX(CASE WHEN b.event_cat = '4' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat4_event_cd , MAX(CASE WHEN b.event_cat = '4' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat4_event_dt 
			,MAX(CASE WHEN b.event_cat = '5' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat5_event_cd , MAX(CASE WHEN b.event_cat = '5' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat5_event_dt 
			,MAX(CASE WHEN b.event_cat = '6' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat6_event_cd , MAX(CASE WHEN b.event_cat = '6' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat6_event_dt 
			,MAX(CASE WHEN b.event_cat = '7' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat7_event_cd , MAX(CASE WHEN b.event_cat = '7' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat7_event_dt 
			,MAX(CASE WHEN b.event_cat = '8' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat8_event_cd , MAX(CASE WHEN b.event_cat = '8' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat8_event_dt 
			,MAX(CASE WHEN b.event_cat = '9' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat9_event_cd , MAX(CASE WHEN b.event_cat = '9' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat9_event_dt 
			,MAX(CASE WHEN b.event_cat = '10' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat10_event_cd , MAX(CASE WHEN b.event_cat = '10' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat10_event_dt 
			,MAX(CASE WHEN b.event_cat = '11' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat11_event_cd , MAX(CASE WHEN b.event_cat = '11' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat11_event_dt 
			,MAX(CASE WHEN b.event_cat = '12' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat12_event_cd , MAX(CASE WHEN b.event_cat = '12' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat12_event_dt 
			,MAX(CASE WHEN b.event_cat = '13' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat13_event_cd , MAX(CASE WHEN b.event_cat = '13' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat13_event_dt 
			,MAX(CASE WHEN b.event_cat = '14' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat14_event_cd , MAX(CASE WHEN b.event_cat = '14' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat14_event_dt 
			,MAX(CASE WHEN b.event_cat = '15' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat15_event_cd , MAX(CASE WHEN b.event_cat = '15' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat15_event_dt 
			,MAX(CASE WHEN b.event_cat = '16' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat16_event_cd , MAX(CASE WHEN b.event_cat = '16' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat16_event_dt 
			,MAX(CASE WHEN b.event_cat = '17' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat17_event_cd , MAX(CASE WHEN b.event_cat = '17' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat17_event_dt 
			,MAX(CASE WHEN b.event_cat = '18' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat18_event_cd , MAX(CASE WHEN b.event_cat = '18' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat18_event_dt 
			,MAX(CASE WHEN b.event_cat = '21' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat21_event_cd , MAX(CASE WHEN b.event_cat = '21' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat21_event_dt 
			,MAX(CASE WHEN b.event_cat = '22' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat22_event_cd , MAX(CASE WHEN b.event_cat = '22' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat22_event_dt 
			,MAX(CASE WHEN b.event_cat = '23' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat23_event_cd , MAX(CASE WHEN b.event_cat = '23' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat23_event_dt 
			,MAX(CASE WHEN b.event_cat = '24' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat24_event_cd , MAX(CASE WHEN b.event_cat = '24' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat24_event_dt 
			,MAX(CASE WHEN b.event_cat = '25' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat25_event_cd , MAX(CASE WHEN b.event_cat = '25' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat25_event_dt 
			,MAX(CASE WHEN b.event_cat = '27' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat27_event_cd , MAX(CASE WHEN b.event_cat = '27' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat27_event_dt 
			,MAX(CASE WHEN b.event_cat = '28' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat28_event_cd , MAX(CASE WHEN b.event_cat = '28' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat28_event_dt 
			,MAX(CASE WHEN b.event_cat = '30' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat30_event_cd , MAX(CASE WHEN b.event_cat = '30' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat30_event_dt 
			,MAX(CASE WHEN b.event_cat = '31' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat31_event_cd , MAX(CASE WHEN b.event_cat = '31' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat31_event_dt 
			,MAX(CASE WHEN b.event_cat = '32' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat32_event_cd , MAX(CASE WHEN b.event_cat = '32' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat32_event_dt 
			,MAX(CASE WHEN b.event_cat = '35' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat35_event_cd , MAX(CASE WHEN b.event_cat = '35' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat35_event_dt 
			,MAX(CASE WHEN b.event_cat = '36' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat36_event_cd , MAX(CASE WHEN b.event_cat = '36' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat36_event_dt 
			,MAX(CASE WHEN b.event_cat = '39' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_cd ELSE NULL END) as cat39_event_cd , MAX(CASE WHEN b.event_cat = '39' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  THEN a.event_dt ELSE NULL END) as cat39_event_dt 
			,MAX(CASE WHEN b.event_cat = '25' AND a.event_dt = c.event_dt and a.event_cd = c.event_cd  AND a.event_cd = 33 THEN a.conception_dt ELSE NULL END) as conception_dt 
			FROM  td.animal.events a 
			inner join td.herd.events b 
			on a.event_cd = b.event_cd 
			AND a.herd_code = b.herd_code 
			INNER JOIN (" .

            $this->topEventsByCatSql($herd_code, $serial_num, $event_date)

            . ") c on a.herd_code = c.herd_code and a.serial_num = c.serial_num 
			GROUP BY a.herd_code, a.serial_num";
        return $sql;
    }
     **/

    /**
     * @method topEventsByCatSql()
     * @param string herd code
     * @param int serial_num
     * @param string event date
     * @return string of SQL
     * @access public
     *
    protected function topEventsByCatSql($herd_code, $serial_num = null, $event_date = null){
        $sql = "SELECT herd_code,serial_num, event_cat, event_cd, event_dt 
            FROM (
				SELECT  row_number() OVER (PARTITION BY a.herd_code, a.serial_num, b.event_cat 
				 ORDER BY a.event_dt DESC,a.ID DESC) AS rowid
				, a.herd_code, a.serial_num, b.event_cat, a.event_cd, a.event_dt 
				 FROM td.animal.events a 
					INNER JOIN td.herd.events b 
						ON a.event_cd = b.event_cd 
						AND a.herd_code = b.herd_code
						AND a.herd_code = '" . $herd_code . "'";
                        if(isset($event_date) && !empty($event_date)) {
                            $sql .= " AND a.event_dt <= DATEADD(day,-1,'" . $event_date . "')";
                        }
                        if(isset($serial_num) && !empty($serial_num)) {
                            $sql .= " AND a.serial_num = " . $serial_num;
                        }
        $sql .= ") as d
			WHERE d.rowid = 1";
        return $sql;
    }
 **/

}
