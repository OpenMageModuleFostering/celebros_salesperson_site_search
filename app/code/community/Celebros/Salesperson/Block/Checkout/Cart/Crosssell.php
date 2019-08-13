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
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */

class Celebros_Salesperson_Block_Checkout_Cart_Crosssell extends Mage_Checkout_Block_Cart_Crosssell {
	
	/**
	 * Get crosssell items
	 *
	 * @return array
	 */
	public function getItems()
	{
		$items = $this->getData('items');
		if (is_null($items)) {
		
			$lastAdded = null;
		
			//This code path covers the 2 cases - product page and shoping cart
			if($this->getProduct()!=null){
				$lastAdded = $this->getProduct()->getId();
			}
			else{
				$cartProductIds = $this->_getCartProductIds();
				$lastAdded = null;
				for($i=count($cartProductIds) -1; $i >=0 ; $i--){
					$id =  $cartProductIds[$i];
					$parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($id);
					if(empty($parentIds)){
						$lastAdded = $id;
						break;
					}
				}
			}
		


			$crossSellIds = Mage::helper('salesperson')->getSalespersonCrossSellApi()->getRecommendationsIds($lastAdded);

			$items = $this->_getCollection()
			->addAttributeToFilter('entity_id', array('in' => $crossSellIds,));
		}

		$this->setData('items', $items);
		return $items;
	}

	public function getItemCollection() {
		return $this->getItems();
	}
	
	public function getItemCount()
	{
		return count($this->getItems());
	}

	/**
	 * Get crosssell products collection
	 */
	protected function _getCollection()
	{
		$collection = Mage::getModel('catalog/product')
		->getCollection()
		->setStoreId(Mage::app()->getStore()->getId())
		->addStoreFilter()
		->setPageSize($this->_maxItemCount);
		$this->_addProductAttributesAndPrices($collection);
	
		Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
		Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
		Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);
	
		return $collection;
	}
}