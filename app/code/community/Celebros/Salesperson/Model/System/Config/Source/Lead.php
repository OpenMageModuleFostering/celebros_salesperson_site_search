<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Lead
{
    public function toOptionArray()
    {
    	return array(
            array('value' => 1, 'label'=>Mage::helper('salesperson')->__('On top')),
            array('value' => 0, 'label'=>Mage::helper('salesperson')->__('None')),
        );
    }
}