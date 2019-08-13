<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Layer_State extends Varien_Object
{
    /**
     * Add filter item to layer state
     *
     * @param   Mage_Catalog_Model_Layer_Filter_Item $filter
     * @return  Mage_Catalog_Model_Layer_State
     */
    public function addFilter($filter)
    {
        $filters = $this->getFilters();
        $filters[] = $filter;
        $this->setFilters($filters);
        return $this;
    }
    
    public function removeFilter($answerId){
    	$filters = array();
    	foreach($this->getFilters() as $filter){
    		if($filter['answers']->Items[0]->Id == $answerId){
    			continue;
    		}
    		$filters[] = $filter;
    	}
    	$this->setFilters($filters);
    }

    /**
     * Set layer state filter items
     *
     * @param   array $filters
     * @return  Mage_Catalog_Model_Layer_State
     */
    public function setFilters($filters)
    {
        if (!is_array($filters)) {
            Mage::throwException(Mage::helper('salesperson')->__('Filters must be as array'));
        }
        $this->setData('filters', $filters);
        return $this;
    }

    /**
     * Get applied to layer filter items
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = $this->getData('filters');
        if (is_null($filters)) {
            $filters = array();
            $this->setData('filters', $filters);
        }
        return $filters;
    }
}
