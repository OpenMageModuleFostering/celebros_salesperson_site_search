<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Nonlead
{
    public function toOptionArray()
    {
    	return array(
            array('value' => 'top', 'label'=>Mage::helper('salesperson')->__('On top')),
            array('value' => 'left', 'label'=>Mage::helper('salesperson')->__('On left')),
            array('value' => 'right', 'label'=>Mage::helper('salesperson')->__('On right')),
        );
    }
}