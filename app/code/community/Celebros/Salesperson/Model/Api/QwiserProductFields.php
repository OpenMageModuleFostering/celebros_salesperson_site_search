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
class Celebros_Salesperson_Model_Api_QwiserProductFields
{
	var $Count;
	var $Items;
	
	Function Celebros_Salesperson_Model_Api_QwiserProductFields($xml_ProductFields)
	{
		if(is_object($xml_ProductFields))
		{
			$xml_ProductFieldsNodes = $xml_ProductFields->child_nodes();
			$xml_ProductFieldsNodes = getDomElements($xml_ProductFieldsNodes);
			$this->Count = count($xml_ProductFieldsNodes);

			for ($i = 0 ; $i <= $this->Count - 1;$i++)
			{
				$ProductFieldNode = $xml_ProductFieldsNodes[$i];
				$this->Items[$i] = Mage::getModel('salesperson/Api_QwiserProductField', $ProductFieldNode);
			}
		}	
	}
}
?>