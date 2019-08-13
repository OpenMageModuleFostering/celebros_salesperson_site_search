<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Block_Result extends Mage_Core_Block_Template
{
	/**
	 * Catalog Product collection
	 *
	 * @var Mage_CatalogSearch_Model_Mysql4_Fulltext_Collection
	 */
	protected $_productCollection;
	
	/**
	 * Retrieve salesperson session
	 *
	 * @return Mage_Catalog_Model_Session
	 */

	var $helper;

	protected function _getSession()
	{
		return Mage::getSingleton('salesperson/session');
	}

	/**
	 * Retrieve QwiserSearchApi model object
	 *
	 * @return Mage_CatalogSearch_Model_Query
	 */
	protected function _getSalespersonApi()
	{
		if(!$this->helper)
			$this->helper = $this->helper('salesperson');

		return $this->helper->getSalespersonApi();
	}

	/**
	 * Prepare layout
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	protected function _prepareLayout()
	{
		// add Home breadcrumb
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		if ($breadcrumbs) {
			$title = "";
			if($this->helper('salesperson')->getQueryText()!='') $title = $this->__("Search results for: '%s'", $this->helper('salesperson')->getQueryText());
			$breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Go to Home Page'),
                'link'  => Mage::getBaseUrl()
			));
			if(Mage::getStoreConfigFlag('salesperson/display_settings/breadcrumbs') && isset($this->_getSalespersonApi()->results) && count($this->_getSalespersonApi()->results->SearchPath->Items) > 0){
				$query = $this->helper('salesperson')->getQueryText();
				if(! empty($query)) {
					$breadcrumbs->addCrumb('search', array(
			                'label' => $title,
			                'title' => $title,
							'link' => Mage::Helper('salesperson')->getResultUrl($this->helper('salesperson')->getQueryText())
					));
				}
				
				$searchPathEntry = 0;
				$paths = array();
				$totalEntries = $this->_getSalespersonApi()->results->SearchPath->Count;
				$searchPaths = $this->_getSalespersonApi()->results->SearchPath->Items;
				for($i = 0; $i < $totalEntries; $i++){
					$paths[] = $searchPaths[($totalEntries - 1) - $i]->Answers->Items[0]->Id;
				}
				array_pop($paths);
				foreach($searchPaths as $key => $searchPath){
					$crumb = array(
			                'label' => $searchPath->Answers->Items[0]->Text,
			                'title' => $searchPath->Answers->Items[0]->Text,
					);
					$searchPathEntry++;
					if($searchPathEntry < $totalEntries){
						$crumb['link'] = Mage::getBlockSingleton('salesperson/layer_state')->getRemoveAnswersFromBredcrumbsUrl($paths);
						array_pop($paths);
					}
					
					$breadcrumbs->addCrumb($searchPath->Answers->Items[0]->Id, $crumb);
				}
			}
			else {
				$breadcrumbs->addCrumb('search', array(
	                'label' => $title,
	                'title' => $title,
				));
			}
		}

		// modify page title
		$pageTitle = $this->__("Search results for: '%s'", $this->helper('salesperson')->getEscapedQueryText());
		if($this->helper('salesperson')->getEscapedQueryText()=='') $pageTitle = $this->__("Search results");
		$this->getLayout()->getBlock('head')->setTitle($pageTitle);
		
		Mage::Helper('salesperson')->setRelatedSearches($this->hasRelatedSearches());
		
		/*if ($headBlock = $this->getLayout()->getBlock('head')) {
			$category = $this->getCurrentCategory();
			if ($title = $category->getMetaTitle()) {
				$headBlock->setTitle($title);
			}
			if ($description = $category->getMetaDescription()) {
				$headBlock->setDescription($description);
			}
			if ($keywords = $category->getMetaKeywords()) {
				$headBlock->setKeywords($keywords);
			}
			if ($this->helper('catalog/category')->canUseCanonicalTag()) {
				$headBlock->addLinkRel('canonical', $category->getUrl());
			}

			if ($this->IsRssCatalogEnable() && $this->IsTopCategory()) {
				$title = $this->helper('rss')->__('%s RSS Feed',$this->getCurrentCategory()->getName());
				$headBlock->addItem('rss', $this->getRssLink(), 'title="'.$title.'"');
			}
		}*/		
		
		return parent::_prepareLayout();
	}
	
	protected function getCurrentCategory(){
		$q = Mage::helper('salesperson')->getQueryText();
		
		$categories = Mage::getModel('catalog/category')
						->getCollection()
						->addAttributeToFilter("name",$q)
						->addIsActiveFilter();

		return reset($categories);
	}

	/**
	 * Retrieve search list toolbar block
	 *
	 * @return Mage_Catalog_Block_Product_List
	 */
	public function getListBlock()
	{
		return $this->getChild('search_result_list');
	}

	/**
	 * Set search available list orders
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	public function setListOrders() {
		return $this;
	}

	/**
	 * Set available view mode
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	public function setListModes() {
		        $this->getListBlock()
		            ->setModes(array(
		                'grid' => $this->__('Grid'),
		                'list' => $this->__('List'))
		            );
		return $this;
	}

	/**
	 * Set Search Result collection
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	public function setListCollection() {

	}

	/**
	 * Retrieve Search result list HTML output
	 *
	 * @return string
	 */
	public function getProductListHtml()
	{
		 
		return $this->getChildHtml('search_result_list');
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Celebros_Helper_QwiserApi_QwiserProduct
	 */
	protected function getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			$query = $this->_getSalespersonApi();
			if(count($query->qsr->Products)>0){
				$this->_productCollection = $query->qsr->Products->Items;
			}
		}

		return $this->_productCollection;
	}

	/**
	 * Retrieve search result count
	 *
	 * @return string
	 */
	public function getResultCount()
	{
		if($this->_getSalespersonApi()->results){
			return $this->_getSalespersonApi()->results->GetRelevantProductsCount();
		}
	}

	/**
	 * Retrieve no Minimum query length Text
	 *
	 * @return string
	 */
	public function getNoMinQueryLengthText()
	{
		if (Mage::helper('salesperson')->isMinQueryLength()) {
			return Mage::helper('salesperson')->__('Minimum Search query length is %s', $this->_getSalespersonApi()->getMinQueryLength());
		}
		return $this->_getData('no_result_text');
	}

	
	public function hasRelatedSearches(){
		if(!empty($this->_getSalespersonApi()->results->RelatedSearches)){
			$relatedSearches = $this->_getSalespersonApi()->results->RelatedSearches;
			$out = array();
			foreach ($relatedSearches as $key => $relatedSearch){
			$urlParams = array();
				$urlParams['_current']  = false;
				$urlParams['_escape']   = true;
				$urlParams['_use_rewrite']   = false;
				$urlParams['_query']    = array(
	        	'q' => $relatedSearch,
				);
				$out[$relatedSearch] = Mage::getUrl('*/*/index', $urlParams);
			}
			return $out;
		}
		return false;
	}
	
	public function getBannerImage(){
		return $this->bannerImage != '' ? $this->bannerImage : false;
	}
	
	public function getCustomMessage(){
		return $this->customMessage != '' ? $this->customMessage : false;
	}
}
