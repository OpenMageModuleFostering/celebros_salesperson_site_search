<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

	$installer->run("
    	CREATE TABLE IF NOT EXISTS `{$this->getTable('celebrosfieldsmapping')}` (
      	`id` int(11) NOT NULL auto_increment,
      	`xml_field` VARCHAR(255) NULL, 
      	`code_field` text,
      	PRIMARY KEY  (`id`),
		UNIQUE `XML_FIELD` ( `xml_field` )
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	
    	INSERT INTO `{$this->getTable('celebrosfieldsmapping')}`  (id, xml_field, code_field)
    		VALUES 
    		(null,'title','title'),
    		(null, 'link', 'link'),
    		(null, 'status','status'),
    		(null, 'image_link','image_link'),
    		(null, 'thumbnail_label','thumbnail_label'),
    		(null, 'rating','rating'),
    		(null, 'short_description','short_description'),
    		(null, 'id', 'id'),
    		(null, 'visible', 'visible'),
    		(null, 'store_id', 'store_id'),
    		(null, 'is_in_stock', 'is_in_stock'),
    		(null, 'product_sku', 'sku'),
    		(null, 'category', 'category'),
    		(null, 'websites', 'websites'),
    		(null, 'news_from_date', 'news_from_date'),
    		(null, 'news_to_date', 'news_to_date') ON DUPLICATE KEY UPDATE code_field=code_field;
	");

$installer->endSetup();