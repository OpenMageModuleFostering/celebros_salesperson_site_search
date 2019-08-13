<?php
/**
 * Salesperson navigation
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


class Celebros_Salesperson_Block_Navigation extends Mage_Catalog_Block_Navigation
{
    /**
     * Get url for category data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if (!($category instanceof Mage_Catalog_Model_Category)) {
            $category = $this->_getCategoryInstance()
                ->setData($category->getData());
        }

        $url = $this->helper('salesperson')->getResultUrl($category->getName());
        
        return $url;
    }
}
