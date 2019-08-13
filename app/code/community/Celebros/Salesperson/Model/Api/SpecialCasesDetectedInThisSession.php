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
class Celebros_Salesperson_Model_Api_SpecialCasesDetectedInThisSession
{
	var $Count = 0;	//the number of values.
	var $Items;	//indexer 
	
	Function Celebros_Salesperson_Model_Api_SpecialCasesDetectedInThisSession($xml_SpecialCasesDetectedInThisSession)
	{
		if(is_object($xml_SpecialCasesDetectedInThisSession))
		{
			$xml_valuesNodes = $xml_SpecialCasesDetectedInThisSession->child_nodes();
			$xml_valuesNodes = getDomElements($xml_valuesNodes);
			$this->Count = count($xml_valuesNodes);
			
			for ($i = 0 ; $i <= $this->Count - 1;$i++)
			{
				$ValueNode = $xml_valuesNodes[$i];
				$this->Items[$i] = $ValueNode->node_value();
			}
		}
	}
	
	public function ToSimpleString() {
		return implode("^", $this->Items);
	}
}