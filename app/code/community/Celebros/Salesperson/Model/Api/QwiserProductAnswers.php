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
class Celebros_Salesperson_Model_Api_QwiserProductAnswers
{
	var $Count = 0;
	var $Items = array();
	Function Celebros_Salesperson_Model_Api_QwiserProductAnswers($xml_ProductAnswers)
	{
		if(is_object($xml_ProductAnswers))
		{
			$xml_ProductAnswersNodes = $xml_ProductAnswers->child_nodes();
			$xml_ProductAnswersNodes = getDomElements($xml_ProductAnswersNodes);
			$this->Count = count($xml_ProductAnswersNodes);

			for ($i = 0 ; $i <= $this->Count - 1;$i++)
			{
				$ProductAnswerNode = $xml_ProductAnswersNodes[$i];
				$key = $ProductAnswerNode->$AnsweredAnswerNode->get_attribute("Id");
				$this->Items[$key] = Mage::getModel('salesperson/Api_QwiserProductAnswer', $ProductAnswerNode);
			}
		}	
	}
}
?>