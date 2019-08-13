<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Block_Product_List extends Celebros_Salesperson_Block_Product_Abstract
{
	/**
	 * Default toolbar block name
	 *
	 * @var string
	 */
	protected $_defaultToolbarBlock = 'salesperson/product_list_toolbar';

	/**
	 * Product Collection
	 *
	 * @var Celebros_Salesperson_Model_Api_QwiserProducts->Items
	 */
	protected $_productCollection;

	public function getQwiserSearchResults(){
		if(Mage::helper('salesperson')->getSalespersonApi()->results)
		return Mage::helper('salesperson')->getSalespersonApi()->results;
	}
	
	public function getStoreId(){
		return Mage::Helper('core')->getStoreId(); 
	}

	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
				$this->_productCollection = $this->getQwiserSearchResults()->Products->Items;
		}
		return $this->_productCollection;
	}

	/**
	 * Retrieve search result count
	 *
	 * @return string
	 */
	public function getResultCount()
	{
		return $this->getQwiserSearchResults()->GetRelevantProductsCount();
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Celebros_Salesperson_Model_Api_QwiserProducts
	 */
	public function getLoadedProductCollection()
	{
		return $this->_getProductCollection();
	}

	/**
	 * Retrieve current view mode
	 *
	 * @return string
	 */
	public function getMode()
	{
		return $this->getChild('toolbar')->getCurrentMode();
	}

	/**
	 * Need use as _prepareLayout - but problem in declaring collection from
	 * another block (was problem with search result)
	 */
	protected function _beforeToHtml()
	{
		$toolbar = $this->getToolbarBlock();

		// called prepare sortable parameters
		$collection = $this->_getProductCollection();

		// use sortable parameters
		if ($orders = Mage::helper('salesperson')->getAvailableOrders()) {
			$toolbar->setAvailableOrders($orders);
		}
		
		if ($sort = Mage::helper('salesperson')->getDefaultSortBy()) {
			$toolbar->setDefaultOrder($sort);
		}
		
		if ($direction = Mage::helper('salesperson')->getSortDirection($sort)) {
			$toolbar->setDefaultDirection($direction);
		}
	
		if ($modes = $this->getModes()) {
			$toolbar->setModes($modes);
		}

		$this->setChild('toolbar', $toolbar);
		
		return parent::_beforeToHtml();
	}

	/**
	 * Retrieve Toolbar block
	 *
	 * @return Celebros_Salesperson_Block_Product_List_Toolbar
	 */
	public function getToolbarBlock()
	{
		if ($blockName = $this->getToolbarBlockName()) {
			if ($block = $this->getLayout()->getBlock($blockName)) {
				return $block;
			}
		}
		$block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, microtime());
		return $block;
	}

	/**
	 * Retrieve list toolbar HTML
	 *
	 * @return string
	 */
	public function getToolbarHtml()
	{
		return $this->getChildHtml('toolbar');
	}

	public function getPriceBlockTemplate()
	{
		return $this->_getData('price_block_template');
	}

	
}
