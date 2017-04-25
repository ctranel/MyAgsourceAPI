<?php
namespace myagsource\dhi;

/**
* Name:  Animal
*
* Author: ctranel
*		  ctranel@agsource.com
*
* Location: na
*
* Created:  2016-10-20
*
* Description:  Library for managing animals
*
* Requirements: PHP5 or above
*
*/

class Animal
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
     * serial number
     *
     * @var int
     **/
    protected $serial_num;

    /**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct(\Animal_model $datasource, $herd_code, $serial_num) {
		if(empty($herd_code) || strlen($herd_code) != 8){
			throw new \Exception('Animal could not be loaded.  No herd code passed to constructor.');
		}
		$this->herd_code = $herd_code;
		$this->serial_num = (int)$serial_num;
        $this->datasource = $datasource;
	}

    /* -----------------------------------------------------------------
     *  formatNAAB

     *

     *  @author: ctranel
     *  @date: 2016-10-20
     *  @return: valid NAAB
     *  @throws: Exception
     * -----------------------------------------------------------------*/
    public static function formatNAAB(\Animal_model $datasource, $naab, $species_code){
        $orig_breed = preg_replace("/[^a-zA-Z]+/", "", $naab);
        $breed = $orig_breed;

        if(strlen($orig_breed) == 1){
            $breed = $datasource->getNaabBreedCode($orig_breed, $species_code);
        }

        if(empty($breed) || strlen($breed) != 2){
            throw new \Exception('Invalid NAAB, breed code should be 2 characters.');
        }

        list($cntry, $id) = explode($orig_breed, $naab);
        $cntry = str_pad($cntry, 3, "0", STR_PAD_LEFT);
        $id = str_pad($id, 5, "0", STR_PAD_LEFT);

        return $cntry . $breed . $id;
    }

    /* -----------------------------------------------------------------
     *  formatSireId

     *

     *  @author: ctranel
     *  @date: 2016-10-20
     *  @return: array with country code (3 char) and id (12 char) elements
     *  @throws: Exception
     * -----------------------------------------------------------------*/
    public static function formatOfficialId($sire_id){
        $letters = preg_replace("/[0-9]+/", "", $sire_id);
        //$id = str_pad(substr($sire_id, -12), 12, "0", STR_PAD_LEFT);
        $id = substr($sire_id, -12);

        if(empty($letters) && strlen($sire_id) === 15){
            $country_cd = substr($sire_id, 0, 3);
            return [
                'country_cd' => $country_cd,
                'id' => $sire_id,
                'stored_id' => $id,
            ];
        }

        if(strlen($sire_id) > 12){
            throw new \Exception('ID cannot be more than 12 characters unless it is an RFID number.');
        }

        $ret = [
            'country_cd' => 'USA',
            'id' => $id,
            'stored_id' => str_pad($id, 12, '0', STR_PAD_LEFT)
        ];

        return $ret;
    }
}
