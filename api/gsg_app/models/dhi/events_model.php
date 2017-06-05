<?php
require_once APPPATH . 'libraries/MssqlUtility.php';

use \myagsource\MssqlUtility;

class Events_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

    /**
     * @method eventData()
     * @return array of event data
     * @access public
     *
     **/
	public function eventData(){
	    $res = $this->db
            ->select("[herd_code],[serial_num],[event_cd_text],[edit_cd],[cost_df],[meat_df],[milk_df],[pen_df],[site_df],[comment_df],[body_df],[animal_event_id],[event_cd],[event_cat],[event_dt],[eventtm],[cost],[times_treat],[is_lf_qtr_treated],[is_rf_qtr_treated],[is_lr_qtr_treated],[is_rr_qtr_treated],[comment],[sire_breed_cd],[sire_country_cd],[sire_id],[sire_reg_num],[sire_name],[sire_naab],[preg_stat],[preg_stat_dt],[preg_result_cd],[conception_dt],[days_bred],[breeding_type],[open_reason_cd],[times_bred_seq],[logID],[logdttm],[body_wt],[height],[condition_score],[withhold_meat_dt],[withhold_milk_dt],[pen_num],[siteID]")
            ->get('TD.animal.vmat_event_data')
            ->result_array();

        if($res === false){
            throw new \Exception('Event data not found.');
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])) {
            return $res[0];
        }

        return [];
    }

    /**
     * @method eventDataById()
     * @param int event id
     * @return array of event data
     * @access public
     *
     **/
    public function eventDataById($id){
        $id = (int)$id;

        $this->db->where('animal_event_id', $id);

        return $this->eventData();
    }

    /**
     * @method existingEventData()
     *
     * Returns array of event data matching herd, animal, event and event date or null
     *
     * @param string herd code
     * @param int serial_num
     * @param int event code
     * @param string event date
     * @return array of event data
     * @access public
     *
     **/
    public function existingEventData($herd_code, $serial_num, $event_cd, $event_date){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;
        $event_cd = (int)$event_cd;
        $event_date = MssqlUtility::escape($event_date);

        if (!isset($herd_code) || !isset($serial_num) || !isset($event_cd) || !isset($event_date)){
            throw new Exception('Missing required information');
        }

        if(isset($serial_num)){
            $this->db->where('serial_num', $serial_num);
        }

        $this->db
            ->where('herd_code', $herd_code)
            ->where('event_cd', $event_cd)
            ->where('event_dt', $event_date);

        return $this->eventData();
    }

    /**
     * @method getHerdDefaultValues()
     * @param string herd code
     * @param int event code
     * @return array of key -> value data
     * @access public
     *
     **/
    public function getHerdDefaultValues($herd_code, $event_code){
        $event_code = (int)$event_code;
        $herd_code = MssqlUtility::escape($herd_code);

        $res = $this->db
            ->select("
              e.event_cat
              ,e.[cost_df] AS cost
              ,e.[meat_df] AS withhold_meat_days
              ,e.[milk_df] AS withhold_milk_days
              ,e.[pen_df] AS pen_num
              ,p.[siteID] AS siteID
              ,e.[comment_df] AS comment
              ,e.[body_df] AS body_df
            ")
            ->join('TD.herd.pens p', 'e.pen_df = p.pen_num AND e.herd_code = p.herd_code', 'left')
            ->where('e.herd_code', $herd_code)
            ->where('e.event_cd', $event_code)
            ->where('e.isactive', 1)
            ->get("[TD].[herd].[events] e")
            ->result_array();

        if($res === false){
            throw new \Exception('Event defaults: ' . $this->db->_error_message());
        }

        if(isset($res[0]) && is_array($res[0])){
            //Get default treatments for the event
            $tx = $this->db
                ->where('herd_code', $herd_code)
                ->where('event_cd', $event_code)
                ->where('isactive', 1)
                ->order_by('list_order')
                ->get('[TD].[herd].[event_rxtx]')
                ->result_array();

            if($tx === false){
                throw new \Exception('Treatment defaults failed: ' . $this->db->_error_message());
            }
            if(isset($tx[0]) && is_array($tx[0])){
                $res[0]['treatments'] = $tx;
            }


            return $res[0];
        }

        return [];
    }

    /**
     * @method getOffspringDefaultValues()
     * @param int event id
     * @return array of key -> value data
     * @access public
     *
     **/
    public function getOffspringDefaultValues($event_id){
        $event_id = (int)$event_id;

        $res = $this->db
            ->select("
                   ISNULL((SELECT TOP 1 calf_control_num FROM TD.animal.calving WHERE herd_code=e.herd_code AND sex_cd = 1 ORDER BY id DESC), 0) AS last_cntl_num_female
                  ,ISNULL((SELECT TOP 1 calf_control_num FROM TD.animal.calving WHERE herd_code=e.herd_code AND sex_cd = 2 ORDER BY id DESC), 0) AS last_cntl_num_male
                  ,((SELECT TOP 1 control_num FROM TD.animal.id WHERE herd_code=e.herd_code ORDER BY serial_num DESC) + 1) AS next_highest_cntl_num

                  ,e.[event_dt] AS calving_dt
                  ,e.serial_num
                  ,COALESCE(emb.[sire_breed_cd], be.[sire_breed_cd], bs.breed_cd) AS sire_breed_cd
                  ,COALESCE(emb.[sire_country_cd], be.[sire_country_cd], bs.country_cd) AS sire_country_cd
                  ,COALESCE(emb.[sire_bull_id], be.[sire_id], bs.bull_id) AS sire_id
                  ,be.[sire_reg_num]
                  ,COALESCE(emb.[sire_name], be.[sire_name], bs.name) AS sire_name
                  ,COALESCE(emb.[sire_naab], be.[sire_naab], bs.naab) AS sire_naab
                  ,be.[conception_dt]
                  ,be.[breeding_type]
                  ,be.[breeding_sireID]
                  ,be.[embryoid]
                  ,emb.[donor_country_cd] AS [donor_dam_country_cd]
                  ,emb.[donor_officialid] AS [donor_dam_id]
                  ,emb.[donor_serial_num] AS [donor_dam_serial_num]
                  ,emb.[donor_name]
                  ,COALESCE(emb.donor_breed_cd, be.sire_breed_cd) AS calf_breed_cd
            ")
//            ->join('TD.animal.events be', 'e.herd_code = be.herd_code AND e.serial_num = be.serial_num AND e.conception_dt = be.event_dt AND be.event_cd IN(32,36)', 'left')
            ->join('TD.animal.vma_offspring_conception_breedings be', 'e.herd_code = be.herd_code AND e.serial_num = be.serial_num', 'left')
            ->join('TD.herd.embryos emb', 'be.embryoid = emb.id AND be.herd_code = emb.herd_code', 'left')
            ->join('TD.herd.breeding_sires bs', 'be.breeding_sireID = bs.id AND be.herd_code = bs.herd_code', 'left')
            ->where('e.id', $event_id)
            ->order_by('be.conception_dt', 'desc')
            ->order_by('be.event_dt', 'desc')
            ->get("[TD].[animal].[events] e")
            ->result_array();

        if($res === false){
            throw new \Exception('Event defaults: ' . $this->db->_error_message());
        }

        if(isset($res[0]) && is_array($res[0])){
            //Get default treatments for the event
            return $res[0];
        }

        return [];
    }

    /**
     * @method topBredDate()
     * @param string herd code
     * @param int serial num
     * @return array of cow id data.
     * @access public
     *
     **/

    public function topBredDate($herd_code, $serial_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;

        $result = $this->db
            ->select('TopBredDate')
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->where('isactive', 1)
            ->get('[TD].[animal].[vma_animal_event_eligibility]')
            ->result_array();
        if(is_array($result) && isset($result[0]) && is_array($result[0])){
            return $result[0]['TopBredDate'];
        }

        return null;
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
            ->select("[herd_code],[serial_num],[event_cd_text],[edit_cd],[cost_df],[meat_df],[milk_df],[pen_df],[site_df],[comment_df],[body_df],[animal_event_id],[event_cd],[event_cat],[event_dt],[eventtm],[cost],[times_treat],[is_lf_qtr_treated],[is_rf_qtr_treated],[is_lr_qtr_treated],[is_rr_qtr_treated],[comment],[sire_breed_cd],[sire_country_cd],[sire_id],[sire_reg_num],[sire_name],[sire_naab],[preg_stat],[preg_stat_dt],[breeding_tech_id],[preg_result_cd],[conception_dt],[days_bred],[breeding_type],[open_reason_cd],[times_bred_seq],[logID],[logdttm],[body_wt],[height],[condition_score],[withhold_meat_dt],[withhold_milk_dt],[pen_num],[siteID]")
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->where('event_dt >=', $early_date)
            ->where('event_dt <=', $late_date)
            ->get('TD.animal.vmat_event_data')
            ->result_array();

        if(isset($res[0]) && is_array($res[0])){
            return $res[0];
        }
    }

    /**
     * @method currentLactationStartDate
     * @param string herd code
     * @param int serial num
     * @return string date
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
            ->get('[TD].[animal].[vma_animal_event_eligibility]')
            ->result_array();

        if(isset($res[0]) && is_array($res[0])){
            return $res[0]['TopFreshDate'];
        }
    }

    /**
     * @method activeEventPeriodStartDate
     * @param string herd code
     * @param int serial num
     * @return string date
     * @access public
     *
     **/
    public function activeEventPeriodStartDate($herd_code, $serial_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;
        $res = $this->db
            ->select("COALESCE([TopFreshDate], birth_dt) AS active_event_period_start_dt")
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->where('isactive', 1)
            ->get('[TD].[animal].[vma_animal_event_eligibility]')
            ->result_array();

        if(isset($res[0]) && is_array($res[0])){
            return $res[0]['active_event_period_start_dt'];
        }
    }

    public function getEligibleAnimals($herd_code, $event_cd, $event_dt, $conditions){
        $herd_code = MssqlUtility::escape($herd_code);
        if(isset($conditions) && is_array($conditions)){
            array_walk($conditions, function(&$v, $k){
                if(MssqlUtility::escape($v[2]) === 'NULL'){
                    $this->db->where('e.' . MssqlUtility::escape($v[0]) . ' ' . MssqlUtility::escape($v[1]) . ' ' . MssqlUtility::escape($v[2]));
                }
                else{
                    $this->db->where('e.' . MssqlUtility::escape($v[0]) . ' ' . MssqlUtility::escape($v[1]), MssqlUtility::escape($v[2]));
                }
            }, $this->db);
        }

        $res = $this->db
            ->select('id.serial_num, id.chosen_id')
            ->from('TD.animal.vma_animal_options id')
            ->join('TD.animal.vma_animal_event_eligibility e', 'id.herd_code = e.herd_code AND id.serial_num = e.serial_num', 'inner')
            ->where('id.herd_code', $herd_code)
            ->where("NOT EXISTS(SELECT 1 FROM TD.animal.events WHERE herd_code = id.herd_code AND serial_num = id.serial_num AND event_cd = " . $event_cd . " AND event_dt = '" . $event_dt . "')")
            ->get()
            ->result_array();

        if(isset($res) && is_array($res)){
            return $res;
        }
    }

    /**
     * @method getConceptionDate()
     *
     * @param string herd code
     * @param int serial_num
     * @return date conception date
     * @access public
     *
     **/
    public function getConceptionDate($herd_code, $serial_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;

        if (!isset($herd_code) || !isset($serial_num)){
            throw new Exception('Missing required information');
        }

        $res = $this->db
            ->select('TOP 1 conception_dt')
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
            ->order_by('event_dt', 'desc')
            ->get('[TD].[animal].[vmat_event_data]')
            ->result_array();

        if($res === false){
            return null;
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])) {
            return $res[0]['conception_dt'];
        }

        return null;
    }

    /**
     * @method embryoCost()
     *
     * @param string herd code
     * @param int embryo_id
     * @return decimal cost of given embryo
     * @access public
     *
     **/
    public function embryoCost($herd_code, $embryo_id){
        $herd_code = MssqlUtility::escape($herd_code);
        $embryo_id = (int)$embryo_id;

        if (!isset($herd_code) || !isset($embryo_id)){
            throw new Exception('Missing required information');
        }

        $res = $this->db
            ->select('TOP 1 unit_cost')
            ->where('herd_code', $herd_code)
            ->where('embryoid', $embryo_id)
            ->where('[transaction_type]', 'P')
            ->order_by('transaction_dttm', 'desc')
            ->get('[TD].[herd].[vmat_embryo_trans]')
            ->result_array();

        if($res === false){
            return null;
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }
        if(isset($res[0]) && is_array($res[0])) {
            return $res[0]['unit_cost'];
        }

        return null;
    }

    /**
     * @method sireCost()
     *
     * @param string herd code
     * @param int sire_id
     * @return decimal cost of given embryo
     * @access public
     *
     **/
    public function sireCost($herd_code, $sire_id){
        $herd_code = MssqlUtility::escape($herd_code);
        $sire_id = (int)$sire_id;

        if (!isset($herd_code) || !isset($sire_id)){
            throw new Exception('Missing required information');
        }

        $res = $this->db
            ->select('TOP 1 unit_cost')
            ->where('herd_code', $herd_code)
            ->where('breeding_sireID', $sire_id)
            ->where('[transaction_type]', 'P')
            ->order_by('transaction_dttm', 'desc')
            ->get('[TD].[herd].[vmat_breeding_sire_trans]')
            ->result_array();

        if($res === false){
            return null;
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }
        if(isset($res[0]) && is_array($res[0])) {
            return $res[0]['unit_cost'];
        }

        return null;
    }

    /**
	 * @method eventEligibilityData()
     *
	 * @param string herd code
     * @param int serial_num
     * @param string event date
	 * @return array of data for determining event eligibility
	 * @access public
	 *
	 **/
	public function eventEligibilityData($herd_code, $serial_num){
        $herd_code = MssqlUtility::escape($herd_code);
        $serial_num = (int)$serial_num;

		if (!isset($herd_code) || !isset($serial_num)){
			throw new Exception('Missing required information');
		}

		//$sql = $this->eligibilitySql($herd_code, $serial_num, $event_date);
        //$ret = $this->db->query($sql)->result_array();

        $res = $this->db
            ->where('herd_code', $herd_code)
            ->where('serial_num', $serial_num)
//            ->where('event_dt', $event_date)
            ->get('[TD].[animal].[vma_animal_event_eligibility]')
            ->result_array();

        if($res === false){
            throw new \Exception('Event eligibility data not found.');
        }
        $err = $this->db->_error_message();

        if(!empty($err)){
            throw new \Exception($err);
        }

        if(isset($res[0]) && is_array($res[0])) {
            return $res[0];
        }

        return [];
	}

    /**
     * @method eligibilitySql()

     * This is the same as TD.[animal].[vma_animal_event_eligibility], but is broken out so that subqueries can use herd code and serial num to limit dataset size
     *
     * @param string herd code
     * @param int serial_num
     * @param string event date
     * @return array of data for determining event eligibility
     * @access public
     *

     * MAKE SURE SQL MATCHES VIEW BEFORE ENABLING THIS FUNCTION
     * protected function eligibilitySql($herd_code, $serial_num, $event_date){
        $sql = "SELECT
                herd_code
                ,serial_num
                ,isactive
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
                  ,id.isactive
                  ,id.species_cd
                  ,id.sex_cd
                  ,id.birth_dt
                  ,CASE
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
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
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN id.curr_lact_bred_cnt > 0 THEN DATEADD(day, 153, te.[TopBredDate]) --if bred
                    END AS earliest_abort_date
                  
                  ,CASE
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopFreshDate] IS NOT NULL AND te.[TopFreshDate] > te.[TopDryDate] THEN DATEADD(day, 1, te.[TopFreshDate])
                    END AS earliest_dry_date
                  
                  ,CASE
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopDryDate] IS NOT NULL AND te.[TopDryDate] = te.[TopStatusDate] THEN DATEADD(day, 1, te.[TopFreshDate])
                    END AS earliest_dry_donor_date
                  
                  ,CASE 
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN te.[TopSoldDiedDate] IS NULL THEN DATEADD(day, 1, te.[TopStatusDate])
                    END AS earliest_solddied_date
            
                  ,CASE
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
                    WHEN id.curr_lact_bred_cnt > 0 THEN DATEADD(day, 27, te.[TopBredDate])
                    END AS earliest_preg_date
            
                  ,CASE
                    WHEN id.isactive = 0 OR te.[TopSoldDiedDate] IS NOT NULL THEN NULL
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
            
                  ,CASE WHEN id.curr_lact_bred_cnt > 0 THEN 1
                    ELSE 0
                    END AS is_bred
            
                 FROM TD.animal.ID id
                 LEFT JOIN(
                    SELECT herd_code, serial_num 
                        ,(SELECT Max(v) FROM (VALUES (tp.cat21_event_dt), (tp.cat27_event_dt), (tp.cat28_event_dt)) AS value(v)) as [TopStatusDate]
                        ,tp.cat28_event_dt as [TopDryDate]
			            ,(SELECT Max(v) FROM (VALUES (tp.cat32_event_dt), (tp.cat36_event_dt)) AS value(v)) as [TopBredDate]
                        ,tp.cat27_event_dt as [TopFreshDate]
                        ,tp.cat21_event_dt as [TopSoldDiedDate]
                    FROM (" . $this->topEventsByAnimalSql($herd_code, $serial_num, $event_date) . ") tp
                ) te ON id.herd_code = te.herd_code AND id.serial_num = te.serial_num
            
            WHERE id.herd_code = '$herd_code' AND id.serial_num = $serial_num
            ) c";
//echo $sql;
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
     * @method topEventsSql()
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
