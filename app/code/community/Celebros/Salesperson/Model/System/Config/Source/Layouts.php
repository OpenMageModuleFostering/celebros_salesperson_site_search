<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Source_Layouts{
    
	protected $_options;
    
    public function toOptionArray()
    {
        if (!$this->_options) {
			$this->_options = array(
				array( 'value'=>'salesperson/1column.phtml','label'=>'1 column'),
				array( 'value'=>'salesperson/2columns-left.phtml','label'=>'2 columns with left bar'),
				array( 'value'=>'salesperson/2columns-right.phtml','label'=>'2 columns with right bar'),
				array( 'value'=>'salesperson/3columns.phtml','label'=>'3 columns'),
			);
		}
        return $this->_options;
    }
}
