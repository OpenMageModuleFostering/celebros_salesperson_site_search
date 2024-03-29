<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_ResultController extends Mage_Core_Controller_Front_Action
{
	const CATALOG_CATEGORY_ATTRIBUTE_ENTITY_TYPE = '9';
	const CATALOG_PRODUCT_ATTRIBUTE_ENTITY_TYPE = '10';
	
	/**
	 * Retrieve salesperson session
	 *
	 * @return Mage_Catalog_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('salesperson/session');
	}

	public function indexAction()
	{
		$bUseAjax = Mage::helper('salesperson')->useAjaxOnSearchPage();
		Mage::helper('salesperson')->setAjaxMode($bUseAjax);
		
		$query = Mage::getModel('catalogsearch/query')
		->loadByQuery(Mage::helper('salesperson')->getQueryText());
		/* @var $query Mage_CatalogSearch_Model_Query */
		if (!$query->getId()) {
			$query->setQueryText(Mage::helper('salesperson')->getQueryText());
		}

		$query->setStoreId(Mage::app()->getStore()->getId());

		$salesperson = Mage::helper('salesperson')->getSalespersonApi();
		/* @var $query Celebros_Salesperson_Model_QwiserSearchApi */

		$salesperson->setStoreId(Mage::app()->getStore()->getId());

		$bNumericsort = 1;
		/*
		 * if the sort by attribute is not numeric value then change the $bNumericsort to false
		 */
		$defaultSortBy = Mage::helper('salesperson')->getDefaultSortBy();

		$sortBy = ($defaultSortBy == "relevancy") ? "" : $defaultSortBy;
		
		if($sortBy == 'price') {
			$bNumericsort = 1;
		}
		else {
			$sortableAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(self::CATALOG_PRODUCT_ATTRIBUTE_ENTITY_TYPE,$sortBy);
		
			if ($sortableAttribute->getBackendType() != 'int'){
				$bNumericsort = 0;
			}
		}		
		
		if (Mage::helper('salesperson')->getQueryText()) {
		
			$salespersonSearch = $salesperson->SearchAdvance(
			Mage::helper('salesperson')->getQueryText(), //Query
			Mage::Helper('salesperson')->getStoreSearchProfile(),//SearchProfile
	        '',//AnswerId
	        '',//EffectOnSearchPath
	        'price',//PriceColumn
			Mage::Helper('salesperson')->getDefaultPageSize(),//PageSize
			$sortBy,//Sortingfield
			$bNumericsort,
			true
			);
			
			//Save the query string to the suggestion database
			if (Mage::helper('salesperson')->isMinQueryLength()) {
				$query->setId(0)
				->setIsActive(1)
				->setIsProcessed(1);
			}
			else {
				if ($query->getId()) {
					$query->setPopularity($query->getPopularity()+1);
				}
				else {
					$query->setPopularity(1);
				}

                /*if ($query->getRedirect()) {
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                } else {*/
                    if ($salespersonSearch->results) {
                        Mage::helper('salesperson')->prepare($query, $salespersonSearch->results->GetRelevantProductsCount());
                    }
               /* }*/
			}

			if (!Mage::helper('salesperson')->isMinQueryLength()) {
				$query->save();
			}
			//End saving query to the suggestion database

			if($salespersonSearch && $salespersonSearch->results){
				//Check the results for errors & concepts
				if($salespersonSearch->results->GetErrorOccurred()){
					if ($salespersonSearch->results->GetErrorMessage() != ''){
						$this->_getSession()->addError($this->__($salespersonSearch->results->GetErrorMessage()));
					}
				}
				 
				//$state = Mage::getSingleton('salesperson/layer')->getState();
				//$state->setInitialQuestions();
				
				if($salespersonSearch->results->QueryConcepts->Count > 0){
					foreach ($queryConcepts = $salespersonSearch->results->QueryConcepts->Items as $queryConcept){
						foreach ($queryConcept->DynamicProperties as $name => $value){
							$query_str = Mage::helper('salesperson')->getQueryText();
							switch($name){
								case "alternative products":
									$msg = str_replace('{{query}}', $query_str, Mage::getStoreConfig('salesperson/display_settings/alt_message'));
									$msg = str_replace('{{new_query}}', $value, $msg);
									if($salespersonSearch->results->GetRelevantProductsCount() == 1 && Mage::Helper('salesperson')->goToProductOnOneResult()){
										if(Mage::getStoreConfig('salesperson/display_settings/alt_message') != '') Mage::getSingleton('catalog/session')->addNotice($msg);
									}
									else {
										 if(Mage::getStoreConfig('salesperson/display_settings/alt_message') != '') $this->_getSession()->addNotice($msg);
									}
									break;
								case "banner image":
									$bannerImg = $value;
									break;
								case "banner flash":
									$bannerFlash = $value;
									break;									
								case "custom message":
									//$customMessage = $value;
									$this->_getSession()->addNotice($value);
									break;
								case "redirection url":
									$this->getResponse()->setRedirect($value);
									break;
							}
						}
					}
				}
				
				//Check if there is only one result & if the store config is set to redirect
				if($salespersonSearch->results->GetRelevantProductsCount() == 1){
					if(Mage::Helper('salesperson')->goToProductOnOneResult()){
						$this->getResponse()->setRedirect($salespersonSearch->results->Products->Items[0]->Field[Mage::Helper('salesperson/mapping')->getMapping('link')]);
					}
				}
				
				//Retrieve the recommended message from the search results
				Mage::helper('salesperson')->getRecommendedMessages();
								
				//If banner image exists for this search add it to the layout
				if (isset($bannerImg)){
					Mage::Helper('salesperson')->setBannerImage($bannerImg);
				}
				
				//If banner flash exists for this search add it to the layout
				if (isset($bannerFlash)){
					Mage::Helper('salesperson')->setBannerFlash($bannerFlash);
				}
				
				//If custom message exists for this search add it to the layout
				/*if (isset($customMessage)){
					Mage::Helper('salesperson')->setCustomMessage($customMessage);
				}*/
				//Set the result layout according to the store config settings
				
				$this->_getSession()->setSearchHandle($salespersonSearch->results->GetSearchHandle());
				// Save the ssid in the current session for anlx in the product page
				$this->_getSession()->setSearchSessionId($salespersonSearch->results->SearchInformation->SessionId);
				
				$this->_initLayoutMessages('salesperson/session');
				$this->_initLayoutMessages('checkout/session');
				
				//Load the results layout
				$this->loadLayout();
				
				$this->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('salesperson/display_settings/layout'));
				
				if($this->getRequest()->getParam("renderAjax"))
				{
					$this->renderAjaxBlocks();
				}
				else {
					$this->renderLayout();
				}
			}
			else {
				$this->_redirectReferer();
			}
		} // if (Mage::helper('salesperson')->getQueryText()) {
		else {
			//Redirect the user to homepage
			$this->_redirectReferer();
		}
	}
	
	/**
	 *
	 *
	 */
	protected function renderAjaxBlocks()
	{
		$arrBlocks = array();
	
		$arrBlocks["salesperson_result"] = $this->getLayout()
		->getBlock('salesperson.result')
		->toHtml();
	
		//**************** Non lead questions *********************
		$blockName = '';
		if (Mage::getStoreConfig('salesperson/display_settings/display_non_lead') == 'top')
			$blockName = 'salesperson.nonleadquestions.top';
		elseif (Mage::getStoreConfig('salesperson/display_settings/display_non_lead') == 'left')
			$blockName = 'salesperson.nonleadquestions.left';
		elseif (Mage::getStoreConfig('salesperson/display_settings/display_non_lead') == 'right')
			$blockName = 'salesperson.nonleadquestions.right';
		
		$arrBlocks["salesperson_nonleadquestions"] = $this->getLayout()
		->getBlock($blockName)
		->toHtml();
		//*********************************************************

		$arrBlocks["salesperson_leadquestion_top"] = $this->getLayout()
		->getBlock('salesperson.leadquestion.top')
		->toHtml();
		
		$arrBlocks["breadcrumbs"] = $this->getLayout()
		->getBlock('breadcrumbs')
		->toHtml();		

		
	
		$this->getResponse()
		->setBody(json_encode($arrBlocks));
	}	

	/**
	 * Every change to the results is made by this action
	 * 
	 */
	public function changeAction(){
		$bUseAjax = Mage::helper('salesperson')->useAjaxOnSearchPage();
		Mage::helper('salesperson')->setAjaxMode($bUseAjax);
		
		if($this->getRequest()->getParam("salespersonaction") && $this->getRequest()->getParam("searchHandle")||$this->getRequest()->getParam("mode") && $this->getRequest()->getParam("searchHandle")){
			$salesperson = Mage::helper('salesperson')->getSalespersonApi();
			/* @var $query Celebros_Salesperson_Model_QwiserSearchApi */

			$salesperson->setStoreId(Mage::app()->getStore()->getId());
			//Retrieve the action type & search handle to perform on the search results
			$action = $this->getRequest()->getParam("salespersonaction");
			$mode=$this->getRequest()->getParam("mode");
			$searchHandle = $this->getRequest()->getParam("searchHandle");
			$salespersonSearch = false;
		if($action)
			{
			switch($action)
			{
				case "limit":
					$size = $this->getRequest()->getParam("size");
					if($mode)
					{
						$perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
						$perPageValues = (string)Mage::getStoreConfig($perPageConfigKey);
						$perPageValues = explode(',', $perPageValues);
						$perPageValues = array_combine($perPageValues, $perPageValues);
						if (Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
							$perPageValues=$perPageValues + array('all'=>$this->__('All'));
						}
						if(!in_array($size,$perPageValues))
						{
							$size=Mage::Helper('salesperson')->getDefaultPageSize();
						}
					}
					if($size == 'all') $size = Mage::Helper('salesperson')->getAllPageSize();
					$salespersonSearch = $salesperson->ChangePageSize($searchHandle, $size);
					break;
				case "sort":
					$order_tmp=$this->getRequest()->getParam("order");
					$newOrder = substr($order_tmp,strpos($order_tmp,'~')+1);

					$direction = $this->getRequest()->getParam("dir");
					$direction = ($direction == 'asc') ? 1 : 0;
					switch ($newOrder){
						case 'price':
							$salespersonSearch = $salesperson->SortByPrice($searchHandle, $direction);
							break;
						case 'relevancy':
							$salespersonSearch = $salesperson->SortByRelevancy($searchHandle, $direction);
							break;
						case 'name':
							$newOrder = 'title';
						default:
							$bNumericsort = 1;
							/*
							 * if the sort by attribute is not numeric value then change the $bNumericsort to false
							 */
							if ($sortableAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(self::CATALOG_CATEGORY_ATTRIBUTE_ENTITY_TYPE,$newOrder) == null);
							$sortableAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(self::CATALOG_PRODUCT_ATTRIBUTE_ENTITY_TYPE,$newOrder);
							if ($sortableAttribute->getBackendType() != 'int'){
								$bNumericsort = 0;
							}
							$salespersonSearch = $salesperson->SortByField($searchHandle, $newOrder, $bNumericsort,$direction);
							break;
					}

					break;
				case "page":
					$page = (int)$this->getRequest()->getParam("p") - 1;
					if ($page < 0) $page = 0;
					$salespersonSearch = $salesperson->MoveToPage($searchHandle, $page);
					break;
				case "answerQuestion":
					$answerId = $this->getRequest()->getParam("answerId");
					$salespersonSearch = $salesperson->AnswerQuestion($searchHandle, $answerId, 1);
					//If price question was answered, remove all other price answers
					if(substr($answerId, 0, 2) == '_P'){
						$searchPaths = $salespersonSearch->results->SearchPath->Items;
						foreach($searchPaths as $searchPath){
							if($searchPath->QuestionId == "PriceQuestion" && $searchPath->Answers->Items[0]->Id!=$answerId)
							{
								$searchHandle = $salespersonSearch->results->GetSearchHandle();
								$salespersonSearch = $salesperson->RemoveAnswer($searchHandle, $searchPath->Answers->Items[0]->Id);
							}
						}
					}
					break;
				case "answerQuestions":
					$answerIds = "";
					foreach($this->getRequest()->getParams() as $name=>$value) {
						if(substr($name,0,7) !="filter_" || empty($value)) continue;
						$answerId = $value;
						$answerIds = empty($answerIds) ? $answerId : $answerId . urlencode('#') . $answerIds;
					}
					$salespersonSearch = $salesperson->AnswerQuestions($searchHandle, $answerIds, 1);
					break;					
				case "removeAnswer":
					$answerId = $this->getRequest()->getParam("answerId");
					$salespersonSearch = $salesperson->RemoveAnswer($searchHandle, $answerId);
					Mage::getSingleton('salesperson/layer')
					->getState()->removeFilter($answerId);
					break;
				case "removeAllAnswers":
					$answerIds = $this->getRequest()->getParam("answerIds");
					if(!strpos($answerIds,',')){ //one answer
						$salespersonSearch = $salesperson->RemoveAnswer($searchHandle, $answerIds);
					}
					else {
						$answerIds = explode(',', $answerIds);
						if (is_array($answerIds)){
							foreach ($answerIds as $answerId){
								Mage::getSingleton('salesperson/layer')
								->getState()->removeFilter($answerId);
							}
							$answerIds = join('%23', $answerIds);
							$salespersonSearch = $salesperson->RemoveAnswers($searchHandle, $answerIds);
						}
						else  {
							$salespersonSearch = $salesperson->RemoveAnswer($searchHandle, $answerIds);
							Mage::getSingleton('salesperson/layer')
							->getState()->removeFilter($answerIds);
						}
					}
					break;
				case "forceQuestion":
					$questionId = $this->getRequest()->getParam('questionId');
					$salespersonSearch = $salesperson->ForceQuestionAsFirst($searchHandle, $questionId);
					break;
						
			}
			}
			else
			{
				$pageSize = Mage::Helper('salesperson')->getDefaultPageSize();
				if ($this->getRequest()->getParam("mode") == "list")
				{
					$pageSize = Mage::getStoreConfig('catalog/frontend/list_per_page');
				}
				if ($this->getRequest()->getParam("mode") == "grid")
				{
					$pageSize = Mage::getStoreConfig('catalog/frontend/list_per_page');
				}
				$salespersonSearch = $salesperson->ChangePageSize($searchHandle,$pageSize);
			}
			if($salespersonSearch){

				//Check the results for errors
				if($salespersonSearch->results->GetErrorOccurred()){
					if ($salespersonSearch->results->GetErrorMessage() != ''){
						$this->_getSession()->addError($this->__($salespersonSearch->results->GetErrorMessage()));
					}
				}
				//Check the results for search path and update the layer state
				if(count($salespersonSearch->results->SearchPath->Items) > 0){
					$state = Mage::getSingleton('salesperson/layer')->getState();
					foreach($salespersonSearch->results->SearchPath->Items as $searchPath){
						$state->addFilter(array(
							'stage'=> $salespersonSearch->results->SearchInformation->Stage, 
							'questionId'  => $searchPath->QuestionId, 
							'answers' => $searchPath->Answers)
						);
					}
				}
				
				//Check if there is only one result & if the store config is set to redirect
				if($salespersonSearch->results->GetRelevantProductsCount() == 1){
					if(Mage::Helper('salesperson')->goToProductOnOneResult()){
						$url = $salespersonSearch->results->Products->Items[0]->Field[Mage::Helper('salesperson/mapping')->getMapping('link')];
						if($this->getRequest()->getParam("renderAjax"))
						{
							$ajaxResponseArr = array('redirectionUrl'=>$url);
							$this->getResponse()
							->setBody(json_encode($ajaxResponseArr));
							return;
						}
						else {
							$this->getResponse()->setRedirect($url);
						}
					}
				}
				
				//Retrieve the recommended message from the search results
				Mage::helper('salesperson')->getRecommendedMessages();
				
				//Load the results layout
				$this->loadLayout();
				
				//Set the result layout according to the store config settings
				
				$this->_getSession()->setSearchHandle($salespersonSearch->results->GetSearchHandle());
				// Save the ssid in the current session for anlx in the product page
				$this->_getSession()->setSearchSessionId($salespersonSearch->results->SearchInformation->SessionId);
				
				$this->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('salesperson/display_settings/layout'));
				$this->_initLayoutMessages('salesperson/session');
				$this->_initLayoutMessages('checkout/session');
				if($this->getRequest()->getParam("renderAjax"))
				{
					$this->renderAjaxBlocks();
				}
				else {
					$this->renderLayout();
				}
			}
				
		} // if($this->getRequest()->getParam("salespersonaction") && $this->getRequest()->getParam("searchHandle")){
		else {
			//Redirect the user to homepage
			$this->_redirectReferer();
		}
	}
}
