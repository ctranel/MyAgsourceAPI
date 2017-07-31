<?php

namespace myagsource\Site\WebContent;

require_once(APPPATH . 'libraries/Site/WebContent/Block.php');
require_once(APPPATH . 'libraries/Site/iWebContent.php');
require_once(APPPATH . 'libraries/dhi/Herd.php');

use myagsource\Supplemental\Content\SupplementalFactory;

/**
 * A repository? for page objects
 * 
 * 
 * @name WebBlockFactory
 * @author ctranel
 * 
 *        
 */
class WebBlockFactory {
	/**
	 * datasource_blocks
	 * @var block_model
	 **/
	protected $datasource_blocks;

    /**
     * supplemental_factory
     * @var SupplementalFactory
     **/
    protected $supplemental_factory;

    function __construct(\Block_model $datasource_blocks, SupplementalFactory $supplemental_factory) {
		$this->datasource_blocks = $datasource_blocks;
        $this->supplemental_factory = $supplemental_factory;
    }
	
    public function getBlocksFromContent($page_id, $block_content){
        if(empty($block_content)){
            throw new \Exception('No content found for specified block.');
        }
        $blocks = [];
        $criteria = ['page_id' => $page_id];
//		$join = [['table' => 'pages_blocks pb', 'condition' => 'b.id = pb.block_id AND pb.page_id = ' . $page_id]];
        $results = $this->datasource_blocks->getByCriteria($criteria);

        if(empty($results)){
            return false;
        }

        foreach($results as $r){
            if(isset($block_content[$r['list_order']])){
                $data_map = $this->datasource_blocks->getBlockDataMapping($r['id']);
                $blocks[] = new Block($this->supplemental_factory, $block_content[$r['list_order']], $r['id'], $r['name'], $r['description'], $r['display_type'], $r['scope'], $r['isactive'], $r['path'], $data_map);//,
            }
        }
        return $blocks;
    }

    public function getBlock($block_id, $block_content){
        if(empty($block_content)){
            throw new \Exception('No content found for specified block.');
        }
        $r = $this->datasource_blocks->getBlock($block_id);

        if(empty($r)){
            throw new \Exception('No data found for specified block.');
        }

        $data_map = $this->datasource_blocks->getBlockDataMapping($r['id']);
        $block = new Block($this->supplemental_factory, $block_content, $r['id'], $r['name'], $r['description'], $r['display_type'], $r['scope'], $r['isactive'], $r['path'], $data_map);
        return $block;
    }
}

?>