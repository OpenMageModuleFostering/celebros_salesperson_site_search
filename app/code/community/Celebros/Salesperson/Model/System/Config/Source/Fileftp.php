<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Fileftp
{
    public function toOptionArray()
    {
    	return array(
            array('value' => 'file', 'label'=>Mage::helper('salesperson')->__('File')),
            array('value' => 'ftp', 'label'=>Mage::helper('salesperson')->__('FTP')),
        );
    }
}