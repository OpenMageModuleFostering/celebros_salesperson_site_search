<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Celebros - Pavel Feldman (email: MagentoSupport@celebros.com)
 *
 */
class Celebros_Salesperson_Block_Giftfinder_View extends Celebros_Salesperson_Block_Layer_View
{
	const CATALOG_CATEGORY_ATTRIBUTE_ENTITY_TYPE = '9';
	const CATALOG_PRODUCT_ATTRIBUTE_ENTITY_TYPE = '10';
	
	/**
	 * Runs empty query search if it has not ran yet
	 *
	 * @return Celebros_Salesperson_Block_Giftfinder_View
	 */
	protected function _prepareLayout()
	{
		$defaultSortBy = $this->getDefaultSortBy();
		$bNumericsort = $this->getIsNumericSort($defaultSortBy);
		
		if(!$this->getQwiserSearchResults()) {
			$salesperson = Mage::helper('salesperson')->getSalespersonApi();
			$salesperson->SearchAdvance(
					"", //Query
					Mage::Helper('salesperson')->getStoreGiftfinderProfile(),//SearchProfile
					'',//AnswerId
					'',//EffectOnSearchPath
					'price',//PriceColumn
					Mage::Helper('salesperson')->getDefaultPageSize(),//PageSize
					$defaultSortBy,//Sortingfield
					$bNumericsort,
					true
			);
		}
		
		if(!$this->getQwiserSearchResults()) return;
		
		//$this->saveToSessionFiltersSideText();
		$this->saveToSessionInitialFilters();
		
		return parent::_prepareLayout();
	}
	
	protected function getDefaultSortBy()
	{
		return Mage::helper('salesperson')->getDefaultSortBy();
	}
	
	protected function getIsNumericSort($sortBy)
	{
		$bNumericsort = 1;
		
		if($sortBy == 'price') { 
			$bNumericsort = 1;
		}
		else {
			$sortableAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(self::CATALOG_PRODUCT_ATTRIBUTE_ENTITY_TYPE,$sortBy);
			
			if ($sortableAttribute->getBackendType() != 'int'){
				$bNumericsort = 0;
			}
		}
		
		return $bNumericsort;
	}
	
	/*public function getFilters() {
		$filters = Mage::registry('giftfinder_filters');
		if(!isset($filters))
		{
			$filters = array();
			$tmpFilters = parent::getFilters();
			
			
			
			foreach ($tmpFilters as $filter) {
				if ($this->isAnsweredQuestion($filter))
					continue;
				$filters[$filter->Id] = $filter;
			}
			
			Mage::register('giftfinder_filters', $filters);
		}
		
		return $filters;
	}*/
	
	public function getMissingInitialFilters(){
		return Mage::registry('missingInitialFilters');
	}

	public function setMissingInitialFilters($missingInitialFilters){
		Mage::register('missingInitialFilters', $missingInitialFilters);
	}
	
	public function isDisabledFilter($filter)
	{
		$missingInitialFilters = $this->getMissingInitialFilters();
		return isset($missingInitialFilters[$filter->Id]);
	}
	
	public function getOrderedFilters()
	{
		$filters = $this->getFilters();
		
		if(!count($filters)) return array();
		$initialFilters = Mage::getSingleton('salesperson/session')->getInitialFilters();
		
		
		$missingInitialFilters = array();
		foreach ($initialFilters as $filter)
		{
			if(!isset($filters[$filter->Id]))
			{
				$missingInitialFilters[$filter->Id] = $filter;
			}
		}
		$this->setMissingInitialFilters($missingInitialFilters);
		
		foreach ($filters as $filter) 
		{
			$initialFilters[$filter->Id] = $filter;
		}
		
		//Updates the price question if requuires
		//e.g. when price slider is set and this is not a first search stage
		//$results = $this->getQwiserSearchResults();
		//$filters = $this->handlePriceQuestion($initialFilters, $results->SearchInformation->Stage);
		
		Mage::getSingleton('salesperson/session')->setInitialFilters($initialFilters);
		return $initialFilters;
	}
	
	protected function saveToSessionFiltersSideText(){
		$filters = parent::getFilters();
		if(isset($filters)){
			foreach ($filters as $filter) {
				$questionId = $filter->Id;
				$setFunc = "set" . $questionId . "SideText";
				Mage::getSingleton('salesperson/session')->$setFunc($filter->SideText);
			}
		}
	}
	
	protected function saveToSessionInitialFilters(){
		$results = $this->getQwiserSearchResults();
		if($results->SearchInformation->Stage == 1) {
			Mage::getSingleton('salesperson/session')->setInitialFilters(parent::getFilters());
		}
	}
	
	protected function getFromSessionFilterSideText($questionId){
		$getFunc = "get" . $questionId . "SideText";
		return Mage::getSingleton('salesperson/session')->$getFunc();
	}
	
