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
class Celebros_Salesperson_Model_Api_QwiserConcepts
{
	var $Count = 0; //number of concepts.
	var $Items; //QwiserConcept array
	
	function Celebros_Salesperson_Model_Api_QwiserConcepts($xml_Concepts)
	{
		if(is_object($xml_Concepts))
		{
			$xml_ConceptsNodes = $xml_Concepts->child_nodes();
			$xml_ConceptsNodes = getDomElements($xml_ConceptsNodes);
			$this->Count = count($xml_ConceptsNodes);

			for ($i = 0 ; $i <= $this->Count - 1;$i++)
			{
				$ConceptNode = $xml_ConceptsNodes[$i];
				$this->Items[$i] = Mage::getModel('salesperson/Api_QwiserConcept', $ConceptNode);
			}
		}
	}
}

?>