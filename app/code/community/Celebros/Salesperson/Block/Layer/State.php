<?php

/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */

class Celebros_Salesperson_Block_Layer_State extends Mage_Core_Block_Template
{
	protected function getQwiserSearchResults(){
    	if(Mage::helper('salesperson')->getSalespersonApi()->results)
    		return Mage::helper('salesperson')->getSalespersonApi()->results;
    }
    /**
     * Initialize Layer State template
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('salesperson/layer/state.phtml');
    }

    /**
     * Retrieve active filters
     *
     * @return array
     */
    public function getActiveFilters()
    {
        $filters = $this->getLayer()->getState()->getFilters();
        if (!is_array($filters)) {
            $filters = array();
        }
        return $filters;
    }

    public function isMultiSelect() {
    	return Mage::getStoreConfigFlag('salesperson/display_settings/enable_non_lead_answers_multiselect');
    }    
    
    /**
     * Retrieve Layer object
     *
     * @return Celebros_Salesperson_Model_Layer
     */
    public function getLayer()
    {
        if (!$this->hasData('layer')) {
            $this->setLayer(Mage::getSingleton('salesperson/layer'));
        }
        return $this->_getData('layer');
    }
    
	public function getStateRemoveUrl($answerId){
    	$params['_current']     = true;
        $params['_use_rewrite'] = false;
        $params['_escape']      = true;
        $params['_query']       = array(
        	'salespersonaction'	=> 'removeAnswer',
        	'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
        	'answerId' => $answerId,
        );
        $url =  Mage::getUrl('*/*/change', $params);
        $page = (int)$this->getQwiserSearchResults()->SearchInformation->CurrentPage+1;
        $url = preg_replace("/p=*\d/",'p='.$page, $url);
        return $url;
    }
    public function getClearAllFiltersUrl(){
    	$filters = $this->getLayer()->getState()->getFilters();
    	$answersIds = array();
    	if(!empty($filters)){
    		foreach($filters as $filter){
    			$answersIds[] = $filter['answers']->Items[0]->Id;
    		}
    	}
    	$answersIds = join(',', $answersIds);
    	$params['_current']     = true;
        $params['_use_rewrite'] = false;
        $params['_escape']      = true;
        $params['_query']       = array(
        	'salespersonaction'	=> 'removeAllAnswers',
        	'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
        	'answerIds' => $answersIds,
        );
        $url =  Mage::getUrl('*/*/change', $params);
        $page = (int)$this->getQwiserSearchResults()->SearchInformation->CurrentPage+1;
        $url = preg_replace("/p=*\d/",'p='.$page, $url);
        return $url;
    }
    
	public function getRemoveAnswersFromBredcrumbsUrl($answersIds){
		$answersIds = join(',', $answersIds);
    	$params['_current']     = true;
        $params['_use_rewrite'] = false;
        $params['_escape']      = true;
        $params['_query']       = array(
        	'salespersonaction'	=> 'removeAllAnswers',
        	'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
        	'answerIds' => $answersIds,
        );
        $url =  Mage::getUrl('*/*/change', $params);
        $page = (int)$this->getQwiserSearchResults()->SearchInformation->CurrentPage+1;
        $url = preg_replace("/p=*\d/",'p='.$page, $url);
        $url = preg_replace("/renderAjax=*\d/",'', $url);
        return $url;
    }
}
