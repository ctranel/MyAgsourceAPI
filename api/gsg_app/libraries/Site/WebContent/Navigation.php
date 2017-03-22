<?php
namespace myagsource\Site\WebContent;

require_once(APPPATH . 'helpers/multid_array_helper.php');

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
class Navigation{
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
     * $groups
     * @var key=>value array of eligible groups
     **/
    protected $groups;

    /**
     * $active_group_id
     * @var int
     **/
    protected $active_group_id;

    /**
	 * $tree
	 * @var Array
	 **/
	protected $tree;

	function __construct(\Navigation_model $datasource_navigation, Herd $herd, $permissions_list, $groups, $active_group_id) {
		$this->datasource_navigation = $datasource_navigation;
		$this->herd = $herd;
		$this->permissions_list = $permissions_list;
        $this->groups = $groups;
        $this->active_group_id = $active_group_id;
		$data = $this->setData();
		$this->tree = $this->buildTree($data);

        //merge hard-coded account nav
        $this->tree[0]['children'][(count($this->tree[0]['children']) - 1)] = array_merge($this->tree[0]['children'][(count($this->tree[0]['children']) - 1)], $this->getAccountData());

        //if user has access to multiple groups, add another menu item
        if(count($this->groups) > 1){
            $this->tree[0]['children'][] = $this->getGroupData();
        }
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
			$tmp_array = $this->datasource_navigation->getAllContent($this->herd->herdCode());
		}
		else{
			/* 
			 * subscription is different from other scopes in that it fetches content by herd data (i.e. herd output) for users that 
			 * have permission only for subscribed content.  All other scopes are strictly users-based
			 */
			if(in_array('View Subscriptions', $this->permissions_list)){
				$tmp_array = array_merge($tmp_array, $this->datasource_navigation->getSubscribedContent($this->herd->herdCode()));
			}
			//if(in_array('View Account', $this->permissions_list)){
				//$scope[] = 'account';
			//}
			if(in_array('View Admin', $this->permissions_list)){
				$scope[] = 'admin';
			}
			if(!empty($scope)){
				$tmp_array = array_merge($tmp_array, $this->datasource_navigation->getContentByScope($scope, $this->herd->herdCode()));
			}

			$tmp_array = array_map("unserialize", array_unique(array_map("serialize", $tmp_array)));
		}

		usort($tmp_array, \sort_by_key_value_comp('list_order'));

		return $tmp_array;
	}

    /**
     * @method getAccountData()
     * @return array tree of general account data
     * @access public
     **/
    protected function getAccountData(){
        $ret = [
            'children' => $this->getGeneralAcctData(),
            'dom_id' => 'account',
            'href' => null,
            'name' => 'Account',
            'route' => null,
            'scope' => 'base',
        ];

        return $ret;
    }

    /**
     * @method getGeneralAcctData()
     * @return array tree of group access options
     * @access public
     **/
    protected function getGeneralAcctData(){
        $acct_nav = [];
        if(!isset($this->herd)){
            return [
              0 => [
                  'dom_id' => 'login',
                  'href' => '/auth/login',
                  'name' => 'Log In',
                  'route' => '/login',
                  'scope' => 'base',
              ]
            ];
        }

        if(in_array("Request Herd", $this->permissions_list)){
            $acct_nav[] = [
                'dom_id' => 'request_herd',
                'href' => 'dhi/change_herd/request',
                'name' => 'Request Herd',
                'route' => '/request_herd',
                'scope' => 'base',
            ];
        }
    /*    if(in_array("Select Herd", $this->permissions_list) && isset($num_herds) && $num_herds > 1){
            $acct_nav[] = [
                'dom_id' => 'select_herd',
                'href' => 'dhi/change_herd/select',
                'name' => 'Change Herd',
                'route' => '/select_herd',
                'scope' => 'base',
            'list_order' => 15,
            ];
        }*/
        if(in_array("Update Service Group Access", $this->permissions_list)){//$this->active_group_id == 2){
            $acct_nav[] = [
                'dom_id' => 'manage_service_grp',
                'href' => 'auth/manage_service_grp',
                'name' => 'Manage Consultant Access',
                'route' => '/manage_service_grp',
                'scope' => 'base'
                ,
            ];
        }
        if(in_array("View Assign w permission", $this->permissions_list)){
            $acct_nav[] = [
                'dom_id' => 'service_grp_manage_herds',
                'href' => 'auth/service_grp_manage_herds',
                'name' => 'Manage Herd Access',
                'route' => '/service_grp_manage_herds',
                'scope' => 'base',
            ];
            $acct_nav[] = [
                'dom_id' => 'service_grp_request',
                'href' => 'auth/service_grp_request',
                'name' => 'Request Herd Access',
                'route' => '/service_grp_request',
                'scope' => 'base',
            ];
        }
        if(false && in_array("View Access Log", $this->permissions_list)){
            $acct_nav[] = [
                'dom_id' => 'access_log',
                'href' => 'access_log/display',
                'name' => 'Access Log',
                'route' => '/access_log',
                'scope' => 'admin',
            ];
        }
        if(true){//$this->as_ion_auth->is_editable_user($this->session->userdata('user_id'), $this->session->userdata('user_id'))){
            $acct_nav[] = [
                'dom_id' => 'edit_user',
                'href' => 'auth/edit_user',
                'name' => 'Edit User Account',
                'route' => '/edit_user',
                'scope' => 'base',
            ];
        }
        if(in_array("Edit All Users", $this->permissions_list) || in_array("Edit Users In Region", $this->permissions_list)){
            $acct_nav[] = [
                'dom_id' => 'list_accounts',
                'href' => 'auth/list_accounts',
                'name' => 'List User Account',
                'route' => '/list_accounts',
                'scope' => 'base',
            ];
        }
        if(in_array("Add All Users", $this->permissions_list) || in_array("Add Users In Region", $this->permissions_list)){
            $acct_nav[] = [
                'dom_id' => 'create_user',
                'href' => 'auth/create_user',
                'name' => 'Add User Account',
                'route' => '/create_user',
                'scope' => 'base',
            ];
        }

        $acct_nav[] = [
            'dom_id' => 'logout',
            'href' => 'api/auth/logout',
            'name' => 'Log Out',
            'route' => 'api/auth/logout',
            'scope' => 'base',
        ];

        return $acct_nav;
    }

    /**
     * @method setGroupData()
     * @return void
     * @access public
     **/
    protected function getGroupData(){
        if(!isset($this->groups) || !is_array($this->groups) || count($this->groups) <= 1) {
            return [];
        }
        $group_change_nav = [];
        foreach($this->groups as $k=>$v){
            $group_change_nav[] = [
                'dom_id' => $k . '',
                'href' => '/auth/set_role/'. $k,
                'name' => $v,
                'route' => '/auth/set_role/'. $k,
                'scope' => 'base',
            ];
        }
        $ret = [
            'children' => $group_change_nav,
            'dom_id' => $this->active_group_id . '',
            'href' => null,
            'name' => $this->groups[$this->active_group_id],
            'route' => null,
            'scope' => 'base',
        ];

        return $ret;
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
					'dom_id' => $dom_id,
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