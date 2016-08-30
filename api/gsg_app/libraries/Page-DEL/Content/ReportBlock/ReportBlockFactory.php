<?php

namespace myagsource\Page\Content\ReportBlock;

use myagsource\Supplemental\Content\SupplementalFactory;
use \myagsource\Site\WebContent\WebBlockFactory;
use \myagsource\Report\Content\ReportFactory;

/**
 * A repository? for report block objects
 *
 *
 * @name ReportBlockFactory
 * @author ctranel
 *
 *
 */
class ReportBlockFactory {// implements iReportContentRepository {
    /**
     * datasource_blocks
     * @var report_block_model
     **/
    protected $datasource_blocks;

    /**
     * web_block_factory
     * @var WebBlockFactory
     **/
    protected $web_block_factory;

    /**
     * report_factory
     * @var ReportFactory
     **/
    protected $report_factory;

    /**
     * supplemental_factory
     * @var SupplementalFactory
     **/
    protected $supplemental_factory;

    function __construct(
        \report_block_model $datasource_blocks,
        WebBlockFactory $web_block_factory,
        ReportFactory $report_factory,
        SupplementalFactory $supplemental_factory = null
    ) {
        $this->datasource_blocks = $datasource_blocks;
        $this->web_block_factory = $web_block_factory;
        $this->report_factory = $report_factory;
        $this->supplemental_factory = $supplemental_factory;
    }

    /*
     * getBySection
     *
     * @param int page_id
     * @author ctranel
     * @returns array of Blocks
     */
    public function getByPage($page_id){
        $reports = [];

        $criteria = ['page_id' => $page_id];
//		$join = [['table' => 'pages_blocks pb', 'condition' => 'b.id = pb.block_id AND pb.page_id = ' . $page_id]];
        $results = $this->datasource_blocks->getByCriteria($criteria);
        if(empty($results)){
            return [];
        }
        foreach($results as $r){
            $reports[$r['list_order']] = $this->dataToObject($r);
        }
        return $reports;
    }

    /*
     * dataToObject
     *
     * @param array result set row
     * @author ctranel
     * @returns \myagsource\Page\iReportBlock
     */
    protected function dataToObject($report_data){
        $web_block = $this->web_block_factory->blockFromData($report_data);
        $report = $this->report_factory->blockFromData($report_data);

        return new ReportBlock($web_block, $report, $this->supplemental_factory);
    }
}
?>
