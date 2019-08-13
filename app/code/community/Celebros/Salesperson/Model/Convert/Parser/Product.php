<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Convert_Parser_Product
extends Mage_Catalog_Model_Convert_Parser_Product
{
	protected $_store;
	protected $_storeId;
	
	public function getStore()
    {
        if (is_null($this->_store)) {
            try {
                $store = Mage::app()->getStore(Mage::getStoreConfig('salesperson/export_settings/store_id'));
            }
            catch (Exception $e) {
                $this->addException(Mage::helper('catalog')->__('Invalid store specified please check the configuration'), Varien_Convert_Exception::FATAL);
                throw $e;
            }
            $this->_store = $store;
        }
        return $this->_store;
    }

	public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->_storeId = $this->getStore()->getId();
        }
        return $this->_storeId;
    }
	/**
	 * Unparse (prepare data) loaded products
	 *
	 * @return Mage_Catalog_Model_Convert_Parser_Product
	 */
	public function unparse()
	{
		$entityIds = $this->getData();

		foreach ($entityIds as $i => $entityId) {

			/*$allproduct = $this->getProductModel()
			->setData(array())
			->load($entityId);*/

			$product = $this->getProductModel()
			->reset()
			->setStoreId($this->getStoreId())
			->load($entityId);
			$this->setProductTypeInstance($product);

			/* Get Product Rating and Calculate Avrage */

			$ratingResourceModel = new Celebros_Salesperson_Model_Mysql4_Qwiser();
			$ratingPercents = $ratingResourceModel->getRateingByEntityId($product->getEntityId());

			$productRating = 0;
			foreach ($ratingPercents as $rating){
				$productRating += (int)$rating['percent'];
			}
			if ($productRating != 0){
				$productRating /= count($ratingPercents);
			}

			if (str_pos($product->getAttributeText('visibility'), 'Search')!==false) {
				$this->setProductTypeInstance($product);
				/* @var $product Mage_Catalog_Model_Product */

				$position = Mage::helper('catalog')->__('Line %d, SKU: %s', ($i+1), $product->getSku());
				$this->setPosition($position);

				$row = array(
                'store'         => $this->getStore()->getCode(),
                'websites'      => '',
                'attribute_set' => $this->getAttributeSetName($product->getEntityTypeId(), $product->getAttributeSetId()),
                'type'          => $product->getTypeId(),
                'category'  => '',
				'status'	=> '',
				'rating' => $productRating,
                'id'	=>	$product->getId(),
				'price' => ''
				);

				/*product status*/
				//$productStatus = $product->getData('status');

				/*categories names */

				$categoryNames = array();
				foreach ($product->getCategoryIds() as $categoryId){
					$categoryName =  Mage::getModel('catalog/category')->load($categoryId)->getName();
					$categoryNames[$categoryName] = $categoryName;
				}
				$row['category'] = join(',', $categoryNames);

				/*websites codes*/
				if ($this->getStore()->getCode() == Mage_Core_Model_Store::ADMIN_CODE) {
					$websiteCodes = array();
					foreach ($product->getWebsiteIds() as $websiteId) {
						$websiteCode = Mage::app()->getWebsite($websiteId)->getCode();
						$websiteCodes[$websiteCode] = $websiteCode;
					}
					$row['websites'] = join(',', $websiteCodes);
				}
				else {
					$row['websites'] = $this->getStore()->getWebsite()->getCode();
					if ($this->getVar('url_field')) {
						$row['url'] = $product->getProductUrl(false);
					}
				}

				if($product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE){
					$resource = Mage::getSingleton('core/resource');
					$DB = $resource->getConnection('catalog_read');
					$prefix = Mage::getConfig()->getNode('global/resources/db/table_prefix');
					$sql = "SELECT * FROM ".$prefix."catalog_product_index_price WHERE `entity_id` ='".$product->getId()."' GROUP BY `entity_id`";
					$result = $DB->fetchAll($sql);
					$row['price'] = $result[0]['min_price'];
				}else{
					$row['price'] = $product->getFinalPrice();
				}

				foreach ($product->getData() as $field => $value) {
					if (in_array($field, $this->_systemFields) || is_object($value)) {
						continue;
					}
					$attribute = $this->getAttribute($field);
					if (!$attribute) {
						continue;
					}

					if ($attribute->usesSource()) {
						$option = $attribute->getSource()->getOptionText($value);
						if ($value && empty($option)) {
							$message = Mage::helper('catalog')->__("Invalid option id specified for %s (%s), skipping the record", $field, $value);
							$this->addException($message, Mage_Dataflow_Model_Convert_Exception::ERROR);
							continue;
						}
						if (is_array($option)) {
							$value = join(self::MULTI_DELIMITER, $option);
						} else {
							$value = $option;
						}
						unset($option);
					}
					elseif (is_array($value)) {
						continue;
					}

					$row[$field] = $value;
				}

				/* Check if there is an Indexable Attribute that is not selected and add it to the array*/

				$attributes = $product->getAttributes();
				foreach ($attributes as $attribute){
					if($attribute->getIsFilterable()){
						if (key_exists($attribute->getData('attribute_code'), $row)){
							$row[$attribute->getData('attribute_code')] = $row[$attribute->getData('attribute_code')].'{{is_filterable}}';
						}
					}
					if($attribute->getIsSearchable()){
						if (key_exists($attribute->getData('attribute_code'), $row)){
							$row[$attribute->getData('attribute_code')] = $row[$attribute->getData('attribute_code')].'{{is_searchable}}';
						}
					}
				}



				if ($stockItem = $product->getStockItem()) {
					foreach ($stockItem->getData() as $field => $value) {
						if (in_array($field, $this->_systemFields) || is_object($value)) {
							continue;
						}
						$row[$field] = $value;
					}
				}

				foreach ($this->_imageFields as $field) {
					if (isset($row[$field]) && $row[$field] == 'no_selection') {
						$row[$field] = null;
					}
				}

				/* EXPORTS TIER PRICING */
				#$_tierPrices = Mage::getModel('bundle/product_price')->getTierPrice("",$product);
				#print_r($product->getTierPrice());
				$row['tier_prices'] = "";
				#$incoming_tierps = $product->getTierPrice();
				$incoming_tierps = $product->getData('tier_price');
				#print_r($incoming_tierps);
				if(is_array($incoming_tierps)) {
					foreach($incoming_tierps as $tier_str){
						#print_r($tier_str);
						$row['tier_prices'] .= $tier_str['cust_group'] . "=" . round($tier_str['price_qty']) . "=" . $tier_str['price'] . "|";
					}
				}

				$batchExport = $this->getBatchExportModel()
				->setId(null)
				->setBatchId($this->getBatchModel()->getId())
				->setBatchData($row)
				->setStatus(1)
				->save();
			}
		}

		return $this;
	}
}