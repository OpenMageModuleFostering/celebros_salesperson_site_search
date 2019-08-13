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
class Celebros_Salesperson_Model_Api_QwiserQuestion
{
	var $Id;
	var $Rank;
	var $SideText;
	var $Text;
	var $Type;
	var $HasMoreAnswers;
	var $ExtraAnswers;
	var $DynamicProperties;
	var $Answers;
	
	Function Celebros_Salesperson_Model_Api_QwiserQuestion($QuestionNode)
	{
		if(is_object($QuestionNode))
		{
			$this->Id = $QuestionNode->get_attribute("Id");
			$this->Rank = $QuestionNode->get_attribute("Rank");
			$this->SideText = $QuestionNode->get_attribute("SideText");
			$this->Text	= $QuestionNode->get_attribute("Text");
			$this->Type = $QuestionNode->get_attribute("Type");
			$this->Answers = Mage::getModel('salesperson/Api_QwiserAnswers', current($QuestionNode->get_elements_by_tagname("Answers")));
			$this->ExtraAnswers = Mage::getModel('salesperson/Api_QwiserAnswers', current($QuestionNode->get_elements_by_tagname("ExtraAnswers")));
			$this->HasMoreAnswers = ($this->ExtraAnswers->Count > 0) ? true : false;
			
			//Question dynamic properties
			$QuestionDynamicProperties = null;
			$childNodes = $QuestionNode->child_nodes();
			$childNodes = getDomElements($childNodes);			
			for ($i = 0 ; $i <= count($childNodes) - 1;$i++)
			{
				$childNode = $childNodes[$i];
				if($childNode->node_name() == "DynamicProperties") {
					$QuestionDynamicProperties = $childNode;
				}
			}
			
			$this->DynamicProperties =  GetQwiserSimpleStringDictionary($QuestionDynamicProperties);
		}
	}
}
?>