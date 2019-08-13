<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Block_Review_Helper extends Mage_Review_Block_Helper
{
    protected $_availableTemplates = array(
        'default' => 'review/helper/summary.phtml',
        'short'   => 'review/helper/summary_short.phtml'
    );

    public function getSummaryHtml($product, $templateType, $displayIfNoReviews)
    {
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = 'default';
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

		$this->setProduct($product);

        return $this->toHtml();
    }

    public function getRatingSummary()
    {
        return $this->getProduct()->Field[$this->helper('salesperson/mapping')->getMapping('rating_summary')];
    }

    public function getReviewsCount()
    {
        return key_exists(
        					$this->helper('salesperson/mapping')->getMapping('reviews_count')
        					,$this->getProduct()->Field
        				) 
        		&& (int)$this->getProduct()->Field[$this->helper('salesperson/mapping')->getMapping('reviews_count')] > 0;
    }

    public function getReviewsUrl()
    {
        return Mage::getUrl('review/product/list', array(
           'id'        => $this->getProduct()->Field[$this->helper('salesperson/mapping')->getMapping('id')],
        ));
    }
}
