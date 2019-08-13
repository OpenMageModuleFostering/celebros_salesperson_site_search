<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Category extends Mage_Catalog_Model_Category
{

	public function getUrl()
	{
		if(!Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search')) return parent::getUrl();

		$url = $this->_getData('url');
		if (is_null($url)) {
			$query = $this->salesperson_get_query();
			$url = Mage::helper('salesperson')->getResultUrl($query);
			$this->setData('url', $url);
		}
		
		return $url;
	}
	
	private function salesperson_get_query(){
		
		$query = $this->salesperson_get_search_phrase($this);
		
		switch(Mage::getStoreConfig('salesperson/nav_to_search_settings/nav_to_search_use_full_category_path')) {
			
			case "category":
				break;
			
			case "full_path":
				
				$categories = $this->getParentCategories();
				$aParentIds = $this->getParentIds();
				$aParentIds = array_reverse($aParentIds);
				
				for($i=0; $i < count($aParentIds) - 2; $i++) {
					$parentId  = $aParentIds[$i];
					$category = $categories[$parentId];
					$searchPhraseAttributeValue = $this->salesperson_get_search_phrase($category);
					$query =  $searchPhraseAttributeValue . " " . $query;
				}
				
				break;
				
			case "category_and_parent":
				
				$categories = $this->getParentCategories();
				
				if(count($categories) < 3) break;
				
				$parentId = $this->getParentId();
				$category = $categories[$parentId];
				$searchPhraseAttributeValue = $this->salesperson_get_search_phrase($category);
				$query =  $searchPhraseAttributeValue . " " . $query;

				break;
				
			case "category_and_root":
				
				$categories = $this->getParentCategories();

				if(count($categories) < 3) break;
				
				$aParentIds = $this->getParentIds();
				$branchRootId = $aParentIds[2];
				$category = $categories[$branchRootId];
				$searchPhraseAttributeValue = $this->salesperson_get_search_phrase($category);
				$query =  $searchPhraseAttributeValue . " " . $query;
				
				break;
		}
		
		return $query;
	}
	
	private function salesperson_get_search_phrase($category){
		$category->load($category->getId());
		$att_code = 'salesperson_search_phrase';
		$att_value = $category->getData($att_code);
		
		if($att_value=='') return $category->getName();
		else return  $att_value;
	}

}