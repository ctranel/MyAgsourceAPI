<?php
namespace myagsource\Page\Content\ReportBlock;

require_once APPPATH . 'libraries/Site/iBlock.php';
require_once APPPATH . 'libraries/Page/iReportBlock.php';

use \myagsource\Page\iReportBlock;
use \myagsource\Report\iReport;
use \myagsource\Site\iBlock;
use \myagsource\Supplemental\Content\SupplementalFactory;

/**
 * Name:  ReportBlock
 *
 * Author: ctranel
 *
 * Created:  02-02-2015
 *
 * Description:  Contains properties and methods specific to displaying blocks of the website.
 *
 */

class ReportBlock implements iReportBlock {
    /**
     * iBlock object which contains properties and methods related to the block context within the site
     * @var iBlock
     **/
    protected $site_block;

    /**
     * supplemental
     * @var Supplemental
     **/
    protected $supplemental;

    /**
     * supplemental_factory
     * @var SupplementalFactory
     **/
    protected $supplemental_factory;

    /**
     * __construct
     *
     * @return void
     * @author ctranel
     **/
    public function __construct(/*$block_datasource, */iBlock $site_block, iReport $report, SupplementalFactory $supp_factory){
        //$this->datasource = $block_datasource;
        $this->site_block = $site_block;
        $this->report = $report;

        $this->supplemental_factory = $supp_factory;
        $this->supplemental = $supp_factory->getBlockSupplemental($this->site_block->id());
    }

    public function toArray(){
        $ret = $this->report->toArray() + $this->site_block->toArray();
        return $ret;
    }

    /*
     * a slew of getters

    public function id(){
        return $this->site_block->id();
    }

    public function path(){
        return $this->site_block->path();
    }

    public function title(){
            return $this->site_block->name();
        }
        public function maxRows(){
            return $this->report->maxRows();
        }
        public function pivotFieldName(){
            return $this->report->pivotFieldName();
        }

        public function primaryTableName(){
            return $this->report->primaryTableName();
        }

        public function sorts(){
            return $this->report->sorts();
        }
    */

    /*
        public function joins(){
            return $this->joins;
        }
 
    public function name(){
        return $this->site_block->name();
    }

    public function description(){
        return $this->site_block->description();
    }
*/
    public function displayType(){
        return $this->site_block->displayType();
    }

    public function hasBenchmark(){
        return $this->report->hasBenchmark();
    }

    /*
        public function subtitle(){
            return $this->report->subtitle();>hasBenchmark();
        }

        public function isSummary(){
            return $this->report->isSummary();
        }

        public function fieldGroups(){
            return $this->report->fieldGroups();
        }

        public function hasCntRow(){
            return $this->report->hasCntRow();
        }

        public function hasAvgRow(){
            return $this->report->hasAvgRow();
        }

        public function hasSumRow(){
            return $this->report->hasSumRow();
        }

        public function hasPivot(){
            return $this->report->hasPivot();
        }

        public function reportFields(){
            return $this->report->reportFields();
        }

        public function numFields(){
            return $this->report->numFields();
        }

        public function filterKeysValues(){
            return $this->report->filterKeysValues();
        }

        public function getFieldlistArray(){
            if(!isset($this->report_fields) || count($this->report_fields) === 0){
                return false;
            }
            $ret = [];
            foreach($this->report_fields as $f){
                $ret[] = $f->dbFieldName();
            }
            return $ret;
        }

        public function getDisplayedFieldArray(){
            if(!isset($this->report_fields) || count($this->report_fields) === 0){
                return false;
            }
            $ret = [];
            foreach($this->report_fields as $f){
                if($f->isDisplayed()){
                    $ret[] = $f->displayName();
                }
            }
            return $ret;
        }

        public function getAppendedRowsCount(){
            return $this->appended_rows_count;
        }
    */

    /**
     * addFieldName
     *
     * Add a field name to be included in the data query.  Will not create a new series
     *
     * @return void
     * @access public
     *
    public function addFieldName($name){
        $this->addl_select_field_names[] = $name;
    }
**/

    /**
     * @method getOutputData
     *
     * Returns data needed by original non-API version of site
     *
     * @return array of output data for block
     * @access public
     *
    public function getOutputData(){
        return [
            'name' => $this->site_block->name(),
            'description' => $this->site_block->name(),
            'filter_text' => $this->filters->get_filter_text(),
            'block' => $this->site_block->path(), //original program included sort_by, sort_order, graph_order but couldn't find anywhere it was used
            'block_id' => $this->site_block->id(),
        ];
    }
**/

    /**
     * @method setDefaultSort()
     * @return void
     * @author ctranel
    public function setDefaultSort(){
        $this->report->setDefaultSort();
    }
**/

    /**
     * @method getFieldTable()
     * @param field name
     * @return string table name
     * @access public
     *
     * @todo: change this to return tables for all fields and iterate where it is called?
    public function getFieldTable($field_name){
        return $this->report->getFieldTable($field_name);
    }
* */

    /**
     * @method isNaturalSort()
     * @param field name
     * @return boolean
     * @access public
     *
    public function isNaturalSort($field_name){
        return $this->report->isNaturalSort($field_name);
    }
* */

    /**
     * @method isNaturalSort()
     * @param field name
     * @return boolean
     * @access public
     *
    public function isSortable($field_name){
        return $this->report->isSortable($field_name);
    }
* */

    /**
     * @method getSelectFields()
     *
     * Retrieves fields designated as select, supplemental params (if set),
     *
     * @return string table name
     * @access public
    public function getSelectFields(){
        return $this->report->getSelectFields();
    }
* */

    /**
     * @method getGroupBy()
     * //@param array of GroupBy objects
     * @return void
     * @access public
    public function getGroupBy(){
        return $this->report->getGroupBy();
    }

    public function defaultSort(){
        return $this->report->defaultSort();
    }

    public function displayBenchRow(){
        return $this->report->displayBenchRow();
    }
* */

    /**
     * setDataset
     *
     * sets the objects dataset property with report data
     * @access public
    protected function setDataset(){
        $this->report->setDataset();
    }
* */
}


