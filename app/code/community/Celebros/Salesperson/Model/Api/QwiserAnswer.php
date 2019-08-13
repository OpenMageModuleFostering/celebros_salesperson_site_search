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
Class Celebros_Salesperson_Model_Api_QwiserAnswer
{
	var $Id;
	var $ImageHeight;
	var $ImageSku;
	var $ImageUrl;
	var $ImageWidth;
	var $ProductCount;
	var $Text;
	var $Type;
	var $DynamicProperties;
	
	Function Celebros_Salesperson_Model_Api_QwiserAnswer($AnswerNode)
	{
		if(is_object($AnswerNode))
		{
			$this->Id = $AnswerNode->get_attribute("Id");
			$this->ImageHeight = $AnswerNode->get_attribute("ImageHeight");
			$this->ImageSku = $AnswerNode->get_attribute("ImageSku");
			$this->ImageUrl = $AnswerNode->get_attribute("ImageUrl");
			$this->ImageWidth = $AnswerNode->get_attribute("ImageWidth");
			$this->ProductCount = $AnswerNode->get_attribute("ProductCount");
			$this->Text = $AnswerNode->get_attribute("Text");
			$this->Type = $AnswerNode->get_attribute("Type");
			$this->DynamicProperties = GetQwiserSimpleStringDictionary(current($AnswerNode->get_elements_by_tagname("DynamicProperties")));
		}
	}
}
?>