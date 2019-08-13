<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
 
//echo "started"; exit(); 
 
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run(
	"INSERT INTO `{$this->getTable('dataflow_profile')}` (`profile_id`, `name`, `created_at`, `updated_at`, `actions_xml`, `gui_data`, `direction`, `entity_type`, `store_id`, `data_transfer`) VALUES"
	." (null, 'Salesperson Exporter', '2010-03-03 10:49:35', '2010-03-08 17:54:19',
	 '<action type=\"catalog/convert_adapter_product\" method=\"load\">
	 	<var name=\"store\"><![CDATA[0]]></var>
	 </action>

	<action type=\"salesperson/convert_parser_product\" method=\"unparse\">
	    <var name=\"store\"><![CDATA[0]]></var>
	    <var name=\"url_field\"><![CDATA[0]]></var>
	</action>

	<action type=\"salesperson/convert_mapper_column\" method=\"map\">
	    <var name=\"map\">
	        <map name=\"store_id\"><![CDATA[store_id]]></map>
	        <map name=\"websites\"><![CDATA[websites]]></map>
	        <map name=\"id\"><![CDATA[id]]></map>
	        <map name=\"name\"><![CDATA[title]]></map>
	        <map name=\"price\"><![CDATA[price]]></map>
	        <map name=\"rating\"><![CDATA[rating]]></map>
	        <map name=\"url_path\"><![CDATA[link]]></map>
	        <map name=\"thumbnail\"><![CDATA[image_link]]></map>
	        <map name=\"category\"><![CDATA[category]]></map>
	        <map name=\"type\"><![CDATA[type]]></map>
	        <map name=\"weight\"><![CDATA[weight]]></map>
	        <map name=\"manufacturer\"><![CDATA[brand]]></map>
	        <map name=\"color\"><![CDATA[color]]></map>
	        <map name=\"thumbnail_label\"><![CDATA[thumbnail_label]]></map>
	        <map name=\"description\"><![CDATA[description]]></map>
	        <map name=\"short_description\"><![CDATA[short_description]]></map>
	        <map name=\"is_in_stock\"><![CDATA[is_in_stock]]></map>
	        <map name=\"news_from_date\"><![CDATA[news_from_date]]></map>
	        <map name=\"news_to_date\"><![CDATA[news_to_date]]></map>
	        <map name=\"sku\"><![CDATA[product_sku]]></map>
	        <map name=\"status\"><![CDATA[status]]></map>
	    </var>
	    <var name=\"_only_specified\">true</var>
	</action>
	
	<action type=\"salesperson/convert_adapter_io\" method=\"save\">
	    <var name=\"type\">file</var>
	    <var name=\"path\">var/export</var>
	    <var name=\"filename\"><![CDATA[products.txt]]></var>
	</action>',
	 '', NULL, '', 0, NULL);"
	 );
	 

	 
	 
	 $installer->endSetup();