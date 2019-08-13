<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Helper_Checkout_Cart extends Mage_Core_Helper_Url
{
    /**
     * Retrieve url for add product to cart
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  string
     */
    public function getAddUrl($product, $additional = array())
    {
        $continueUrl    = Mage::helper('core')->urlEncode($this->getCurrentUrl());
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;

        if(key_exists(Mage::Helper('salesperson/mapping')->getMapping('id'), $product->Field)){
	        $routeParams = array(
	            $urlParamName   => $continueUrl,
	            'product'       => $product->Field[Mage::Helper('salesperson/mapping')->getMapping('id')]
	        );
        }

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

//        if ($product->hasUrlDataObject()) {
//            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
//            $routeParams['_store_to_url'] = true;
//        }

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart') {
            $routeParams['in_cart'] = 1;
        }

        return $this->_getUrl('checkout/cart/add', $routeParams);
    }
}
