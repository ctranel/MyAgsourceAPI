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
     *  @return: valid 12 digit SireId
     *  @throws: Exception
     * -----------------------------------------------------------------*/
    public static function formatOfficialId(\Animal_model $datasource, $sire_id){
        $sire_id = substr($sire_id, -12);
        /*
        $letters = preg_replace("/[0-9]+/", "", $sire_id);

        if(!empty($letters)){
            throw new \Exception('Non-numeric characters are not allowed in official IDs.');
        }
*/
        if(strlen($sire_id) < 12){
            $sire_id = str_pad($sire_id, 12, "0", STR_PAD_LEFT);
        }

        return $sire_id;
    }
}
