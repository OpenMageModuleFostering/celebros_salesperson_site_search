<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Block_Layer_View extends Mage_Core_Block_Template
{
	protected $_radioButtonsQuestions = array();

	protected function getQwiserSearchResults(){
    	if(Mage::helper('salesperson')->getSalespersonApi()->results)
    		return Mage::helper('salesperson')->getSalespersonApi()->results;
    }
    /**

    /**
     * Prepare child blocks
     *
     * @return Celebros_Salesperson_Block_Layer_View
     */
    protected function _prepareLayout()
    {
        $stateBlock = $this->getLayout()->createBlock('salesperson/layer_state')
            ->setLayer($this->getLayer());
        $this->setChild('layer_state', $stateBlock);
        
        $this->_setRadioButtonsQuestions();
            
        return parent::_prepareLayout();
    }
    
    protected function _setRadioButtonsQuestions(){
    	$arrSideText = explode(',',Mage::getStoreConfig('salesperson/display_settings/radiobuttons_questions'));
    	for($i = 0; $i < count($arrSideText) ; $i++) {
    		$sideText = $arrSideText[$i];
    		$this->_radioButtonsQuestions[$sideText] = $sideText;
    	}
    }

    /**
     * Get layer object
     *
     * @return Celebros_Salesperson_Model_Layer
     */
    public function getLayer()
    {
        return Mage::getSingleton('salesperson/layer');
    }

    /**
     * Get layered navigation state html
     *
     * @return string
     */
    public function getStateHtml()
    {
        return $this->getChildHtml('layer_state');
    }
    
    /**
     * Get all layer filters
     *
     * @return array
     */
    public function getFilters()
    {
    	$filters = Mage::registry('layer_filters');
    	
    	if(!isset($filters)){
    		$results = $this->getQwiserSearchResults();
    		if(!isset($results)) return array();
    		$filters = $results->Questions->GetAllQuestions();
    		//Updates the price question if requuires
    		//e.g. when price slider is set and this is not a first search stage
    		$filters = $this->handlePriceQuestion($filters, $results->SearchInformation->Stage);
    		
	        Mage::register('layer_filters', $filters);
    	}
        return $filters;
    }
    
    public function handlePriceQuestion($filters, $searchStage){
    	$priceQuestion = null;
    	
    	//Adds price slider question to the session
    	if( $this->isPriceSlider() && 
    		$searchStage == "1" &&
    		isset($filters["PriceQuestion"])
    	) Mage::getSingleton('salesperson/session')->setPriceQuestion($filters["PriceQuestion"]);
    	
    	//Gets price slider question from the session
    	if( $this->isPriceSlider() && $searchStage != "1"){ 
    		$priceQuestion = Mage::getSingleton('salesperson/session')->getPriceQuestion();
    		//Remove price question if it was not answered yet
    		if(isset($priceQuestion) && !$this->isAnsweredQuestion($priceQuestion)) $priceQuestion = null;
    	}
    	
    	if(isset($priceQuestion)) $filters["PriceQuestion"] = $priceQuestion;
    	
    	if(isset($filters["PriceQuestion"])) $this->setPriceQuestion($filters["PriceQuestion"]);
    	
    	return $filters;
    }
    
    public function setPriceQuestion($priceQuestion){
    	Mage::register('PriceQuestion', $priceQuestion);
    }
    
    public function getPriceQuestion(){
    	return Mage::registry('PriceQuestion');
    }

    public function getAnsweredPriceRange($priceQuestion){
    	$answeredPriceRange = array();
	    $results = $this->getQwiserSearchResults();
	    if(isset($results) && isset($results->SearchPath->Items)) {
	    	foreach($results->SearchPath->Items as $searchPathEntry) {
	    		if($searchPathEntry->QuestionId == $priceQuestion->Id) {
	    			$answerId =  $searchPathEntry->Answers->Items[0]->Id;
	    			$tmp = preg_replace( '/_P/', "" ,$answerId);
	    			$answeredPriceRange[0] = (int) preg_replace( '/_\d*/', "" ,$tmp);
	    			$answeredPriceRange[1] = (int) preg_replace( '/_P\d*_/', "" ,$answerId);
	    		}
	    	}
	    }
	    if(count($answeredPriceRange) == 0) {
	    	$answeredPriceRange[0] = 0;
	    	$answeredPriceRange[1] = $this->getMaxPrice($priceQuestion);
    	}
    	return $answeredPriceRange;
    }
    
    public function isMultiSelect() {
    	return Mage::getStoreConfigFlag('salesperson/display_settings/enable_non_lead_answers_multiselect');
    }
    
    public function isHierarchical($filter){
    	$isHierarchical = true;
    	if(isset($filter->DynamicProperties["IsHierarchical"]) && $filter->DynamicProperties["IsHierarchical"]=="False" ) $isHierarchical = false; 
    	return $isHierarchical;
    }
    
    public function isPriceQuestion($filter){
    	return ($filter->Type == "Price");
    }
    
    public function isRadioButtonsQuestion($filter){
    	return isset($this->_radioButtonsQuestions[$filter->SideText]);
    }
    
	public function getFilterHtmlRadioButtons($filter){
		
		$html = "";
		
		if((int)$filter->Answers->Count > 0){
			foreach ($filter->Answers->Items as $answer){
				$checked = $this->isAnsweredAnswer($answer) ? 'checked="checked"' : '';
				$answerUrl =  $this->isAnsweredAnswer($answer) ? '' : $this->answerQuestionUrl($answer->Id);
				$html .= "<input type=\"radio\" onclick=\"celebrosSetLocation('{$answerUrl}')\" name=\"filter_{$filter->Id}\" value=\"{$answer->Id}\" {$checked}> {$answer->Text}";
			}
			$html .="<input type=\"radio\" name=\"filter_{$filter->Id}\" value=\"\"> All </input>";
		}
		
		return $html;
	}
    
    public function isPriceSlider(){
    	return (Mage::getStoreConfig('salesperson/display_settings/price_selector') == "slider");
    }
    
    public function getMinPrice($priceQuestion){
    	$answerId = $priceQuestion->Answers->Items[0]->Id;
    	$tmp = preg_replace( '/_P/', "" ,$answerId);
    	$min = (int) preg_replace( '/_\d*/', "" ,$tmp);
    	return $min;
    } 

    public function getMaxPrice($priceQuestion){
    	$answerId = end($priceQuestion->Answers->Items)->Id;
    	$max = (int) preg_replace( '/_P\d*_/', "" ,$answerId);
        return $max;
    }

    public function getStateRemoveUrl($answerId) {
    	$stateBlock = $this->getLayout()->createBlock('salesperson/layer_state');
    	return $stateBlock->getStateRemoveUrl($answerId);
    } 
    
    public function answerQuestionUrl($answerId){
    	$urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = false;
        $urlParams['_query']    = array(
        	'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
        	'salespersonaction' => 'answerQuestion',
        	'answerId' => $answerId,
        );
        
        $url = Mage::getUrl('*/*/change', $urlParams);
        if (preg_match("/p==*\d/", $url)){
       	 $url = preg_replace("/p==*\d/",'p=1', $url);
        }
        else {
        	$url .= "&p=1";
        }
        
        $url = str_replace("&amp;", '&', $url);
        
        return $url;
    }
    
    public function answerQuestionsUrl(){
    	if(!$this->getQwiserSearchResults()) return "";
    	$urlParams = array();
    	$urlParams['_current']  = true;
    	$urlParams['_escape']   = true;
    	$urlParams['_use_rewrite']   = false;
    	$urlParams['_query']    = array(
    			'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
    			'salespersonaction' => 'answerQuestions',
    	);
    	$url =  Mage::getUrl('salesperson/result/change', $urlParams);
    	if (preg_match("/p==*\d/", $url)){
    		$url = preg_replace("/p==*\d/",'p=1', $url);
    	}
    	else {
    		$url .= "&p=1";
    	}
    	return $url;
    }    
    
	public function getFilterText($filter,$type){
		if ($type == "nonlead" && Mage::Helper('salesperson')->getNonLeadQuestionsPosition() != 'top'){
	    		return $filter->SideText;
		}
		elseif ($type == "lead"){
			return $filter->SideText;
		}
		return $filter->Text;
    }
    
    public function getLeadQuestion() {
    	$filters = $this->getFilters();
    	
    	$leadQuestion = Mage::registry('leadQuestion');
    	
    	if(!isset($leadQuestion)){
    		
    		foreach($filters as $filter)
    		{
    			if($this->isHierarchical($filter)){
    				$leadQuestion = $filter;
    				break;
    			}
    		}
    		Mage::register('leadQuestion', $leadQuestion);
    	}
    	
    	return $leadQuestion;
    	
    }
    
    public function getLeadAnswers($question){
    	$answers = array_merge($question->Answers->Items, $question->ExtraAnswers->Items);
    	$count = count($answers);
    	$max = $this->getMaxLeadAnswers();
    	if($count > $max) $answers = array_slice($answers, 0, $max, true);
    	return $answers;
    }
    
    public function getMoreLeadAnswers($question){
    	$answers = array_merge($question->Answers->Items, $question->ExtraAnswers->Items);
    	$count = count($answers);
    	$max = $this->getMaxLeadAnswers();
    	if($count > $max) $answers = array_slice($answers, $max, $count - $max, true);
    	else $answers = array();
    	return $answers;
    }
    
	public function getMaxLeadAnswers(){
    	return (int)Mage::getStoreConfig('salesperson/display_settings/max_lead_answers');
    }
    
    public function getMaxNonLeadAnswers(){
    	return (int)Mage::getStoreConfig('salesperson/display_settings/max_non_lead_answers');
    }
    
    public function getNonLeadQuestions(){
    	$questions = $this->getFilters();
    	$priceQuestion = isset($questions["PriceQuestion"]) ? $questions["PriceQuestion"] : null;
    	
    	if($this->canShowLeadQuestion()){ 
    		$leadQuestion = $this->getLeadQuestion();
    		if(isset($leadQuestion)) unset($questions[$leadQuestion->Id]);
    	}
    	
    	//Remove more questions
    	$count = count($questions);
    	$max = $this->getMaxNonLeadQuestions();
    	
    	if($count > $max) $questions = array_slice($questions, 0, $max, true);
    	//Replace last question with the price question if the question came back in the search results but it sliced to more questions
    	if($priceQuestion &&  !isset($questions["PriceQuestion"]))
    	{
    		$replacement = array($priceQuestion->Id=>$priceQuestion);
    		array_splice($questions, $max-1, 1, $replacement);
    	}
    	
    	return $questions;
    }
    
    public function getMoreNonLeadQuestions(){
    	$questions = $this->getFilters();
    	
    	if($this->canShowLeadQuestion()){
    		$leadQuestion = $this->getLeadQuestion();
    		if(isset($leadQuestion)) unset($questions[$leadQuestion->Id]);
    	}

    	//Remove more questions
    	$count = count($questions);
    	$max = $this->getMaxNonLeadQuestions();
    	$lastBeforeMoreQuestion = array_slice($questions, $max-1, 1, true);
    	if($count > $max) $questions = array_slice($questions, $max, $count - $max, true);
    	else $questions = array();    	
    	
    	//Add first question with removed question in getNonLeadQuestions and unset Price question
        if(isset($questions["PriceQuestion"]))
    	{
    		unset($questions["PriceQuestion"]);
    		$replacement = $lastBeforeMoreQuestion;
    		array_splice($questions, 0,0, $replacement);
    	}
    	
    	return $questions;
    }    
    
    public function getNonLeadAnswers($question){
    	$answers = array_merge($question->Answers->Items, $question->ExtraAnswers->Items);
    	$count = count($answers);
    	$max = $this->getMaxNonLeadAnswers();
    	if($count > $max) $answers = array_slice($answers, 0, $max, true);
    	return $answers;
    }    
    
    public function getMoreNonLeadAnswers($question){
    	$answers = array_merge($question->Answers->Items, $question->ExtraAnswers->Items);
    	$count = count($answers);
    	$max = $this->getMaxNonLeadAnswers();
    	if($count > $max) $answers = array_slice($answers, $max, $count - $max, true);
    	else $answers = array();
    	return $answers;
    }
       
	public function getMaxNonLeadQuestions(){
    	return (int)Mage::getStoreConfig('salesperson/display_settings/max_non_lead_questions');
    }
    
	public function showProductCountInLeadAnswers(){
    	return Mage::getStoreConfigFlag('salesperson/display_settings/show_product_count_in_lead_answers');
    }
    
    public function showProductCountInNonLeadAnswers(){
    	return Mage::getStoreConfigFlag('salesperson/display_settings/show_product_count_in_non_lead_answers');
    }
 
    /**
     * Check availability display layer block
     *
     * @return bool
     */
    public function canShowNoneLeadSideBlock()
    {
        return Mage::Helper('salesperson')->getNonLeadQuestionsPosition() == 'left' || Mage::Helper('salesperson')->getNonLeadQuestionsPosition() == 'right';
    }
    
    public function canShowLeadQuestion(){
    	return Mage::getStoreConfigFlag('salesperson/display_settings/display_lead');
    }

    public function forceLeadQuestion($questionId){
    	$urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = false;
        $urlParams['_query']    = array(
        	'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
        	'salespersonaction' => 'forceQuestion',
        	'questionId' => $questionId,
        );
        return Mage::getUrl('*/*/change', $urlParams);
    }

    public function stateHasFilters(){
    	return count($this->getLayer()->getState()->getFilters()) > 0;
    }
    
    public function getCustomPriceAnswerUrl(){
    	$urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = false;
        $urlParams['_use_rewrite']   = false;
        $urlParams['_query']    = array(
        	'searchHandle' => $this->getQwiserSearchResults()->GetSearchHandle(),
        	'salespersonaction' => 'answerQuestion',
        );
        $url =  Mage::getUrl('*/*/change', $urlParams);
        if(strpos($url, "answerId=")){
        	$replace_string = substr($url,strpos($url, "answerId="),strpos($url, '&',strpos($url, "answerId=")) - strpos($url, "answerId="));
        	$url = str_replace($replace_string, '', $url);
        }
        if (preg_match("/p=*\d/", $url)){
       	 $url = preg_replace("/p=*\d/",'p=1', $url);
        }
        else {
        	$url .= "&p=1";
        }
        return $url;
    }
    
    public function getDisplayImageInLeadQuestion(){
    	return Mage::getStoreConfigFlag('salesperson/display_settings/display_image_lead_question');
    }
    
    public function isAnsweredQuestion($question){
    	$searchPathEntries = Mage::helper('salesperson')->getSearchPathEntries();
    	return isset($searchPathEntries[$question->Id]);
    }
    
    public function isAnsweredAnswer($answer){
    	$answeredAnswers = Mage::helper('salesperson')->getAnsweredAnswers();
    	return isset($answeredAnswers[$answer->Id]);
    }
    
    public function getSearchPathEntries(){
    	return Mage::helper('salesperson')->getSearchPathEntries();
    }
    
    public function getAnswerHtml($answer, $bIsHierarchical){
    	$html = "";
    	$bMultiSelect=$this->isMultiSelect();
    	
    	if($bMultiSelect && !$bIsHierarchical){
	    	$answerUrl =  $this->isAnsweredAnswer($answer) ? $this->getStateRemoveUrl($answer->Id) : $this->answerQuestionUrl($answer->Id);
	    	$strChecked =  $this->isAnsweredAnswer($answer) ? 'checked="checked"' : "";
	    	$strDisabled = ($answer->ProductCount == 0) ? 'disabled="disabled"' : "";
	    	
	    	$html .= 
	    	"<input type=checkbox {$strChecked} {$strDisabled} 
	    	onclick=\"celebrosSetLocation('{$answerUrl}');\"
	    	name=\"{$answer->Id}\" id=\"{$answer->Id}\" />";
	    	
			$href = Mage::helper('salesperson')->getHref($answerUrl);
    		$html .= "<a {$href}>$answer->Text</a>";
    	}
		else{
			$href = Mage::helper('salesperson')->getHref($this->answerQuestionUrl($answer->Id));
    		$html .= "<a {$href}>$answer->Text</a>";
    	}   
    	if ($this->showProductCountInNonLeadAnswers()) $html .= " ({$answer->ProductCount})";
    	return $html;
    }
    
    public function getMoreAnswersHtml($moreAnswers){
    	$html = "";
    	
	    $html .= "<select id='answersList' class='cel_moreAnswers'
	    onchange='celebrosSetLocation(this.value)'>";
    	
	    foreach ($moreAnswers as $answer){
		    $html .= "<option value='{$this->answerQuestionUrl($answer->Id)}'> {$answer->Text}";
		    if ($this->showProductCountInNonLeadAnswers())$html .= " ({$answer->ProductCount})";
		    $html .= "</option>";
	    }
	    
	    $html .= "</select>";
	    return $html;
    }
    
    public function hasSearchResults(){
    	$results = $this->getQwiserSearchResults();
    	return isset($results);
    }    
    
}