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
class Celebros_Salesperson_Model_Api_QwiserSearchResults
{
	var $xml_root;
	var $QwiserSearchResults;
	var $QwiserErrorOccurred;
	var $QwiserErrorMessage;
	var $SearchInformation;
	var $Questions;
	var $SearchPath;
	var $Products;
	var $QueryConcepts;
	var $SpellerInformation ;
	var $RelatedSearches;
	var $SpecialCasesDetectedInThisSearch;
	
	Function Celebros_Salesperson_Model_Api_QwiserSearchResults($root)
	{
		$this->xml_root = $root;
		$this->QwiserSearchResults = current($this->xml_root->get_elements_by_tagname("QwiserSearchResults"));
		$this->QwiserErrorOccurred = (bool)$this->xml_root->get_attribute("ErrorOccurred");
		$this->QwiserErrorMessage = current($this->xml_root->get_elements_by_tagname("QwiserError"));
		$this->SearchInformation = Mage::getModel('salesperson/Api_SearchInformation', current($this->xml_root->get_elements_by_tagname("SearchInformation")));
		$this->Questions =	Mage::getModel('salesperson/Api_QwiserQuestions', current($this->xml_root->get_elements_by_tagname("Questions")));
		$this->SearchPath = Mage::getModel('salesperson/Api_QwiserSearchPath', current($this->xml_root->get_elements_by_tagname("SearchPath")));
		$this->Products = Mage::getModel('salesperson/Api_QwiserProducts', current($this->xml_root->get_elements_by_tagname("Products")));
		$this->QueryConcepts = Mage::getModel('salesperson/Api_QwiserConcepts', current($this->xml_root->get_elements_by_tagname("QueryConcepts")));
		$this->SpellerInformation = Mage::getModel('salesperson/Api_QwiserSpellerInformation', current($this->xml_root->get_elements_by_tagname("SpellerInformation")));
		$this->RelatedSearches  = GetQwiserSimpleStringCollection(current($this->xml_root->get_elements_by_tagname("RelatedSearches")));
		$this->SpecialCasesDetectedInThisSearch  = current($this->xml_root->get_elements_by_tagname("SpecialCasesDetectedInThisSearch"));
	}
	
	Function GetErrorOccurred(){
		return $this->QwiserErrorOccurred;
	}
	
	Function GetErrorMessage(){
		if ($this->GetErrorOccurred()){
			return $this->QwiserErrorMessage->get_attribute("ErrorMessage");
		}
	}
	
	Function GetExactMatchFound()
	{
		return $this->QwiserSearchResults->get_attribute("ExactMatchFound");
	}
	
	Function GetLogHandle()
	{
		return $this->QwiserSearchResults->get_attribute("LogHandle");
	}
	
	Function GetSearchHandle()
	{
		return $this->QwiserSearchResults->get_attribute("SearchHandle");
	}
	
	Function GetMaxMatchClassFound()
	{
			return $this->QwiserSearchResults->get_attribute("MaxMatchClassFound");
	}
	
	Function GetMinMatchClassFound()
	{
			return $this->QwiserSearchResults->get_attribute("MinMatchClassFound");
	}	
	
	Function GetRecommendedMessage()
	{
			return $this->QwiserSearchResults->get_attribute("RecommendedMessage");
	}
	
	Function GetRedirectionUrl()
	{
			return $this->QwiserSearchResults->get_attribute("RedirectionUrl");
	}
	
	Function GetRelevantProductsCount()
	{
			return $this->QwiserSearchResults->get_attribute("RelevantProductsCount");
	}
	
	Function GetSearchDataVersion()
	{
			return $this->QwiserSearchResults->get_attribute("SearchDataVersion");
	}
	
	Function GetSearchEngineTimeDuration()
	{
			return $this->QwiserSearchResults->get_attribute("SearchEngineTimeDuration");
	}
	
	Function GetSearchTimeDuration()
	{
			return $this->QwiserSearchResults->get_attribute("SearchTimeDuration");
	}
	
	Function GetSearchStatus()
	{
		return $this->QwiserSearchResults->get_attribute("SearchStatus");
	}
	
	/*Function GetLogHandle()
	{
	  return $this->QwiserSearchResults->get_attribute("LogHandle");
	}*/
		
}


?>