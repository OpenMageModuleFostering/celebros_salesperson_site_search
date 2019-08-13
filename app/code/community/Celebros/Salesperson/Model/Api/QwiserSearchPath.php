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
class Celebros_Salesperson_Model_Api_QwiserSearchPath
{
	var $Count = 0;
	var $Items;
	
	Function Celebros_Salesperson_Model_Api_QwiserSearchPath($xml_SearchPath)
	{
		if(is_object($xml_SearchPath))
		{
			$xml_SearchPathNodes = $xml_SearchPath->child_nodes();
			$xml_SearchPathNodes = getDomElements($xml_SearchPathNodes);
			$this->Count = count($xml_SearchPathNodes);
		
			for ($i = 0 ; $i < $this->Count;$i++)
			{
				$SearchPathNode = $xml_SearchPathNodes[$i];
				$this->Items[$i] = Mage::getModel('salesperson/Api_QwiserSearchPathEntry', $SearchPathNode);
			}
		}	
	} 
}
?>