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
Class Celebros_Salesperson_Model_Api_QwiserAnsweredAnswer
{
	var $AnswerId;
	var $EffectOnSearchPath;

	Function Celebros_Salesperson_Model_Api_QwiserAnsweredAnswer($AnsweredAnswerNode)
	{
		if(is_object($AnsweredAnswerNode))
		{
			$this->AnswerId = $AnsweredAnswerNode->get_attribute("AnswerID");
			$this->EffectOnSearchPath = $AnsweredAnswerNode->get_attribute("EffectOnSearchPath");
		}
	}
}
?>