<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Helper_Wishlist_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieve Item Store for URL
     *
     * @param Celebros_Salesperson_Model_Api_QwiserProduct|Mage_Wishlist_Model_Item $item
     * @return Mage_Core_Model_Store
     */
    protected function _getUrlStore($item)
    {
	Mage::log("wishlist");
        $storeId = null;
        if ($item instanceof Celebros_Salesperson_Model_Api_QwiserProduct) {
        	if(key_exists(Mage::Helper('salesperson/mapping')->getMapping('visible'),$item->Field) && key_exists(Mage::Helper('salesperson/mapping')->getMapping('store_id'),$item->Field)){
	            if ($item->Field[Mage::Helper('salesperson/mapping')->getMapping('visible')] == '1' ) {
	                $storeId = $item->Field[Mage::Helper('salesperson/mapping')->getMapping('store_id')];
	            }
        	}
        }
        return Mage::app()->getStore($storeId);
    }
    
	/**
     * Retrieve url for adding product to wishlist with params
     *
     * @param Celebros_Salesperson_Model_Api_QwiserProduct|Mage_Wishlist_Model_Item $product
     * @param array $param
     * @return  string|boolean
     */
    public function getAddUrlWithParams($item, array $params = array())
    {Mage::log("wishlist");
        $productId = null;
        if ($item instanceof Celebros_Salesperson_Model_Api_QwiserProduct) {
        	if(key_exists(Mage::Helper('salesperson/mapping')->getMapping('id'),$item->Field)){
            	$productId = $item->Field[Mage::Helper('salesperson/mapping')->getMapping('id')];
        	}
        }
        if ($item instanceof Mage_Wishlist_Model_Item) {
            $productId = $item->getProductId();
        }

        if ($productId) {
            $params['product'] = $productId;
            return $this->_getUrlStore($item)->getUrl('wishlist/index/add', $params);
        }

        return false;
    }
}
