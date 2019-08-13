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
class Celebros_Salesperson_Model_Api_QwiserProductField 
{
	var $FieldType;
	var $Name;

	
	Function Celebros_Salesperson_Model_Api_QwiserProductField($ProductFieldNode)
	{
		if(is_object($ProductFieldNode))
		{
			$this->FieldType = $ProductFieldNode->get_attribute("FieldType");
			$this->Name = $ProductFieldNode->get_attribute("Name");
		}
	}
}
?>