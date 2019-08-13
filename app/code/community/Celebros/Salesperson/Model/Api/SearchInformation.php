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
class Celebros_Salesperson_Model_Api_SearchInformation
{
	var $Query;
	var $OriginalQuery;
	var $SearchProfileName;
	var $PriceFieldName;
	var $NumberOfPages;	
	var $CurrentPage;
	var $PageSize;
	var $IsDefaultPageSize;
	var $IsDefaultSearchProfileName;
	var $SkuSearchOccured;
	var $DeadEndOccurred;
	var $FirstQuestionId;
	var $SessionId;
	var $Stage;
	var $SortingOptions;
	var $AnsweredAnswers;
	var $SpecialCasesDetectedInThisSession;
	var $MinMatchClassFound= 0;
	var $MaxMatchClassFound = 5;

	Function Celebros_Salesperson_Model_Api_SearchInformation($xml_SearchInformation)
	{
		if(is_object($xml_SearchInformation))
		{
			$this->Query = $xml_SearchInformation->get_attribute("Query");
			$this->OriginalQuery = $xml_SearchInformation->get_attribute("OriginalQuery");
			$this->SearchProfileName = $xml_SearchInformation->get_attribute("SearchProfileName");
			$this->PriceFieldName = $xml_SearchInformation->get_attribute("PriceFieldName");
			$this->NumberOfPages = $xml_SearchInformation->get_attribute("NumberOfPages");
			$this->CurrentPage = $xml_SearchInformation->get_attribute("CurrentPage");
			$this->PageSize = $xml_SearchInformation->get_attribute("PageSize");
			$this->IsDefaultPageSize = $xml_SearchInformation->get_attribute("IsDefaultPageSize");
			$this->SkuSearchOccured = $xml_SearchInformation->get_attribute("SkuSearchOccured");
			$this->DeadEndOccurred = $xml_SearchInformation->get_attribute("DeadEndOccurred");
			$this->FirstQuestionId = $xml_SearchInformation->get_attribute("FirstQuestionId");
			$this->SessionId = $xml_SearchInformation->get_attribute("SessionId");
			$this->Stage = $xml_SearchInformation->get_attribute("Stage");
			$this->SortingOptions = Mage::getModel('salesperson/Api_SortingOptions', current($xml_SearchInformation->get_elements_by_tagname("SortingOptions")));
			$this->AnsweredAnswers = Mage::getModel('salesperson/Api_QwiserAnsweredAnswers', current($xml_SearchInformation->get_elements_by_tagname("AnsweredAnswers")));
			$this->SpecialCasesDetectedInThisSession = Mage::getModel('salesperson/Api_SpecialCasesDetectedInThisSession', current($xml_SearchInformation->get_elements_by_tagname("SpecialCasesDetectedInThisSession")));
		}
	}
	
	public function ToSearchHandle() {
		$builder = "";
		
		if (strlen($this->Query > 0))
		{
			$builder.= "A=" . str_replace("~", "~~", $this->Query) . "~";
		}
		if (strlen($this->OriginalQuery > 0))
		{
			$builder.= "B=" . str_replace("~", "~~", $this->OriginalQuery) . "~";
		}
		if ($this->CurrentPage != "0")
		{
			$builder.= "C=" . $this->CurrentPage . "~";
		}
		if ($this->IsDefaultPageSize != "true")
		{
			$builder.= "D=" . $this->PageSize . "~";
		}
		if (!$this->SortingOptions->IsDefault())
		{
			$builder.= "E=" . str_replace("~", "~~", $this->SortingOptions.ToString()) . "~";
		}
		if (strlen($this->FirstQuestionId) > 0)
		{
			$builder.= "F=" . str_replace("~", "~~", $this->FirstQuestionId) . "~";
		}
		if ($this->AnsweredAnswers->Count > 0)
		{
			$builder.= "G=" .  str_replace("~", "~~", $this->AnsweredAnswersToString()) . "~";
		}		
		if ($this->IsDefaultSearchProfileName!="true")
		{
			$builder.= "H=" . str_replace("~", "~~",  $this->SearchProfileName) . "~";
		}
		if (strlen($this->PriceFieldName) > 0)
		{
			$builder.= "I=" . str_replace("~", "~~",  $this->PriceFieldName) . "~";
		}
		if ($this->SpecialCasesDetectedInThisSession->Count > 0)
		{
			$builder.= "J=" . str_replace("~", "~~", $this->SpecialCasesDetectedInThisSessionToString());
		}
		if ($this->MaxMatchClassFound != 0)
		{
			$builder.= "K=" . $this->MaxMatchClassFound . "~";
		}
		if ($this->MinMatchClassFound != 0)
		{
			$builder.= "L=" . $this->MinMatchClassFound . "~";
		}
		if (!$this->IsDefaultNumberOfPages())
		{
			$builder.= "M=" . $this->NumberOfPages . "~";
		}
		if (!$this->IsDefaultStage())
		{
			$builder.= "N=" . $this->Stage . "~";
		}
		return $this->UUEncode6Bit($builder);
	}
	