	protected function getFilterOptions($filter)
	{
		$options = array();
		
		if(!$this->isAnsweredQuestion($filter)) {
			$options[] = array(
					'label' => 'Select ' . $filter->SideText,
					'value' => '',
					'params'  => array("disabled"=>"disabled")
			);
		}
	
		foreach ($filter->Answers->Items as $answer) {
			$value = (Mage::helper('salesperson')->isGiftfinderStandardTemplate()) ? $answer->Id : $this->answerQuestionUrl($answer->Id) ;
			$options[] = array(
					'label' => $answer->Text,
					'value' => $value
			);
		}
		
		if(isset($filter->ExtraAnswers->Items)) {
			foreach ($filter->ExtraAnswers->Items as $answer) {
				$value = (Mage::helper('salesperson')->isGiftfinderStandardTemplate()) ? $answer->Id : $this->answerQuestionUrl($answer->Id) ;
				$options[] = array(
						'label' => $answer->Text,
						'value' => $value
				);
			}
		}
	
		return $options;
	}	
	
	public function getDisabledFilterHtmlSelect ($sideText) {
		$options[] = array(
				'label' => 'Select ' . $sideText,
				'value' => '',
				'params'  => array("disabled"=>"disabled")
		);
		
		$extraParams = 'disabled="disabled"';
		
		$select  = Mage::app()->getLayout()->createBlock('core/html_select')
		->setClass('validate-select')
		->setExtraParams($extraParams)
		->setValue('')
		->setOptions($options);
		return $select->getHtml();
	}
	
	public function getFilterHtmlSelect($filter)
	{
		$extraParams = (Mage::helper('salesperson')->isGiftfinderStandardTemplate()) ? null : 'onchange="celebrosSetLocation(this.options[this.selectedIndex].value)"' ;
		
		$bDisabled = $this->isDisabledFilter($filter);
		$disabledOptions[] = array(
				'label' => 'Select ' . $filter->SideText,
				'value' => '',
				'params'  => array("disabled"=>"disabled")
		);
		
		
		if($this->isAnsweredQuestion($filter) || $bDisabled) {
			$extraParams = 'disabled="disabled"';
		}
		
		$options = $bDisabled ? $disabledOptions : $this->getFilterOptions($filter);
		
		$select  = Mage::app()->getLayout()->createBlock('core/html_select')
		->setName('filter_' . $filter->Id)
		->setId('filter_' . $filter->Id)
		->setClass('validate-select')
		->setExtraParams($extraParams)
		->setValue('')
		->setOptions($options);
		return $select->getHtml();
	}

	public function getAnsweredAnswerHtmlSelect($searchPathAnsweredAnswer) {
		$options = array();
		$options[] = array(
				'label' => $searchPathAnsweredAnswer->Text,
				'value' => '',
				'params'  => array("disabled"=>"disabled")
		);
		
		$select  = Mage::app()->getLayout()->createBlock('core/html_select')
		->setName('answer_' . $searchPathAnsweredAnswer->Id)
		->setId('answer_' . $searchPathAnsweredAnswer->Id)
		->setClass('validate-select')
		->setExtraParams('disabled="disabled"')
		->setValue('')
		->setOptions($options);
		
		return $select->getHtml();
	}
	
	public function getSearchPathAnsweredAnswer($_filter){
		$searchPathEntries = $this->getSearchPathEntries();
		$searchPathEntry = $searchPathEntries[$_filter->Id];
		return $searchPathEntry->Answers->Items[0];
	}
	
	public function getClearAllFiltersUrl(){
	
		$answeredAnswers = Mage::helper('salesperson')->getAnsweredAnswers();
		
		$answersIds = array_keys($answeredAnswers);
		
		$answersIds = join(',', $answersIds);
		$params['_current']     = true;
		$params['_use_rewrite'] = false;
		$params['_escape']      = true;
		$params['_query']       = array(
				'salespersonaction'	=> 'removeAllAnswers',
				'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
				'answerIds' => $answersIds,
		);
		
		$url = Mage::getUrl('*/*/change', $params);
		
		$page = (int)$this->getQwiserSearchResults()->SearchInformation->CurrentPage+1;
		$url = preg_replace("/p=*\d/",'p='.$page, $url);
		return $url;
	}
	
	public function answerQuestionsUrl(){
		if(!$this->getQwiserSearchResults()) return "";
		$url =  parent::answerQuestionsUrl() .   '&trigger=gf';
		return $url;
	}
	
	public function answerQuestionUrl($answerId){
		return parent::answerQuestionUrl($answerId) . "&trigger=gf";
	}	

	public function isGiftfinderStandardTemplate(){
		return Mage::helper('salesperson')->isGiftfinderStandardTemplate();
	}
}