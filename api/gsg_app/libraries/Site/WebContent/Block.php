<?php
namespace myagsource\Site\WebContent;

require_once APPPATH . 'libraries/Site/iBlock.php';
require_once APPPATH . 'libraries/Site/iBlockContent.php';

use myagsource\Site\iBlock;
use myagsource\Site\iBlockContent;
use myagsource\dhi\Herd;
/**
* Name:  Block
*
* Author: ctranel
*  
* Created:  02-02-2015
*
* Description:  Contains properties and methods specific to displaying blocks of the website.
*
*/

class Block implements iBlock {
	/**
	 * block id
	 * @var int
	 **/
	protected $id;

	/**
	 * page_id
	 * @var int
	protected $page_id;
**/

	/**
	 * block name
	 * @var string
	 **/
	protected $name;
	
	/**
	 * block description
	 * @var string
	 **/
	protected $description;
	
	/**
	 * block path
	 * @var string
	 **/
	protected $path;
		
	/**
	 * display_type
	 * @var string
	 **/
	protected $display_type;
	
	/**
	 * is_summary
	 * @var boolean
	protected $is_summary;
	 **/
	
	/**
	 * scope
	 * @var string
	 **/
	protected $scope;
	
	/**
	 * has_benchmark
	 * @var boolean
	protected $has_benchmark;
**/
	
	/**
	 * active
	 * @var boolean
	 **/
	protected $active;

	/**
	 * block_content
	 * @var iBlockContent
	 **/
	protected $block_content;


	/**
	 * __construct
	 *
	 * @return void
	 * @author ctranel
	 **/
	public function __construct($block_content, $id, $name, $description, $display_type, $scope, $active, $path) { //
		$this->id = $id;
		//$this->page_id = $page_id;
		$this->name = $name;
		$this->description = $description;
		$this->display_type = $display_type;
		$this->scope = $scope;
		$this->active = $active;
		$this->path = $path;
		$this->block_content = $block_content; //report or form
	}
	
	public function id(){
		return $this->id;
	}

	public function path(){
		return $this->path;
	}

	public function name(){
		return $this->name;
	}

    public function description(){
        return $this->description;
    }

    public function displayType(){
		return $this->display_type;
	}

	public function children(){
		return $this->report_fields;
	}

	public function hasBenchmark(){
		return $this->block_content->hasBenchmark();
	}

	public function toArray(){
        if($this->block_content instanceof iBlockContent){
            $ret = $this->block_content->toArray();
        }
        $ret['id'] = $this->id;
            //'page_id' => $this->page_id,
        $ret['name'] = $this->name;
        $ret['description'] = $this->description;
        $ret['display_type'] = $this->display_type;
        $ret['path'] = $this->path;
//            'has_benchmark' => $this->has_benchmark
;
        return $ret;
	}

	public function toJson(){
		return json_encode($this->toArray());
	}

	/**
	 * @method loadChildren()
	 * @param iWebContent[]
	 * @return void
	 * @access public
	public function loadChildren($children){
		$this->children = $children;
	}
* */

}


