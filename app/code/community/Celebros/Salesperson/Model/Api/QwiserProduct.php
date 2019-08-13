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
	class Celebros_Salesperson_Model_Api_QwiserProduct
	{
		var $Field;
		var $FoundInAnswerId;
		var $FoundInAnswerPath;
		var $IsBestSeller;
		var $MatchClassFound;
		var $Price;
		var $Sku;
		
	function Celebros_Salesperson_Model_Api_QwiserProduct($ProdNode)
	{
		if(is_object($ProdNode))
		{
			$this->Field = $this->GetProductCommonInformation($ProdNode);
			$this->FoundInAnswerId = $ProdNode->get_attribute("FoundInAnswerId");
			$this->FoundInAnswerPath = $ProdNode->get_attribute("FoundInAnswerPath");
			$this->IsBestSeller = $ProdNode->get_attribute("IsBestSeller");
			$this->MatchClassFound = $ProdNode->get_attribute("MatchClassFound");
			$this->Price = $ProdNode->get_attribute("Price");
			$this->Sku = $ProdNode->get_attribute("Sku");
		}
	}	
		
	function GetProductCommonInformation($ProdNode)
	{
		$ProductFields=current(getDomElements($ProdNode->child_nodes()));
		$ProductFieldsArray=getDomElements($ProductFields->child_nodes());
		foreach ($ProductFieldsArray as $Pfield)
		{
		
			/**
				* Make Fields keys lowercase
				* 
				* @modifyed by Sveta Oksen - sveta.oksen@gmail.com
				* @since 28/03/2011
			*/
			$ProdField[strtolower($Pfield->get_attribute("name"))] = $Pfield->get_attribute("value");
		}
		return $ProdField;
	} 
	}
?>