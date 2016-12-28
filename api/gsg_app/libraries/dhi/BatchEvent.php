<?php
namespace myagsource\dhi;

/**
* Name:  BatchEvent
*
* Author: ctranel
*		  ctranel@agsource.com
*
* Location: na
*
* Created:  2016-11-09
*
* Description:  Library for managing animal events
*
* Requirements: PHP5 or above
*
*/

class BatchEvent
{
	/**
	 * datasource
	 *
	 * @var datasource
	 **/
	protected $datasource;

    /**
     * herd identifier
     *
     * @var string
     **/
    protected $herd_code;

    /**
     * eligible messages
     *
     * @var array of strings
     **/
    protected $eligible_messages;

    /**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\Events_model $datasource, $herd_code) {
		if(empty($herd_code) || strlen($herd_code) != 8){
			throw new \Exception('Herd could not be loaded.  No herd code passed to constructor.');
		}
		$this->herd_code = $herd_code;
        $this->datasource = $datasource;
	}

    /* -----------------------------------------------------------------
     *  eligibleMessage

     *  Returns array messages regarding animals eligibility for passed event

     *  @author: ctranel
     *  @date: 2016-10-21
     *  @return: string or null
     *  @throws:
     * -----------------------------------------------------------------*/

    public function eligibleMessage(){
        if(isset($this->eligible_messages)){
            return $this->eligible_messages;
        }
        return null;
    }

    /* -----------------------------------------------------------------
     *  eligibleAnimals

     *

     *  @author: ctranel
     *  @date: 2016-11-09
     *  @return: key=>value array of animals eligible for event on given date
     *  @throws:
     * -----------------------------------------------------------------*/

    public function eligibleAnimals($event_cd, $event_dt){
        if(!isset($event_cd) || empty($event_cd)){
            throw new \BadMethodCallException('No event code passed to function.');
        }

        $now = new \DateTime();
        $event_dt = isset($event_dt) ? new \DateTime($event_dt) : null;

        if($event_cd == 22 || $event_cd == 23 || $event_cd == 24 || $event_cd == 29){
            $this->eligible_messages[] = "Event entered is an internal event and cannot be keyed.";
        }
        if(isset($event_dt) && $event_dt > $now){
            $this->eligible_messages[] = "Cannot enter events with date or time in the future.";
        }

        //BELOW CONDITIONS ARE USED TO BUILD QUERY
        $conditions = [
            ['isactive', '=', '1'],
            ['topSoldDiedDate', 'IS', 'NULL']
        ];

        if($event_cd < 21 || $event_cd > 28){
            //@todo: custom events need to be allowed
            $conditions[] = ['sex_cd', '!=', '2'];
        }

        //Fresh events
        if($event_cd == 1 || $event_cd == 2){
            if($event_cd == 1){
                $conditions[] = ['is_youngstock', '=', '0'];
            }
            if($event_cd == 1){
                $conditions[] = ['is_youngstock', '=', '1'];
            }

            if($event_dt instanceof DateTime){
                $conditions[] = ['earliest_fresh_eligible_date', '<=',  $event_dt->format('Y-m-d')];
            }
            else{
                $conditions[] = ['earliest_fresh_eligible_date', 'IS NOT', 'NULL'];
            }
        }

        //Abort events
        if($event_cd == 5){
            if($event_dt instanceof DateTime){
                $conditions[] = ['earliest_abort_eligible_date', '<=',  $event_dt->format('Y-m-d')];
            }
            else{
                $conditions[] = ['earliest_abort_eligible_date', 'IS NOT', 'NULL'];
            }
        }

        //Dry events
        if($event_cd == 6 || $event_cd == 10){
            if($event_dt instanceof DateTime){
                $conditions[] = ['earliest_dry_eligible_date', '<=',  $event_dt->format('Y-m-d')];
            }
            else{
                $conditions[] = ['earliest_dry_eligible_date', 'IS NOT', 'NULL'];
            }
        }

        //Repro Eligible
        $repro_eligible_codes = [30,31,34,35,38,39,40,32,36];
        if(in_array($event_cd, $repro_eligible_codes)){
            if($event_dt instanceof DateTime){
                $conditions[] = ['earliest_repro_eligible_date', '<=',  $event_dt->format('Y-m-d')];
            }
            else{
                $conditions[] = ['earliest_repro_eligible_date', 'IS NOT', 'NULL'];
            }
        }

        //Preg events
        if($event_cd == 33){
            if($event_dt instanceof DateTime){
                $conditions[] = ['earliest_preg_eligible_date', '<=',  $event_dt->format('Y-m-d')];
            }
            else{
                $conditions[] = ['earliest_preg_eligible_date', 'IS NOT', 'NULL'];
            }
        }
        //END BUILD QUERY

        //if error message is set, no animals are eligible for this event
        if(isset($this->eligible_messages) && count($this->eligible_messages) > 0){
            return false;
        };

        $animals = $this->datasource->getEligibleAnimals($this->herd_code, $conditions);

        if(!$animals || count($animals) < 1){
            $this->eligible_messages[] = "No animals are eligible for the selected event on the selected date.";
            return false;
        }

        $return = [];
        foreach($animals as $c){
            $return[] = (Object)[$c['serial_num'] => $c['chosen_id']];
        }

        return $return;
    }
}
