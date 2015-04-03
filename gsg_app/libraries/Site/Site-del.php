<?php

namespace myagsource\Site;

/**
 *
 * @author ctranel
 *        
 */
class Site {
	
	/**
	 * datasource_site
	 * @var \Site_model
	 **/
	protected $datasource_site;

	/**
	 */
	function __construct(\site_model $datasource_site) {
		$this->datasource_site = $datasource_site;
	}
	
	public function getNavigationArray($base_section){
		$data = $this->datasource_site->getNavigationData($base_section);
		$this->structureNavData($data);
	}
	
	protected function structureNavData($data){
		if(!is_array($data) || empty($data)){
			return false;
		}

		$arr_sections = [];
		foreach($data as $r){
			if($r['section_id'] === null){
				$arr_sections[$r['section_id']] = [
					''
				]
			}
			else{
				$arr_children = 
			}
		}
	}
}

?>