<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Pagenavtype
{
	public function toOptionArray()
    {
    	return array(
            array('value' => 'textual', 'label'=>Mage::helper('salesperson')->__('Limited')),
            array('value' => 'multipage', 'label'=>Mage::helper('salesperson')->__('Full')),
        );
    }
}