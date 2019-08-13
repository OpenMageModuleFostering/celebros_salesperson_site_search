<?php

class Celebros_Salesperson_Helper_Mapping extends Mage_CatalogSearch_Helper_Data
{

	/**
	 * Retrieve Mapping array
	 *
	 * @return array
	 */
	
	public function getMappingFieldsArray(){
		return Mage::getSingleton("salesperson/mapping")->getFieldsArray();
	}
	
 	/**
	 * Retrieve a mapping for a field
	 * 
	 * @return string
	 * 
	 */
	public function getMapping($code_field = ""){
		$mappingArray = $this->getMappingFieldsArray();
		if(!key_exists($code_field, $mappingArray)){
			return $code_field;
		}
		return strtolower($mappingArray[$code_field]);
	}
}