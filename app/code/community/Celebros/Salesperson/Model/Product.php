<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Product extends Celebros_Salesperson_Model_Api_QwiserProduct
{
	/**
	 * Initialize resources
	 */
	protected function _construct()
	{
		$this->_init('salesperson/product');
	}

	/**
	 * Retrive product by SKU
	 * 
	 * @param string $productSku
	 * @return Celebros_Salesperson_Model_Api_QwiserProduct
	 */
	public function load($productSku){
		$products = Mage::Helper("salesperson")->getSalespersonApi()->results->Products->Items;
		foreach ($products as $product)
		{
			if($product->Sku = $productSku){
				return $product;
			}
		}
	}
	/**
	 * Retrive QwiserSearchResult
	 * 
	 * @return Celebros_Salesperson_Model_Api_QwiserSearchResult
	 */
	protected function getQwiserSearchResults(){
    	if(Mage::helper('salesperson')->getSalespersonApi()->results)
    		return Mage::helper('salesperson')->getSalespersonApi()->results;
    }
    
	/**
	* Retrieve Store Id of the product
	*
	* @return int
	*/
	public function getStoreId()
	{
		if (key_exists(Mage::Helper('salesperson/mapping')->getMapping('store_id'),$this->Field)) {
			return $this->Field(Mage::Helper('salesperson/mapping')->getMapping('store_id'));
		}
		return Mage::app()->getStore()->getId();
	}

	/**
	 * Get product url model
	 *
	 * @return Mage_Catalog_Model_Product_Url
	 */
	public function getUrlModel()
	{
		if ($this->_urlModel === null) {
			$this->_urlModel = Mage::getSingleton('catalog/product_url');
		}
		return $this->_urlModel;
	}


	/**
	 * Get product name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->Field(Mage::Helper('salesperson/mapping')->getMapping('title'));
	}

	/**
	 * Get product status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->Field[Mage::Helper('salesperson/mapping')->getMapping('status')] == 'Enabled';
	}

	public function getInStock() {

//		$productModel = Mage::getSingleton('catalog/product');
//		$product = $productModel->load($this->getId());
//		$inventoery = Mage::getSingleton('cataloginventory/stock_item')->loadByProduct($product);
//		$isInStock = $inventoery->getData('is_in_stock');
//		if ((int)$isInStock == 0){ return false; }else { return true; }
		return key_exists(Mage::Helper('salesperson/mapping')->getMapping('is_in_stock'), $this->Field) && (int)$this->Field[Mage::Helper('salesperson/mapping')->getMapping('is_in_stock')] == 0 ? true : false;
	}
	public function isSaleable() {
//		$productModel = Mage::getSingleton('catalog/product');
//		$product = $productModel->load($this->getId());
//		$inventoery = Mage::getSingleton('cataloginventory/stock_item')->loadByProduct($product);
//		$isInStock = $inventoery->getData('is_in_stock');
//		if ((int)$isInStock == 0){ return false; }else { return true; }
		return key_exists(Mage::Helper('salesperson/mapping')->getMapping('is_salable'), $this->Field) && (int)$this->Field[Mage::Helper('salesperson/mapping')->getMapping('is_salable')] == 0 ? false : true;
	}


	public function getSku()
	{
		return key_exists(Mage::Helper('salesperson/mapping')->getMapping('sku'),$this->Field) ? $this->Field[Mage::Helper('salesperson/mapping')->getMapping('sku')] : false;
	}
	
	/**
	 * Retrive Product Id
	 * @return string
	 */
	public function getId() {
		return $this->Field[Mage::Helper('salesperson/mapping')->getMapping('id')];
	}

	/**
	 * Retrieve assigned category Ids
	 *
	 * @return array
	 */
	public function getCategory()
	{
		return key_exists(Mage::Helper('salesperson/mapping')->getMapping('category'),$this->Field) ? $this->Field[Mage::Helper('salesperson/mapping')->getMapping('category')] : false;
	}

	/**
	 * Retrieve product websites identifiers
	 *
	 * @return array
	 */
	public function getWebsiteIds()
	{
		if (strpos($this->Field[Mage::Helper('salesperson/mapping')->getMapping('websites')],',')){
			$websitesIds = explode(",", $this->Field[Mage::Helper('salesperson/mapping')->getMapping('websites')]);
			return $websitesIds;
		}
		else {
			return array($this->Field[Mage::Helper('salesperson/mapping')->getMapping('websites')]);
		}
	}

	/**
	 * Get all sore ids where product is presented
	 *
	 * @return array
	 */
	public function getStoreIds()
	{
		if (strpos($this->Field[Mage::Helper('salesperson/mapping')->getMapping('store_id')],',')){
			$storeIds = explode(",", $this->Field[Mage::Helper('salesperson/mapping')->getMapping('store_id')]);
			return $storeIds;
		}
		else {
			return array($this->Field[Mage::Helper('salesperson/mapping')->getMapping('store_id')]);
		}
	}

	/**
	 * Retrive the Rating precent
	 * 
	 * @return string
	 */
	public function getRatingSummary()
	{
		return $this->Field[$this->Field[Mage::Helper('salesperson/mapping')->getMapping('rating_summary')]]; //_getData('rating_summary');
	}
	/**
	 * Retrieve product found in
	 *
	 * @return category with link
	 */
	public function getAvailableInCategories()
	{
		if(key_exists(Mage::Helper('salesperson/mapping')->getMapping('category'), $this->Field) && $this->Field[Mage::Helper('salesperson/mapping')->getMapping('category')] != ''){
			$categories = strpos($this->Field[Mage::Helper('salesperson/mapping')->getMapping('category')],',') 
									? explode(',',$this->Field[Mage::Helper('salesperson/mapping')->getMapping('category')]) 
									: $this->Field[Mage::Helper('salesperson/mapping')->getMapping('category')];
			if (is_array($categories)){
				$urls = array();
				foreach ($categories as $category){
					$urlParams = array();
					$urlParams['_current']  = false;
					$urlParams['_escape']   = true;
					$urlParams['_use_rewrite']   = false;
					$urlParams['_query']    = array(
		        	'q' => $category,
					);
					$urls[$category] = Mage::getUrl('*/*/index', $urlParams);
				}
				return $urls;
			}
			else {
				$urlParams = array();
				$urlParams['_current']  = false;
				$urlParams['_escape']   = true;
				$urlParams['_use_rewrite']   = false;
				$urlParams['_query']    = array(
	        	'q' => $categories,
				);
				return array($categories => Mage::getUrl('*/*/index', $urlParams));
			}
		}
	}
}
