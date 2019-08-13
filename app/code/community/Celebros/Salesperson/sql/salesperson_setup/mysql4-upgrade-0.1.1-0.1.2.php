<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
$installer = Mage::getResourceModel('catalog/setup', 'default_setup');
/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */

$installer->startSetup();

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);
$attributeCode = 'salesperson_search_phrase';

if(!$installer->getAttribute('catalog_category', $attributeCode)) {
	$installer->addAttribute('catalog_category', $attributeCode,  array(
	    'type'     => 'varchar',
	    'label'    => 'Salesperson search phrase',
	    'input'    => 'text',
	    'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
	    'visible'           => true,
	    'required'          => false,
	    'user_defined'      => false,
	    'default'           => ''
	));
	 
	$installer->addAttributeToGroup(
	    $entityTypeId,
	    $attributeSetId,
	    $attributeGroupId,
	    $attributeCode,
	    '10000'                    //last Magento's attribute position in General tab is 10
	);
	
}

$installer->endSetup();