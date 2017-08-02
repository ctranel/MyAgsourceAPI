<?php
namespace myagsource\Listings\Content\Conditions;

require_once APPPATH . 'libraries/Listings/iWhereGroup.php';
require_once APPPATH . 'libraries/Listings/Content/Conditions/WhereCriteria.php';

use \myagsource\Listings\iWhereGroup;
use \myagsource\Listings\Content\Conditions\WhereCriteria;
use \myagsource\Datasource\iDataField;
use myagsource;

/**
 * Name:  WhereGroup
 * 
 * 
 * Author: ctranel
 *
 * Created:  2017-08-01
 *
 * Description:  WhereGroup.
 *
 */
class WhereGroup implements iWhereGroup {
	/**
	 * operator
	 * @var string
	 **/
	protected $operator;
	
	/**
	 * criteria
	 * @var array of WhereCriteria objects
	 **/
	protected $criteria;
	
	/**
	 * child_groups
	 * @var array of WhereGroup objects
	 **/
	protected $child_groups;
	
	/**
	 */
	/* -----------------------------------------------------------------
	*  Constructor

	*  Sets datafield and order properties

	*  @author: ctranel
	*  @date: 2017-08-01
	*  @param: iDataField sort field
	*  @param: string sort order
	*  @return datatype
	*  @throws: 
	* -----------------------------------------------------------------
	\*/
	public function __construct($operator, $criteria = null, $child_groups = null) {
		if(!is_array($criteria) && !is_array($child_groups)){
			throw new \InvalidArgumentException('Either criteria or child group is required to initialize WhereGroup');
		}
		$this->operator = $operator;
		$this->criteria = $criteria;
		$this->child_groups = $child_groups;
	}
	
	/**
	 * criteria
	 *
	 * returns array of potentially nested where criteria:
	 * [operator, criteria=>[
	 *     	[field, condition], 
	 *     	[field, condition],
	 * 		[field, condition], 
	 * 		[operator, criteria=>[
	 *     		[field, condition], [field, condition]
	 *     	]
	 * ]]
	 * 
	 * @return multidimensional array
	 * 
	 * @author ctranel
	 **/
	public function criteria(){
		$ret = ['operator' => $this->operator, 'criteria' => []];
		//compose array of criteria
		if(is_array($this->criteria)){
			foreach($this->criteria as $c){
				$ret['criteria'][] = $c->criteria();
			}
		}
		
		//add child groups to criteria
		if(is_array($this->child_groups)){
			foreach($this->child_groups as $cg){
				$ret['criteria'][] = $cg->criteria();
			}
		}
		return $ret;		
	}
	
	
}

?>