<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Helper_Product_Compare extends Mage_Core_Helper_Url
{
    /**
     * Get parameters used for build add product to compare list urls
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  array
     */
    protected function _getUrlParams($product)
    {
    		if(key_exists(Mage::Helper('salesperson/mapping')->getMapping('id'),$product->Field)){
            	$productId = $product->Field[Mage::Helper('salesperson/mapping')->getMapping('id')];
    		}
	        return array(
		            'product' => $productId,
		            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
		        );
        	
    }

    /**
     * Retrieve url for adding product to conpare list
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  string
     */
    public function getAddUrl($product)
    {
        return $this->_getUrl('catalog/product_compare/add', $this->_getUrlParams($product));
    }
}
