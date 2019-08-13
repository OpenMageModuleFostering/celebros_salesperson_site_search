<?php

class Celebros_Salesperson_Adminhtml_MappingController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }
    
    public function postAction()
    {
        $post = $this->getRequest()->getPost();
        
        try {
            if (empty($post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            /* here's your form processing */
            
            $mappingModel = Mage::getSingleton("salesperson/mapping");
            
            if (!key_exists('mapping',$post)) {
                Mage::throwException($this->__('Invalid form data.'));
            }
            
            foreach($post['mapping'] as $key => $value){
            	$mappingModel->load($key);
            	$mappingModel->setXmlField($value);
            	$mappingModel->save();
            }
            
            $message = $this->__('The mapping has been saved successfully.');
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*');
    }
}
