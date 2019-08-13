<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Backend_Navigationtosearch_Enable extends Mage_Core_Model_Config_Data
{
	/**
	 * 
	 *
	 * @return Celebros_Salesperson_Model_System_Config_Backend_Navigationtosearch_Enable
	 */
	protected function _afterSave()
	{
		$store_code=(Mage::app()->getRequest()->getParam('store')); // Current store scope

		/*
		if ($store_code=='default') // do not allow to change nav2search from the default store view. Can collide with the default scope
		{
			return;
		}
		*/
		
		if (!(isset($store_code)))
		{
			$websites=Mage::app()->getWebsites();
			$website=$websites['1'];
			$store_code=$website->getDefaultStore()->getCode();
		}
		
		$store_col=Mage::getModel('core/store')->getCollection()->load();
		foreach($store_col as $store)
		{
			if ($store->getCode()==$store_code)
			{
				$store_id=$store->getStoreId();
				break;
			}
		}
		
		if ( ($this->getData('groups/nav_to_search_settings/fields/nav_to_search/value') === "0") && (Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search',$store_id)) )
		{
			$rewrites = Mage::helper('salesperson')->getCategoriesRewrites($store_id);
			foreach($rewrites as $rewrite)
			{
				$rewrite->delete();
				$rewrite->getResource()->commit();
			}
		
			$model = Mage::getModel('catalog/url');
		
			$store = Mage::app()->getStore($store_id);
			$model->refreshCategoryRewrite($store->getRootCategoryId(), $store_id, false);
		} // Check if activated
		elseif($this->getData('groups/nav_to_search_settings/fields/nav_to_search/value') === "1") /*&&
				!Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search',$store_id))*/
		{
			Mage::helper('salesperson')->updateCategoriesUrlRewrites($store_id);
		}
	}
}
