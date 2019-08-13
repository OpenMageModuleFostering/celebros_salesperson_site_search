<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Navigationtosearch
{
    public function toOptionArray()
    {
    	return array(
    		array('value' => 'category', 'label'=>Mage::helper('salesperson')->__('Category name')),
            array('value' => 'full_path', 'label'=>Mage::helper('salesperson')->__('Full category path')),
    		array('value' => 'category_and_parent', 'label'=>Mage::helper('salesperson')->__('Category and category parent name')),    			
    		array('value' => 'category_and_root', 'label'=>Mage::helper('salesperson')->__('Category and category root name')),
        );
    }
}