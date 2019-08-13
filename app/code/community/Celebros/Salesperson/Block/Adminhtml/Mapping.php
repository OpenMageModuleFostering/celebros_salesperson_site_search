<?php

class Celebros_Salesperson_Block_Adminhtml_Mapping extends Mage_Adminhtml_Block_Widget{
	
	private $_fieldsCollection;
	
	/**
	 * Load fields collection
	 *
	 * @return Celebros_Salesperson_Model_Mysql4_Mapping_Collection
	 */
	protected function _loadFieldsCollection(){
		if(!$this->_fieldsCollection){
			$this->_fieldsCollection = Mage::getSingleton("salesperson/mapping")->getCollection();
		}
		return $this->_fieldsCollection;
	}
	
	/**
	 * Get fields collection
	 * 
	 * @return Celebros_Salesperson_Model_Mysql4_Mapping_Collection
	 *
	 */
	public function getFields(){
		return $this->_loadFieldsCollection();
	}
}