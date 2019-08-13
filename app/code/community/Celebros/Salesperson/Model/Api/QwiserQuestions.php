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
class Celebros_Salesperson_Model_Api_QwiserQuestions
{
	var $Count = 0;
	var $Items = array();
	
	Function Celebros_Salesperson_Model_Api_QwiserQuestions($xml_Questions)
	{
		if(is_array($xml_Questions))
		{
			$this->Items = $xml_Questions;
			$this->Count = count($xml_Questions);
		}
		else
		{
			if(is_object($xml_Questions))
			{
				$xml_questionsNodes = $xml_Questions->child_nodes();
				$xml_questionsNodes = getDomElements($xml_questionsNodes);
				$this->Count = count($xml_questionsNodes);

				for ($i = 0 ; $i <= $this->Count - 1;$i++)
				{
					$QuestionNode = $xml_questionsNodes[$i];
					$key = $QuestionNode->get_attribute("Id");
					$this->Items[$key] = Mage::getModel('salesperson/Api_QwiserQuestion', $QuestionNode);
				}
			}
		}

	}
	
	Function GetAllQuestions(){
		return $this->Items;
	}
	
	//get a question by its id .
	Function GetQuestionById($ID)
	{
		foreach ($this->Items as $q)
		{
			if($q->Id=$ID)
			{
				return $q;
			}
		}
	}
	
	//get all questions with the given side text
	Function GetQuestionsBySideText($SideText)
	{
		$qArray = array();	
		foreach ($this->Items as $q)
		{
			if($q->SideText=$SideText)
			{
				$qArray[] = $q;	
			}
		}
		return 	$qArray;
	}
	
	//get all question with the given text .
	Function GetQuestionsByText($QuestionText)
	{
		$qArray = array();
		foreach ($this->Items as $q)
		{
			if($q->Text=$QuestionText)
			{
				$qArray[] = $q;	
			}
		}
		return $qArray;	
	}
	
	//get all question with the given type .
	Function GetQuestionsByType($Type)
	{
		$qArray = array();
		foreach ($this->Items as $q)
		{
			if($q->Type=$Type)
			{
				$qArray[] = $q;	
			}
		}
		return $qArray;	
		
	}
} 
?>