<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_GiftfinderController extends Mage_Core_Controller_Front_Action
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

	protected function getDefaultSortBy()
	{
		return Mage::helper('salesperson')->getDefaultSortBy();
	}
	
	public function indexAction()
	{
		$bUseAjax = Mage::helper('salesperson')->isGiftfinderDynamicAjaxTemplate();
		Mage::helper('salesperson')->setAjaxMode($bUseAjax);
		
		$this->loadLayout();
		
		$this->getLayout()->getBlock('root')->setTemplate(Mage::getStoreConfig('salesperson/display_settings/layout'));
		
		$this->getLayout()->getBlock('product_list_toolbar')->setData('_current_grid_order', $this->getDefaultSortBy());
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
	
	/**
	 * 
	 *
	 */
	protected function renderAjaxBlocks()
	{
		$arrBlocks = array();
		
		$arrBlocks["salesperson_giftfinder_view"] = $this->getLayout()
		->getBlock('salesperson.giftfinder.view')
		->toHtml();
		
	
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
	
	public function changeAction()
	{
		$bUseAjax = Mage::helper('salesperson')->isGiftfinderDynamicAjaxTemplate();
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
				if(!$mode)
					{
						$size = $this->getRequest()->getParam("size");
						$salespersonSearch = $salesperson->ChangePageSize($searchHandle, $size);
						break;
					}
				else
				{
                                $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
                                $perPageValues = (string)Mage::getStoreConfig($perPageConfigKey);
                                $perPageValues = explode(',', $perPageValues);
                                $perPageValues = array_combine($perPageValues, $perPageValues);
                                if (Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
                                    $perPageValues=$perPageValues + array('all'=>$this->__('All'));
                                }
                                    $size = $this->getRequest()->getParam("size");
					if(!in_array($size,$perPageValues))
					{
					$size=Mage::Helper('salesperson')->getDefaultPageSize();

					}
                                       $salespersonSearch = $salesperson->ChangePageSize($searchHandle, $size);
						break;
				}
				case "sort":
					$newOrder = $this->getRequest()->getParam("order");
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
