<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Selectortype
{
	public function toOptionArray()
    {
    	return array(
            array('value' => 'links', 'label'=>Mage::helper('salesperson')->__('Links')),
            array('value' => 'dropdown', 'label'=>Mage::helper('salesperson')->__('Dropdown')),
        );
    }
}