<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 */
class Celebros_Salesperson_Model_Api_SortingOptions
{
	var $Ascending;
	var $FieldName;
	var $NumericSort;
	var $Method;
	
	Function Celebros_Salesperson_Model_Api_SortingOptions($xml_SortingOptions)
	{
		if(is_object($xml_SortingOptions))
		{
			$this->Ascending = $xml_SortingOptions->get_attribute("Ascending");
			$this->FieldName = $xml_SortingOptions->get_attribute("FieldName");
			$this->NumericSort = $xml_SortingOptions->get_attribute("NumericSort");
			$this->Method = $xml_SortingOptions->get_attribute("Method");
		}
	}
	
	public function ToString(){
		$builder = "";
		if($this->Ascending == "true") $builder .= "1";
		else $builder .= "0";
		$builder .= "^";
		if($this->NumericSort == "true") $builder .= "1";
		else $builder .= "0";
		$builder .= "^";
		$builder .= $this->_sortMethodToInt($this->Method);
		$builder .= "^";
		$builder .= $this->FieldName;
		
		return $builder;
	}
	
	
	public function IsDefault()
	{
		return (($this->Ascending != "true" && $this->NumericSort != "true") && ((strlen($this->FieldName) == 0) && ($this->Method == "Relevancy")));
	}

	private function _sortMethodToInt($strSortMethod){
		switch ($strSortMethod) {
			case "Price":
				return 0;
				break;
			case "Relevancy":
				return 1;
				break;
			case "SpecifiedField":
				return 2;
				break;
		}
	}
}
?>