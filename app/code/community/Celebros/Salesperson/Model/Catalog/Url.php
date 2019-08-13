<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Catalog_Url extends Mage_Catalog_Model_Url
{

	/**
	 * Generate either id path, request path or target path for product and/or category
	 *
	 * For generating id or system path, either product or category is required
	 * For generating request path - category is required
	 * $parentPath used only for generating category path
	 *
	 * @param string $type
	 * @param Varien_Object $product
	 * @param Varien_Object $category
	 * @param string $parentPath
	 * @return string
	 * @throws Mage_Core_Exception
	 */
	
	/*protected function _refreshCategoryRewrites(Varien_Object $category, $parentPath = null, $refreshProducts = true)
	{
		$res = parent::_refreshCategoryRewrites($category, $parentPath, $refreshProducts);
		
		//Check if we need to overide the category rewrite behaviour
		$navToSearchEnabled = Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search');
		if($navToSearchEnabled) {
			$query = $this->_getSalespersonQuery($category);
			$targetPath = Mage::helper('salesperson')->getResultUrl($query, $bAddIndexSuffix = true, $bRelativePath = true);
			
			$rewrite = Mage::getModel('core/url_rewrite')->load($this->_rewrite->getId());
			$rewrite->setTargetPath($targetPath)
					->save();					
		}
		
		return $res;
	}
	
	protected function _saveRewriteHistory($rewriteData, $rewrite)
	{
		if ($rewrite instanceof Varien_Object && $rewrite->getId() 
			&& trim($rewriteData['request_path']) == trim($rewrite->getRequestPath()))
			return $this;
		else 
			return parent::_saveRewriteHistory($rewriteData, $rewrite);
	}
	
	public function generatePath($type = 'target', $product = null, $category = null, $parentPath = null)
	{
		//Check if we need to overide the category rewrite behaviour
		$bOverride = !$product && $category && Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search');
		if($bOverride) 
			return $this->_generateCelebrosCategoryPath($type, $product, $category, $parentPath);
		else 
			return parent::generatePath($type, $product, $category, $parentPath);
	}
	
	private function _generateCelebrosCategoryPath($type = 'target', $product = null, $category = null, $parentPath = null)
	{
		$path = "";
		
		switch($type){
			case 'target':
				$query = $this->_getSalespersonQuery($category);
				$path = Mage::helper('salesperson')->getResultUrl($query, $bAddIndexSuffix = true, $bRelativePath = true);
				break;
			case 'request':
				$path = parent::generatePath($type, $product, $category, $parentPath);
				//Zend_Debug::dump($path);
				$pathWithSpace = $path . " ";
				$oldPath = $this->_rewrite->getRequestPath();
				//Add space to request path so it will be persisted
				if($path == $oldPath) $path = $pathWithSpace;
				
				//$path = substr($path,strlen($path)-1, 1) == " " ? substr($path,0, strlen($path)-1) : $path . " ";
				break;
			default: 
				$path = parent::generatePath($type, $product, $category, $parentPath);
		}
		
		return $path;
	}*/
	
	/*public function refreshRewrites($storeId = null)
	{
		parent::refreshRewrites($storeId);
		Mage::helper('salesperson')->updateCategoriesUrlRewrites();
	}*/
	
	public function generatePath($type = 'target', $product = null, $category = null, $parentPath = null)
	{
		if($this->_isNeedTargetPathOverrideByCelebros($type, $product, $category, $parentPath)) 
			return $this->_generateCelebrosCategoryTargetPath($category);
		else 
			return parent::generatePath($type, $product, $category, $parentPath);
	}	
	
	private function _generateCelebrosCategoryTargetPath($category)
	{
		$query = Mage::helper('salesperson')->getCategoryRewriteQuery($category);
		$targetPath = Mage::helper('salesperson')->getResultUrl($query, $bAddIndexSuffix = true, $bRelativePath = true);
		return $targetPath;
	}
	
	private function _isNeedTargetPathOverrideByCelebros($type = 'target', $product = null, $category = null, $parentPath = null) {
		//Check if we need to overide the category rewrite behaviour
		$bOverrideTargetPath = ($type == 'target') && !$product && $category && Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search');
		
		if(!$bOverrideTargetPath) return $bOverrideTargetPath;
		
		$rewrite = $this->_rewrite;
		if ($rewrite && $rewrite->getId() && $this->generatePath('request', $product, $category, $parentPath) == $rewrite->getRequestPath()) {
			$bOverrideTargetPath = false;
		}
		
		return $bOverrideTargetPath;
	}
	
}