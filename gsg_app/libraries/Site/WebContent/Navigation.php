<?php
namespace myagsource\Site\WebContent;

require_once(APPPATH . 'helpers/multid_array_helper.php');

//use \myagsource\Site\iWebContentRepository;
//use \myagsource\Site\iWebContent;
use \myagsource\dhi\Herd;

/**
 * Constructs permission-and-herd-based navigation
 * 
 * 
 * @name Navigation
 * @author ctranel
 * 
 *        
 */
class Navigation{// implements iWebContentRepository {
	/** 
	 * $datasource_navigation
	 * @var navigation_model
	 **/
	protected $datasource_navigation;

	/**
	 * $herd
	 * @var Herd
	 **/
	protected $herd;

	/**
	 * $permissions_list
	 * @var Array of strings
	 **/
	protected $permissions_list;

	/**
	 * $tree
	 * @var Array
	 **/
	protected $tree;

	function __construct(\Navigation_model $datasource_navigation, Herd $herd, $permissions_list) {
		$this->datasource_navigation = $datasource_navigation;
		$this->herd = $herd;
		$this->permissions_list = $permissions_list;
		$data = $this->setData();
		$this->tree = $this->buildTree($data);
	}
	
	/**
	 * @method setData()
	 * @return void
	 * @access public
	 **/
	//@todo: if we allow producers to select which sections to allow for consultants, we will need to pass that array to this section as well
	protected function setData(){ 
		$scope = ['base'];
		$tmp_array = [];

		if(in_array('View All Content', $this->permissions_list)){
			$tmp_array = $this->datasource_navigation->getAllContent();
		}
		else{
			/* 
			 * subscription is different from other scopes in that it fetches content by herd data (i.e. herd output) for users that 
			 * have permission only for subscribed content.  All other scopes are strictly users-based
			 */
			if(in_array('View Subscriptions', $this->permissions_list)){
				$tmp_array = array_merge($tmp_array, $this->datasource_navigation->getSubscribedContent($this->herd->herdCode()));
			}
			if(in_array('View Account', $this->permissions_list)){
				$scope[] = 'account';
			}
			if(in_array('View Admin', $this->permissions_list)){
				$scope[] = 'admin';
			}
			if(!empty($scope)){
				$tmp_array = array_merge($tmp_array, $this->datasource_navigation->getContentByScope($scope));
			}

			$tmp_array = array_map("unserialize", array_unique(array_map("serialize", $tmp_array)));
		}

		usort($tmp_array, \sort_by_key_value_comp('list_order'));

		return $tmp_array;
	}

	/**
	 * buildTree()
	 * 
	 * Recursive function that takes tabular data and converts into a tree based on id and parent_id fields
	 * 
	 * @param array of data
	 * @param int parent_id
	 * @return array of tree branch
	 * @access public
	 * 
	 * @todo: path algorithm below will only work with pages.  sections will duplicate paths of parent sections
	 **/
	protected function buildTree(array $data, $parent_id = 0, $path = ''){
		$branch = [];
		
		foreach ($data as $k=>$d) {
			if ($d['parent_id'] == $parent_id) {
				unset($data[$k]);
				$full_path = $path . $d['path'];
				$dom_id = explode('/', trim($d['path'], '/'))[0];
				$tmp_array = [
					'name' => $d['name'],
					'id' => $dom_id,//split and take the first element
					'href' => '/' . $full_path,
					'scope' => $d['scope'],
					'route' => $d['route'],
				];
				$children = $this->buildTree($data, $d['id'], $full_path);
				if ($children) {
					$tmp_array['children'] = $children;
				}
				$branch[] = $tmp_array;
			}
		}

		return $branch;
	}

    /**
     * toArray()
     *
     * Returns an array representation of tree
     *
     * @return array
     * @access public
     **/
    public function toArray($section = null){
        if(!isset($this->tree) || !is_array($this->tree) || empty($this->tree)){
            return false;
        }
        if(isset($section)){
            $tree = self::getSubTree('name', $section, $this->tree);//array_search('Herd Summary', array_column($this->tree, 'name'));
        }
        return (isset($tree) ? $tree :$this->tree);
    }



    /**
	 * jsonOutput()
	 * 
	 * Returns json string representation of tree
	 * 
	 * @return string
	 * @access public
	 **/
	public function jsonOutput($section = null){
		$json = json_encode($this->toArray($section));
		return $json;
	}
	
	
	/**
	 * getSubTree
	 * 
	 * @param string needle property name
	 * @param string needle property value for which to search
	 * @param array haystack
	 * @return mixed key
	 * @author ctranel
	 **/
	protected static function getSubTree($prop_name, $prop_value, $haystack){
		$return_val = [];
		foreach($haystack as $val) {
			if(isset($val[$prop_name]) && $val[$prop_name] === $prop_value) {
				$return_val = $return_val + $val['children'];
			}
			elseif(isset($val['children']) && is_array($val['children'])){
				$tmp = self::getSubTree($prop_name, $prop_value, $val['children']);
				if($tmp){
					$return_val = $return_val + $tmp;
				}
			}
		}
		
		if(!empty($return_val)){
			return $return_val;
		}
	}
}

?>