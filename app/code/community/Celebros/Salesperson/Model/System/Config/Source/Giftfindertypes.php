<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_GiftFinderTypes{
    
	protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options = array(
				array( 'value'=>'salesperson/giftfinder/view.phtml','label'=>'Standard'),
				array( 'value'=>'salesperson/giftfinder/viewdynamic.phtml','label'=>'Dynamic'),
				array( 'value'=>'salesperson/giftfinder/viewajaxdynamic.phtml','label'=>'Dynamic Ajax'),
			);
		}
        return $this->_options;
    }
}
