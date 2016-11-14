<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Events_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

    /**
     * @method eventData()
     * @param int event id
     * @return array of event data
     * @access public
     *
     **/
	public function eventData($id){
	    $id = (int)$id;

	    $res = $this->db
            ->select("[herd_code],[serial_num],[event_cd_text],[edit_cd],[cost_df],[meat_df],[milk_df],[pen_df],[site_df],[comment_df],[body_df],[ID],[event_cd],[event_cat],[event_dt],[eventtm],[cost],[times_treat],[is_lf_qtr_treated],[is_rf_qtr_treated],[is_lr_qtr_treated],[is_rr_qtr_treated],[comment],[sire_breed_cd],[sire_country_cd],[sire_id],[sire_reg_num],[sire_name],[sire_naab],[preg_stat],[preg_stat_dt],[tech_id],[preg_result_cd],[conception_dt],[days_bred],[breeding_type],[data_source],[open_reason_cd],[times_bred_seq],[logID],[logdttm],[body_wt],[height],[condition_score],[withhold_meat_dt],[withhold_milk_dt],[pen_num],[siteID]")
            ->where('ID', $id)
            ->get('TD.animal.vma_event_data')
            ->result_array();

        if(isset($res[0]) && is_array($res[0])){
            return $res[0];
        }
    }

    /**
     * @method getEventsBetweenDates()
     * @param string early date
     * @param string late date
     * @param array of event codes
     * @return array of event data
     * @access public
     *
     **/
    public function getEventsBetweenDates($herd_code, $serial_num, $early_date, $late_date, $event_codes = null){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;
        $early_date = MssqlUtility::escape($early_date);
        $late_date = MssqlUtility::escape($late_date);
        if(isset($event_codes) && is_array($event_codes)){
            array_walk($event_codes, function(&$v, $k){return (int)$v;});
        }


        if(isset($event_codes) && is_array($event_codes)){
            $this->db->where_in('event_cd', $event_codes);
        }

        $res = $this->db
            ->select("[herd_code],[serial_num],[event_cd_text],[edit_cd],[cost_df],[meat_df],[milk_df],[pen_df],[site_df],[comment_df],[body_df],[ID],[event_cd],[event_cat],[event_dt],[eventtm],[cost],[times_treat],[is_lf_qtr_treated],[is_rf_qtr_treated],[is_lr_qtr_treated],[is_rr_qtr_treated],[comment],[sire_breed_cd],[sire_country_cd],[sire_id],[sire_reg_num],[sire_name],[sire_naab],[preg_stat],[preg_stat_dt],[tech_id],[preg_result_cd],[conception_dt],[days_bred],[breeding_type],[data_source],[open_reason_cd],[times_bred_seq],[logID],[logdttm],[body_wt],[height],[condition_score],[withhold_meat_dt],[withhold_milk_dt],[pen_num],[siteID]")
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->where('event_dt >=', $early_date)
            ->where('event_dt <=', $late_date)
            ->get('TD.animal.vma_event_data')
            ->result_array();

        if(isset($res[0]) && is_array($res[0])){
            return $res[0];
        }
    }

    /**
     * @method currentLactationStartDate
     * @param string herd code
     * @param int serial num
     * @return datetime string
     * @access public
     *
     **/
    public function currentLactationStartDate($herd_code, $serial_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;
        $res = $this->db
            ->select("TopFreshDate")
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->get('[TD].[animal].[animal_event_eligibility]')
            ->result_array();

        if(isset($res[0]) && is_array($res[0])){
            return $res[0]['TopFreshDate'];
        }
    }

    public function getEligibleAnimals($herd_code, $conditions){
        $herd_code = MssqlUtility::escape($herd_code);
        if(isset($conditions) && is_array($conditions)){
            array_walk($conditions, function(&$v, $k){
                //var_dump();
                $v = 'e.' . MssqlUtility::escape($v[0]) . ' ' . MssqlUtility::escape($v[1]) . ' ' . MssqlUtility::escape($v[2]);
            });
        }

        $conditions = implode(' AND ', $conditions);


        $res = $this->db
            ->select('id.serial_num, id.chosen_id')
            ->from('TD.animal.vma_animal_options id')
            ->join('TD.animal.animal_event_eligibility e', 'id.herd_code = e.herd_code AND id.serial_num = e.serial_num', 'inner')
            ->where('id.herd_code', $herd_code)
            ->where($conditions)
            ->get()
            ->result_array();

        if(isset($res) && is_array($res)){
            return $res;
        }
    }

    /**
	 * @method eventEligibilityData()
	 * @param string herd code
     * @param int serial_num
     * @param string event date
	 * @return array of data for determining event eligibility
	 * @access public
	 *
	 **/
	public function eventEligibilityData($herd_code, $serial_num, $event_date){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;
        $event_date = MssqlUtility::escape($event_date);

		if (!isset($herd_code) || !isset($serial_num) || !isset($event_date)){
			throw new Exception('Missing required information');
		}

		$sql = $this->eligibilitySql($herd_code, $serial_num, $event_date);

        $ret = $this->db->query($sql)->result_array();

        if(isset($ret[0]) && is_array($ret[0])){
            return $ret[0];
        }
	}

    /**
     * @method eligibilitySql()
     * @param string herd code
     * @param int serial_num
     * @param string event date
     * @return array of data for determining event eligibility
     * @access public
     *
     **/
    protected function eligibilitySql($herd_code, $serial_num, $event_date){
        $sql = "SELECT
                herd_code
                ,serial_num
                ,is_active
                ,species_cd
                ,sex_cd
                ,birth_dt
                ,CASE WHEN [TopFreshDate] IS NULL THEN 1 ELSE 0 END AS is_youngstock
                ,CASE WHEN earliest_dry_date IS NULL THEN NULL
                    ELSE (SELECT Max(v) FROM (VALUES (earliest_dry_date), (DATEADD(day, 1, [TopStatusDate]))) AS value(v))
                    END AS earliest_dry_eligible_date
                ,CASE WHEN earliest_fresh_date IS NULL THEN NULL
                    ELSE (SELECT Max(v) FROM (VALUES (earliest_fresh_date), (DATEADD(day, 1, [TopStatusDate]))) AS value(v))
                    END AS earliest_fresh_eligible_date
                ,CASE WHEN earliest_abort_date IS NULL THEN NULL
                    ELSE (SELECT Max(v) FROM (VALUES (earliest_abort_date), (DATEADD(day, 1, [TopStatusDate]))) AS value(v))
                    END AS earliest_abort_eligible_date
                ,CASE WHEN earliest_preg_date IS NULL THEN NULL
                    ELSE (SELECT Max(v) FROM (VALUES (earliest_preg_date), (DATEADD(day, 1, [TopStatusDate]))) AS value(v))
                    END AS earliest_preg_eligible_date
                ,CASE WHEN earliest_repro_eligible_date IS NULL THEN NULL
                    ELSE (SELECT Max(v) FROM (VALUES (earliest_repro_eligible_date), (DATEADD(day, 1, [TopStatusDate]))) AS value(v))
                    END AS earliest_repro_eligible_date
                ,[TopStatusDate]
                ,[TopFreshDate]
                ,[TopBredDate]
                ,[TopDryDate]
                ,[TopSoldDiedDate]
                ,current_status
                ,is_bred
            FROM (
                 SELECT
                  id.herd_code
                  ,id.serial_num
                  ,id.is_active
                  ,id.species_cd
                  ,id.sex_cd
                  ,id.birth_dt
                  ,CASE
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopFreshDate] IS NOT NULL THEN --cow
                        CASE WHEN id.species_cd = 'C' AND te.[TopDryDate] = te.[TopStatusDate] THEN DATEADD(day, 251, te.[TopFreshDate])
                            WHEN id.species_cd = 'G' AND te.[TopDryDate] = te.[TopStatusDate] THEN DATEADD(day, 136, te.[TopFreshDate])
                            END
                    WHEN te.[TopFreshDate] IS NULL THEN --heifer (no fresh date)
                        CASE WHEN id.species_cd = 'C' THEN DATEADD(day, 501, id.birth_dt)
                            WHEN id.species_cd = 'G' THEN DATEADD(day, 251, id.birth_dt)
                            END
                    END AS earliest_fresh_date
            
                  ,CASE 
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopBredDate] IS NOT NULL AND te.[TopBredDate] > te.[TopFreshDate] THEN DATEADD(day, 153, te.[TopBredDate]) --if bred
                    END AS earliest_abort_date
                  
                  ,CASE
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopFreshDate] IS NOT NULL THEN DATEADD(day, 1, te.[TopFreshDate])
                    END AS earliest_dry_date
                  
                  ,CASE
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopDryDate] IS NOT NULL AND te.[TopDryDate] = te.[TopStatusDate] THEN DATEADD(day, 1, te.[TopFreshDate])
                    END AS earliest_dry_donor_date
                  
                  ,CASE 
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopSoldDiedDate] IS NULL THEN DATEADD(day, 1, te.[TopStatusDate])
                    END AS earliest_solddied_date
            
                  ,CASE
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopBredDate] > te.[TopFreshDate] THEN DATEADD(day, 27, te.[TopBredDate])
                    END AS earliest_preg_date
            
                  ,CASE
                    WHEN id.is_active = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopFreshDate] IS NOT NULL THEN DATEADD(day, 1, te.[TopFreshDate])--cow
                    WHEN te.[TopFreshDate] IS NULL THEN --heifer (no fresh date)
                        CASE WHEN id.species_cd = 'C' THEN DATEADD(day, 301, id.birth_dt)
                            WHEN id.species_cd = 'G' THEN DATEADD(day, 201, id.birth_dt)
                            END
                    END AS earliest_repro_eligible_date
            
                  ,te.[TopStatusDate]
                  ,te.[TopFreshDate]
                  ,te.[TopBredDate]
                  ,te.[TopDryDate]
                  ,te.[TopSoldDiedDate]
            
                  ,CASE WHEN te.[TopSoldDiedDate] = te.[TopStatusDate] THEN 'Sold/Died'
                    WHEN te.[TopDryDate] = te.[TopStatusDate] THEN 'Dry'
                    WHEN te.[TopFreshDate] = te.[TopStatusDate] THEN 'In Milk'
                    END AS current_status
            
                  ,CASE WHEN te.[TopBredDate] > te.[TopFreshDate] THEN 1
                    ELSE 0
                    END AS is_bred
            
                 FROM TD.animal.ID id
                 LEFT JOIN(
                    SELECT herd_code, serial_num 
                        ,(SELECT Max(v) FROM (VALUES (tp.cat21_event_dt), (tp.cat27_event_dt), (tp.cat28_event_dt)) AS value(v)) as [TopStatusDate]
                        ,tp.cat28_event_dt as [TopDryDate]
                        ,tp.cat32_event_dt as [TopBredDate]
                        ,tp.cat27_event_dt as [TopFreshDate]
                        ,tp.cat21_event_dt as [TopSoldDiedDate]
                    FROM (" . $this->topEventsByAnimalSql($herd_code, $serial_num, $event_date) . ") tp
                ) te ON id.herd_code = te.herd_code AND id.serial_num = te.serial_num
            
            WHERE id.herd_code = '$herd_code' AND id.serial_num = $serial_num
            ) c";
//echo $sql;
        return $sql;
    }

    /**
     * @method topEventsByAnimalSql()
     * @param string herd code
     * @param int serial_num
     * @param string event date
     * @return string of SQL
     * @access public
     *
     **/
    protected function topEventsByAnimalSql($herd_code, $serial_num, $event_date){
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

    /**
     * @method topEventsSql()
     * @param string herd code
     * @param int serial_num
     * @param string event date
     * @return string of SQL
     * @access public
     *
     **/
    protected function topEventsByCatSql($herd_code, $serial_num, $event_date){
        $sql = "SELECT herd_code,serial_num, event_cat, event_cd, event_dt 
            FROM (
				SELECT  row_number() OVER (PARTITION BY a.herd_code, a.serial_num, b.event_cat 
				 ORDER BY a.event_dt DESC,a.ID DESC) AS rowid
				, a.herd_code, a.serial_num, b.event_cat, a.event_cd, a.event_dt 
				 FROM td.animal.events a 
					INNER JOIN td.herd.events b 
						ON a.event_cd = b.event_cd 
						AND a.herd_code = b.herd_code
						AND a.event_dt <= DATEADD(second,-1,'" . $event_date . "')
			) as d
			WHERE d.rowid = 1";
        return $sql;
    }
}
