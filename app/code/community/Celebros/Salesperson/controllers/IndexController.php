<?php
class Celebros_Salesperson_IndexController extends Mage_Core_Controller_Front_Action
{
	public function testModelAction() {
		$mappings = Mage::getModel('salesperson/mapping')->getCollection();
    	foreach($mappings as $mapping){
        	echo '<h3>'.$mapping->getXmlField().'</h3>';
        	echo nl2br($mapping->getCodeField());
    	}    
    }
}