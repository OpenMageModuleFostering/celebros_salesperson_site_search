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
Class Celebros_Salesperson_Model_Api_QwiserAnsweredAnswers
{
	var $Count = 0;
	var $Items = array();
	
	Function Celebros_Salesperson_Model_Api_QwiserAnsweredAnswers($xml_AnsweredAnswers)
	{
		//if we got an array of QwiserAnswer
		if(is_array($xml_AnsweredAnswers))
		{
			$this->Items = $xml_AnsweredAnswers;
			$this->Count = count($xml_AnsweredAnswers);
		}
		//if we got a node of QwiserAnsweredAnswers
		else
		{	
			if(is_object($xml_AnsweredAnswers))
			{	
				$xml_AnsweredAnswersNodes = $xml_AnsweredAnswers->child_nodes();
				$xml_AnsweredAnswersNodes = getDomElements($xml_AnsweredAnswersNodes);
				$this->Count = count($xml_AnsweredAnswersNodes);

				for ($i = 0 ; $i <= $this->Count - 1;$i++)
				{
					$AnsweredAnswerNode = $xml_AnsweredAnswersNodes[$i];
					$key = $AnsweredAnswerNode->get_attribute("AnswerID");
					$this->Items[$key] = Mage::getModel('salesperson/Api_QwiserAnsweredAnswer', $AnsweredAnswerNode);
				}
			}
		}
	}
	
	public function ToSimpleString(){
		$builder = "";
		
		for ($i = 0 ; $i <= $this->Count - 1;$i++)
		{
			$answer = $this->Items[$i];
			$builder .= $answer->AnswerId;
			$builder .= "^";
			$builder .= $this->_effectOnSearchPathToInt($answer->EffectOnSearchPath);
		}
		
		$builder = substr($builder,0,-1);
		return $builder;
	}
	
	private function _effectOnSearchPathToInt($strEffectOnSearchPath)
	{
		switch ($strEffectOnSearchPath) {
			case "Exclude":
				return 0;
				break;
			case "ExactAnswerNode":
				return 1;
				break;
			case "EntireAnswerPath":
				return 2;
				break;
		}
	}
	
	
	
	
}
?>