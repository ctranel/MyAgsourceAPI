<?php
namespace myagsource\dhi;

/**
* Name:  HerdEvent
*
* Author: ctranel
*		  ctranel@agsource.com
*
* Location: na
*
* Created:  2016-10-20
*
* Description:  Library for managing herd events
*
* Requirements: PHP5 or above
*
*/

class HerdEvents
{
	/**
	 * datasource
	 *
	 * @var \Events_model
	 **/
	protected $datasource;

    /**
     * herd identifier
     *
     * @var string
     **/
    protected $herd_code;

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
     *  topBredDate
     *
     *  @author: ctranel
     *  @date: 2017-05-18
     * @param: int serial num
     *  @return: string date
     *  @throws: Exception
     * -----------------------------------------------------------------*/
    public function getHerdDefaultValues($event_cd, $serial_num){
        if(empty($serial_num)){
            throw new \Exception('Animal information is not specified');
        }

        $defaults = $this->datasource->getHerdDefaultValues($this->herd_code, $event_cd);

        if((int)$event_cd === 33 && isset($serial_num) && !is_array($serial_num) && !empty($serial_num)){
            $conception_dt = $this->topBredDate($serial_num);
            if(!empty($conception_dt)){
                $defaults['conception_dt'] = $conception_dt;
            }
        }

        return $defaults;
    }


    /* -----------------------------------------------------------------
     *  topBredDate
     *
     *  @author: ctranel
     *  @date: 2017-05-18
     * @param: int serial num
     *  @return: string date
     *  @throws: Exception
     * -----------------------------------------------------------------*/
    protected function topBredDate($serial_num){
        if(empty($serial_num)){
            throw new \Exception('Animal information is not specified');
        }

        $res = $this->datasource->topBredDate($this->herd_code, $serial_num);

        return $res;
    }

}
