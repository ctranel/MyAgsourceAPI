<?php
namespace myagsource\dhi;
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



class PdfArchives
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


    /**
     * getAllHerdArchives
     *
     * @return array reflecting structure of pdf archives (test_date-->report)
     * @author ctranel
     **/
    public function getAllHerdArchives(){
        $archives = $this->datasource->getHerdArchiveData($this->herd_code);
        return $this->getHerdArchives($archives);
    }

    /**
     * getSubscribedHerdArchives
     *
     * @return array reflecting structure of pdf archives (test_date-->report)
     * @author ctranel
     **/
    public function getSubscribedHerdArchives(){
        $archives = $this->datasource->getSubscribedHerdArchiveData($this->herd_code);
        return $this->getHerdArchives($archives);
    }

    /**
     * getHerdArchives
     *
     * @param array raw dataset
     * @return array reflecting structure of pdf archives (test_date-->report)
     * @author ctranel
     **/
    protected function getHerdArchives($data){
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

        return $tests;
    }
}