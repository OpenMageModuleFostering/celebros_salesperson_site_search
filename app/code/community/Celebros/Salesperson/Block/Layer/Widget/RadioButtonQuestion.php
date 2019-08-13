<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Celebros - Pavel Feldman (email: MagentoSupport@celebros.com)
 *
 */


class Celebros_Salesperson_Block_Layer_Widget_RadioButtonQuestion extends Mage_Customer_Block_Widget_Abstract
{
	protected $_question;
	
    /**
     * Initialize block
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('salesperson/layer/widget/radiobuttonquestion.phtml');
        return $this;
    }
    
    public function setQuestion($question){
    	$this->_question = $question;
    	return $this;
    }
    
    public function getQuestion(){
    	return $this->_question;
    }
    
    public function isAnsweredAnswer($answer){
    	$answeredAnswers = Mage::helper('salesperson')->getAnsweredAnswers();
    	return isset($answeredAnswers[$answer->Id]);
    }
}
