<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Block_Layer extends Mage_Catalog_Block_Layer_View
{
	/**
	 * Retrieve salesperson search results object
	 * 
	 * @return Celebros_Salesperson_Model_Api_QwiserSearchResults
	 */
	protected function getQwiserSearchResults(){
    	if(Mage::helper('salesperson')->getSalespersonApi()->results)
    		return Mage::helper('salesperson')->getSalespersonApi()->results;
    }
    
    /**
     * Retrieve relevant products count
     * 	
     * @return string
     */
	public function getResultCount()
    {
    	return $this->getQwiserSearchResults()->GetRelevantProductsCount();
    }

    /**
     * Get layer object
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        return Mage::getModel('salesperson/layer');
    }

    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowBlock()
    {
        $availableResCount = (int) Mage::app()->getStore()
            ->getConfig(Mage_CatalogSearch_Model_Layer::XML_PATH_DISPLAY_LAYER_COUNT );

        if (!$availableResCount
            || ($availableResCount>=$this->getResultCount())) {
            return parent::canShowBlock();
        }
        return false;
    }
}
