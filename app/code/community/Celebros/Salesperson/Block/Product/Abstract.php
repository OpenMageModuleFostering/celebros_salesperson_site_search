<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
abstract class Celebros_Salesperson_Block_Product_Abstract extends  Mage_Catalog_Block_Product_Abstract
{
    
    /**
     * Flag which allow/disallow to use link for as low as price
     *
     * @var bool
     */
    protected $_useLinkForAsLowAs = true;

    protected $_reviewsHelperBlock;

    /**
     * Default product amount per row
     *
     * @var int
     */
    protected $_defaultColumnCount = 3;

    /**
     * Product amount per row depending on custom page layout of category
     *
     * @var array
     */
    protected $_columnCountLayoutDepend = array();
    

    
    /**
     * Retrieve url for add product to cart
     * Will return product view page URL if product has required options
     *
     * @param Celebros_Salesperson_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        return $this->helper('salesperson/checkout_cart')->getAddUrl($product, $additional);
    }

    /**
     * Retrieve url for add product to wishlist
     *
     * @param Celebros_Salesperson_Model_Product $product
     * @return string
     */
    public function getAddToWishlistUrl($product)
    {
        return $this->helper('salesperson/wishlist')->getAddUrl($product);
    }

    /**
     * Retrieve Add Product to Compare Products List URL
     *
     * @param Celebros_Salesperson_Model_Product $product
     * @return string
     */
    public function getAddToCompareUrl($product)
    {
        return $this->helper('salesperson/product_compare')->getAddUrl($product);
    }
    
    /**
     * Returns product price block html
     *
     * @param Mage_Catalog_Model_Product $product
     * @param boolean $displayMinimalPrice
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix='')
    {
    	$id = $product->Field['id'];
		if(key_exists('magento_id',$product->Field))
			$id = $product->Field['magento_id'];
			
		$realProduct = Mage::getModel('catalog/product')->load($id);
		

		if($realProduct)
		{
		
		$realProduct=
	    Mage::getModel("catalog/product")->getCollection()
        ->addAttributeToSelect(Mage::getSingleton("catalog/config")->getProductAttributes())
        ->addAttributeToFilter("entity_id", $realProduct->getId())
        ->setPage(1, 1)
        ->addMinimalPrice()
        ->addFinalPrice()
        ->addTaxPercents()
        ->load()
        ->getFirstItem();

			return parent::getPriceHtml($realProduct, $displayMinimalPrice, $idSuffix);
		}
		else return $product->Field['price'];
    }

    

    /**
     * Get product reviews summary
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $templateType
     * @param bool $displayIfNoReviews
     * @return string
     */
    public function getSalespersonReviewsSummaryHtml($product, $templateType = false, $displayIfNoReviews = false)
    {
        $this->_initReviewsHelperBlock();
        return $this->_reviewsHelperBlock->getSummaryHtml($product, $templateType, $displayIfNoReviews);
    }

    /**
     * Add/replace reviews summary template by type
     *
     * @param string $type
     * @param string $template
     */
    public function addReviewSummaryTemplate($type, $template)
    {
        $this->_initReviewsHelperBlock();
        $this->_reviewsHelperBlock->addTemplate($type, $template);
    }

    /**
     * Create reviews summary helper block once
     *
     */
    protected function _initReviewsHelperBlock()
    {
        if (!$this->_reviewsHelperBlock) {
            $this->_reviewsHelperBlock = $this->getLayout()->createBlock('salesperson/review_helper');
        }
    }

    /**
     * Retrieve currently viewed product object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->hasData('product')) {
            $this->setData('product', Mage::registry('product'));
        }
        return $this->getData('product');
    }


   
 
    /**
     * Retrieve given media attribute label or product name if no label
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $mediaAttributeCode
     *
     * @return string
     */
    public function getImageLabel($product=null, $mediaAttributeCode='image')
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }

        $label = $product->getData($mediaAttributeCode.'_label');
        if (empty($label)) {
            $label = $product->getName();
        }

        return $label;
    }
    
    public function getMapping($code_field = ""){
    	return $this->helper('salesperson/mapping')->getMapping($code_field);
    }
    
    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getSalespersonProductUrl($product)
    {
		return $product->Field[$this->getMapping('link')];
    }

    /**
     * Check Product has URL
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve product amount per row
     *
     * @return int
     */
    public function getColumnCount()
    {
        if (!$this->_getData('column_count')) {
            $pageLayout = $this->getPageLayout();
            if ($pageLayout && $this->getColumnCountLayoutDepend($pageLayout)) {
                $this->setData(
                    'column_count',
                    $this->getColumnCountLayoutDepend($pageLayout)
                );
            } else {
                $this->setData('column_count', $this->_defaultColumnCount);
            }
        }

        return (int)$this->_getData('column_count');
    }

    /**
     * Add row size depends on page layout
     *
     * @param string $pageLayout
     * @param int $rowSize
     * @return Mage_Catalog_Block_Product_List
     */
    public function addColumnCountLayoutDepend($pageLayout, $columnCount)
    {
        $this->_columnCountLayoutDepend[$pageLayout] = $columnCount;
        return $this;
    }

    /**
     * Remove row size depends on page layout
     *
     * @param string $pageLayout
     * @return Mage_Catalog_Block_Product_List
     */
    public function removeColumnCountLayoutDepend($pageLayout)
    {
        if (isset($this->_columnCountLayoutDepend[$pageLayout])) {
            unset($this->_columnCountLayoutDepend[$pageLayout]);
        }

        return $this;
    }

    /**
     * Retrieve row size depends on page layout
     *
     * @param string $pageLayout
     * @return int|boolean
     */
    public function getColumnCountLayoutDepend($pageLayout)
    {
        if (isset($this->_columnCountLayoutDepend[$pageLayout])) {
            return $this->_columnCountLayoutDepend[$pageLayout];
        }

        return false;
    }

    /**
     * Retrieve current page layout
     *
     * @return Varien_Object
     */
    public function getPageLayout()
    {
    	$pageLayoutHandles = Mage::getSingleton('page/config')->getPageLayoutHandles();
    	$pageLayoutHandleskeys = array_keys($pageLayoutHandles);
//    	return false;
    	switch(Mage::getStoreConfig('salesperson/display_settings/layout')){
    		case "salesperson/1column.phtml":
    			return $pageLayoutHandleskeys[1];
    			break;
    		case "salesperson/2columns-left.phtml":
    			return $pageLayoutHandleskeys[2];
    			break;
    		case "salesperson/2columns-right.phtml":
    			return $pageLayoutHandleskeys[3];
    			break;
    		case "salesperson/3columns.phtml":
    			return $pageLayoutHandleskeys[4];
    			break;
    			
    	}
        //return $this->helper('page/layout')->getCurrentPageLayout();
    }

}
