<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Celebros - Pavel Feldman (email: MagentoSupport@celebros.com)
 *
 */
class Celebros_Salesperson_Block_Analytics_View extends Celebros_Salesperson_Block_Layer_View
{
	const CATALOG_CATEGORY_ATTRIBUTE_ENTITY_TYPE = '9';
	const CATALOG_PRODUCT_ATTRIBUTE_ENTITY_TYPE = '10';
	
	/**
	 * Sets parameters for tempalte
	 *
	 * @return Celebros_Salesperson_Block_Analytics_View
	 */
	protected function _prepareLayout()
	{
		$this->setCustomerId(Mage::getStoreConfig('salesperson/anlx_settings/cid'));
		$this->setHost(Mage::getStoreConfig('salesperson/anlx_settings/host'));
		
		$product = $this->getProduct();
		//Set product click tracking params
		if(isset($product)){
			$this->setProductId($product->getId());
			$this->setProductName(str_replace("'", "\'", $product->getName()));
			$this->setProductPrice($product->getFinalPrice());
			$webSessionId = isset($_SESSION['core']['visitor_data']['session_id']) ? $_SESSION['core']['visitor_data']['session_id'] : session_id();
			$this->setWebsessionId($webSessionId);		
		}
		//Set search tracking params
		else {
			$pageReferrer = Mage::getModel('core/url')->getBaseUrl() . $_SERVER['PHP_SELF'];
			$this->setPageReferrer($pageReferrer);
			$this->setQwiserSearchSessionId(Mage::getSingleton('salesperson/session')->getSearchSessionId());
			$webSessionId = isset($_SESSION['core']['visitor_data']['session_id']) ? $_SESSION['core']['visitor_data']['session_id'] : session_id();
			$this->setWebsessionId($webSessionId);
			$this->setQwiserSearchLogHandle(Mage::Helper('salesperson')->getSalespersonApi()->results->GetLogHandle());
		}
		
		return parent::_prepareLayout();
	}
	
	/**
	 * Retrieve current product model
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
	{
		return Mage::registry('product');
	}
}