<?php
namespace myagsource\Report\Content;

require_once APPPATH . 'libraries/Report/iWhereGroup.php';
require_once APPPATH . 'libraries/Report/Content/WhereCriteria.php';

use \myagsource\Report\iWhereGroup;
use \myagsource\Report\Content\WhereCriteria;
use \myagsource\Datasource\iDataField;
use myagsource;

/**
 * Name:  WhereGroup
 * 
 * 
 * @todo: Child groups have not yet been implemented
 *
 * Author: ctranel
 *
 * Created:  06-05-2015
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
	 * @todo: implement this fully for nesting groups
	 **/
	protected $child_groups;
	
	/**
	 */
	/* -----------------------------------------------------------------
	*  Constructor

	*  Sets datafield and order properties

	*  @author: ctranel
	*  @date: Feb 10, 2015
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
				$ret['criteria'][] = $this->cg->criteria();
			}
		}
		return $ret;		
	}
	
	
}

?>