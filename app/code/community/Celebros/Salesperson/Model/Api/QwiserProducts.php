<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 */
class Celebros_Salesperson_Model_Api_QwiserProducts
{
	var $Count = 0;	//the number of products.
	var $Items;	//indexer .
	
	Function Celebros_Salesperson_Model_Api_QwiserProducts($xml_Products)
	{
		if(is_object($xml_Products))
		{
			$xml_productsNodes = $xml_Products->child_nodes();
			$xml_productsNodes = getDomElements($xml_productsNodes);
			$this->Count = count($xml_productsNodes);

			for ($i = 0 ; $i <= $this->Count - 1;$i++)
			{
				$ProdNode = $xml_productsNodes[$i];
				
				$this->Items[$i] = Mage::getModel('salesperson/Product', $ProdNode);
				
			}
		}
	}


}
?>