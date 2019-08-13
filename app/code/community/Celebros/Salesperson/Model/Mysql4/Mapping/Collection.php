<?php
class Celebros_Salesperson_Model_Mysql4_Mapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    protected function _construct()
    {
            $this->_init('salesperson/mapping');
            $this->setOrder('xml_field', Varien_Data_Collection::SORT_ORDER_ASC);
    }
} 