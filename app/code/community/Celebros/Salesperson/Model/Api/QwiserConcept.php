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
class Celebros_Salesperson_Model_Api_QwiserConcept 
{
	var $Id; //the concept id.
	var $Name; //the concept name.
	var $Rank; //the concept rank.
	var $Type; //the concept type(attribute,commodity,temathic). 
	var $DynamicProperties; //the concept dynamic properties.
	
	function Celebros_Salesperson_Model_Api_QwiserConcept($ConceptNode)
	{
		if(is_object($ConceptNode))
		{
			$this->Id = $ConceptNode->get_attribute("Id");
			$this->Name = $ConceptNode->get_attribute("Name");
			$this->Rank = $ConceptNode->get_attribute("Rank");
			$this->Type = $ConceptNode->get_attribute("Type");
			$this->DynamicProperties = GetQwiserSimpleStringDictionary(current($ConceptNode->get_elements_by_tagname("DynamicProperties")));
		}
	}
}
?>