	private function AnsweredAnswersToString() {
		return $this->AnsweredAnswers->ToSimpleString();
	}
	
	private function SpecialCasesDetectedInThisSessionToString()
	{
		return $this->SpecialCasesDetectedInThisSession->ToSimpleString();
	}
	
	private function IsDefaultNumberOfPages() {
		return $this->NumberOfPages == "0";
	}
	
	private function IsDefaultStage() {
		return $this->Stage == "1";
	}
	
	private function GetCharCode($intCode) {
		if($intCode >= 0 && $intCode <= 25)
		{
			$intCode += 65;//ASCII of A
		}
		else if($intCode >= 26 && $intCode <= 51)
		{
			$intCode += 97 - 26;//ASCII of a
		}
		else if($intCode >= 52 && $intCode <= 61)
		{
			$intCode +=  48 - 52;
		}
		else if ($intCode == 62)
		{
			$intCode = 95;//ASCII of _
		}
		else
		{
			$intCode = 45;//ASCII of -
		}
		return chr($intCode);
		
	}
	
	private function UUEncode6Bit($strInput) {
		$sbCodedString  = "";
		
		if(empty($strInput)) return $sbCodedString;
		
		$arrInput = str_split($strInput);
		$arrByteInput = array();
		for($i=0; $i < count($arrInput) ; $i++) {
			$arrByteInput[] = ord($arrInput[$i]);
		}
		
		$iGroupsNum = (int)ceil(count($arrByteInput)/3);
		$intCode = 0;
		$intReminder = 0;
		$b = 0;
		$iOffset = 0;
		
		for($i=0; $i <$iGroupsNum ; $i++)
		{
			$iOffset = 3*$i;
			$b = $arrByteInput[$iOffset];
			$intCode = (128 + 64 + 32 + 16 + 8 +4) & $b; //gets the upper 6 bites of the byte
			$intCode = $intCode>>2; // shift 2 times right
			$sbCodedString .= $this->GetCharCode($intCode);// this will be our first char
			$intReminder = (2 + 1)& $b;// take the 2 reminded bits
			$intReminder = $intReminder << 4;//shift them 4 bits right (those bits will be the upper 4 bits of the constructed char code)
			if($iOffset + 1 < count($arrByteInput))//check if there is another byte in the array
			{
				$b = $arrByteInput[$iOffset + 1];//take the next byte
				$intCode = (128 + 64 + 32 + 16) & $b;//get it's 4 upper bits
				$intCode = $intCode >> 4;//and shift them so they will be the 4 lowest bits
				//the next char code will be constructed from the 2 bits of the former byte
				//and the 4 first of the current.
				$sbCodedString .= $this->GetCharCode($intCode + $intReminder);
				//Get the 4 last bits of the current byte
				$intReminder = (8 + 4 + 2 + 1)& $b;
				//shift them 2 bits left
				$intReminder = $intReminder << 2;
				//check if there is another byte in the array
				if($iOffset + 2 < count($arrByteInput))
				{
					$b = $arrByteInput[$iOffset + 2];//take the next byte
					$intCode = (128 + 64) & $b;//get it's 2 upper bits
					$intCode = $intCode >> 6;//and shift them 6 bits left
					//the next char code will be constructed from the 4 bits of the former byte and the 2 upper bits
					//of the current byte.
					$sbCodedString .= $this->GetCharCode($intCode + $intReminder);
					//Take the 6 reminded bits of the current byte. They will construct the last char code
					$intReminder = (32 + 16 + 8 + 4 + 2 + 1) & $b;
					$sbCodedString .= $this->GetCharCode($intReminder);
				}
				else
				{
					$sbCodedString .= $this->GetCharCode($intReminder);
					break;
		
				}
			}
			else
			{
				$sbCodedString .= $this->GetCharCode($intReminder);
				break;
			}
		}
		return $sbCodedString;
	}
}

?>