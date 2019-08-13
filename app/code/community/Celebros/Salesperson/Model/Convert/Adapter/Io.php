<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Convert_Adapter_Io extends Mage_Dataflow_Model_Convert_Adapter_Abstract
{
    /**
     * @return Varien_Io_Abstract
     */
    public function getResource($forWrite = false)
    {
        if (!$this->_resource) {
        	$type = Mage::getStoreConfig('salesperson/export_settings/type');
        	if ($type == ''){
            	$type = $this->getVar('type', 'file');
        	}        	
            $className = 'Varien_Io_'.ucwords($type);
            $this->_resource = new $className();

            $isError = false;

            $ioConfig = $this->getVars();
         	if (Mage::getStoreConfig('salesperson/export_settings/type')!= ''){
            	$ioConfig['type'] = Mage::getStoreConfig('salesperson/export_settings/type');
            }
            if (Mage::getStoreConfig('salesperson/export_settings/ftp_host')!= ''){
            	$ioConfig['host'] = Mage::getStoreConfig('salesperson/export_settings/ftp_host');
            }
        	if (Mage::getStoreConfig('salesperson/export_settings/ftp_port')!= ''){
            	$ioConfig['port'] = Mage::getStoreConfig('salesperson/export_settings/ftp_port');
            }
        	if (Mage::getStoreConfig('salesperson/export_settings/ftp_user')!= ''){
            	$ioConfig['user'] = Mage::getStoreConfig('salesperson/export_settings/ftp_user');
            }
        	if (Mage::getStoreConfig('salesperson/export_settings/ftp_password')!= ''){
            	$ioConfig['password'] = Mage::getStoreConfig('salesperson/export_settings/ftp_password');
            }
        	if (Mage::getStoreConfig('salesperson/export_settings/passive')!= ''){
            	$ioConfig['passive'] = Mage::getStoreConfig('salesperson/export_settings/passive');
            }
            switch ($type) {
                case 'file':
                	$t_path = Mage::getStoreConfig('salesperson/export_settings/path');
                    if (preg_match('#^'.preg_quote(DS, '#').'#', $t_path) ||
                        preg_match('#^[a-z]:'.preg_quote(DS, '#') .'#i', $t_path)) {

                        $path = $this->_resource->getCleanPath($t_path);
                    }
                    else {
                        $baseDir = Mage::getBaseDir();
                        $path = $this->_resource->getCleanPath($baseDir . DS . trim($t_path, DS));
                    }
                    $this->_resource->checkAndCreateFolder($path);

                    $realPath = realpath($path);

                    if (!$isError && $realPath === false) {
                        $message = Mage::helper('dataflow')->__('Destination folder "%s" does not exist or not access to create', Mage::getStoreConfig('salesperson/export_settings/path'));
                        Mage::throwException($message);
                    }
                    elseif (!$isError && !is_dir($realPath)) {
                        $message = Mage::helper('dataflow')->__('Destination folder "%s" is not a directory', $realPath);
                        Mage::throwException($message);
                    }
                    elseif (!$isError) {
                        if ($forWrite && !is_writeable($realPath)) {
                            $message = Mage::helper('dataflow')->__('Destination folder "%s" is not a writeable', $realPath);
                            Mage::throwException($message);
                        }
                        else {
                            $ioConfig['path'] = rtrim($realPath, DS);
                        }
                    }
                    break;
                default:
                    $ioConfig['path'] = rtrim(Mage::getStoreConfig('salesperson/export_settings/path'), '/');
                    break;
            }

            if ($isError) {
                return false;
            }
            try {
                $this->_resource->open($ioConfig);
            } catch (Exception $e) {
                $message = Mage::helper('dataflow')->__('Error occured during file opening: "%s"', $e->getMessage());
                Mage::throwException($message);
            }
        }
        return $this->_resource;
    }
    
	/**
     * Load data
     *
     * @return Mage_Dataflow_Model_Convert_Adapter_Io
     */
    public function load()
    {
        if (!$this->getResource()) {
            return $this;
        }

        $batchModel = Mage::getSingleton('dataflow/batch');
        $destFile = $batchModel->getIoAdapter()->getFile(true);

        $result = $this->getResource()->read($this->getVar('filename'), $destFile);
        $filename = $this->getResource()->pwd() . '/' . $this->getVar('filename');
        if (false === $result) {
            $message = Mage::helper('dataflow')->__('Could not load file: "%s"', $filename);
            Mage::throwException($message);
        } else {
            $message = Mage::helper('dataflow')->__('Loaded successfully: "%s"', $filename);
            $this->addException($message);
        }

        $this->setData($result);
        return $this;
    }

    /**
     * Save result to destionation file from temporary
     *
     * @return Mage_Dataflow_Model_Convert_Adapter_Io
     */
    public function save()
    {
        if (!$this->getResource(true)) {
            return $this;
        }

        $batchModel = Mage::getSingleton('dataflow/batch');

        $dataFile = $batchModel->getIoAdapter()->getFile(true);
        
        $filename = $this->getVar('filename', 'products.txt');

        $result   = $this->getResource()->write($filename, $dataFile, 0777);

        if (false === $result) {
            $message = Mage::helper('dataflow')->__('Could not save file: %s', $filename);
            Mage::throwException($message);
        } else {
            $message = Mage::helper('dataflow')->__('Saved successfully: "%s" [%d byte(s)]', $filename, $batchModel->getIoAdapter()->getFileSize());
            if ($this->getVar('link')) {
                $message .= Mage::helper('dataflow')->__('<a href="%s" target="_blank">Link</a>', $this->getVar('link'));
            }
            $this->addException($message);
        }
        return $this;
    }
}
