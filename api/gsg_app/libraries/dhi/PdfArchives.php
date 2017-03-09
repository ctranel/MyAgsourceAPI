<?php
namespace myagsource\dhi;

require_once(APPPATH . 'libraries/Site/iBlockContent.php');

use myagsource\Site\iBlockContent;

/**
 * Name:  HerdAccess
 *
 * Author: ctranel
 *
 * Created:  03-29-2016
 *
 * Description:  Provides information about a user's access to herds.
 *
 * Requirements: PHP5.2 or above
 */



class PdfArchives implements iBlockContent
{
    /**
     * datasource
     * @var object
     **/
    protected $datasource;

    /**
     * herd_code
     * @var string of numbers
     **/
    protected $herd_code;

    /**
     * __construct
     *
     * @return void
     * @author ctranel
     **/
    public function __construct($datasource, $herd_code) {
        $test_input = (int)$herd_code;
        if (!$test_input || strlen($herd_code) != 8){
            throw  new Exception('Invalid Herd Code');
        }
        $this->herd_code = $herd_code;
        $this->datasource = $datasource;
    }

    public function toArray() {
        return $this->dataset;
    }

    /**
     * getAllHerdArchives
     *
     * @return array reflecting structure of pdf archives (test_date-->report)
     * @author ctranel
     **/
    public function setAllHerdArchives(){
        $archives = $this->datasource->getHerdArchiveData($this->herd_code);
        $this->setHerdArchives($archives);
    }

    /**
     * getSubscribedHerdArchives
     *
     * @return array reflecting structure of pdf archives (test_date-->report)
     * @author ctranel
     **/
    public function setSubscribedHerdArchives(){
        $archives = $this->datasource->getSubscribedHerdArchiveData($this->herd_code);
        $this->setHerdArchives($archives);
    }

    /* -----------------------------------------------------------------
*  keyMetaArray

*  returns field-name-keyed array with meta data for keys

*  @author: ctranel
*  @date: Jul 1, 2014
*  @param: array of key=>value pairs that have been processed by the parseFormData static function
*  @return key->value array of keys meta data
*  @throws: * -----------------------------------------------------------------
*/
    public function keyMetaArray(){
        return ['herd_code' => $this->herd_code];
    }

    /**
     * getHerdArchives
     *
     * @param array raw dataset
     * @return array reflecting structure of pdf archives (test_date-->report)
     * @author ctranel
     **/
    protected function setHerdArchives($data){
        $tests = [];
        $prev_test_date = '';
        $reports = [];
        foreach ($data as $a) {
            if($prev_test_date !== $a['test_date']){
                if($prev_test_date !== ''){
                    $tests[] = [
                        'test_date' => $prev_test_date,
                        'reports' => $reports,
                    ];
                }
                
                //set new test date and reset report array
                $prev_test_date = $a['test_date'];
                $reports = [];
            }
            $reports[] = [
                'id' => $a['id'],
                'text' => $a['report_name'],
            ];
        }

        if($prev_test_date !== ''){
            $tests[] = [
                'test_date' => $prev_test_date,
                'reports' => $reports,
            ];
        }

        $this->dataset = $tests;
    }
}