<?php 

class Celebros_Salesperson_Model_Mapping extends Mage_Core_Model_Abstract 
{
	private $_fieldsArray; 
	
    protected function _construct()
    {
        $this->_init('salesperson/mapping');
    }   
    
    protected function _loadFieldsArray(){
    	$fieldsCollection = Mage::getSingleton("salesperson/mapping")->getCollection();
    	$this->_fieldsArray = array();
    	foreach($fieldsCollection as $field){
    		$this->_fieldsArray[$field->getCodeField()] = $field->getXmlField();
    	}
    }
    
    /**
     * Get Fields Array 
     * 
     * @return array
     */
    public function getFieldsArray(){
    	if(!$this->_fieldsArray){
    		$this->_loadFieldsArray();
    	}
    	return $this->_fieldsArray;
    }
    
} 