<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_Convert_Mapper_Column extends Mage_Dataflow_Model_Convert_Mapper_Abstract
{
	/**
	 * Dataflow batch model
	 *
	 * @var Mage_Dataflow_Model_Batch
	 */
	protected $_batch;

	/**
	 * Dataflow batch export model
	 *
	 * @var Mage_Dataflow_Model_Batch_Export
	 */
	protected $_batchExport;

	/**
	 * Dataflow batch import model
	 *
	 * @var Mage_Dataflow_Model_Batch_Import
	 */
	protected $_batchImport;

	/**
	 * Retrieve Batch model singleton
	 *
	 * @return Mage_Dataflow_Model_Batch
	 */
	public function getBatchModel()
	{
		if (is_null($this->_batch)) {
			$this->_batch = Mage::getSingleton('dataflow/batch');
		}
		return $this->_batch;
	}

	/**
	 * Retrieve Batch export model
	 *
	 * @return Mage_Dataflow_Model_Batch_Export
	 */
	public function getBatchExportModel()
	{
		if (is_null($this->_batchExport)) {
			$object = Mage::getModel('dataflow/batch_export');
			$this->_batchExport = Varien_Object_Cache::singleton()->save($object);
		}
		return Varien_Object_Cache::singleton()->load($this->_batchExport);
	}

	/**
	 * Retrieve Batch import model
	 *
	 * @return Mage_Dataflow_Model_Import_Export
	 */
	public function getBatchImportModel()
	{
		if (is_null($this->_batchImport)) {
			$object = Mage::getModel('dataflow/batch_import');
			$this->_batchImport = Varien_Object_Cache::singleton()->save($object);
		}
		return Varien_Object_Cache::singleton()->load($this->_batchImport);
	}

	public function map()
	{
		$batchModel  = $this->getBatchModel();
		$batchExport = $this->getBatchExportModel();

		$batchExportIds = $batchExport
		->setBatchId($this->getBatchModel()->getId())
		->getIdCollection();

		$onlySpecified = (bool)$this->getVar('_only_specified') === true;

		if (!$onlySpecified) {
			foreach ($batchExportIds as $batchExportId) {
				$batchExport->load($batchExportId);
				$batchModel->parseFieldList($batchExport->getBatchData());
			}

			return $this;
		}

		if ($this->getVar('map') && is_array($this->getVar('map'))) {
			$attributesToSelect = $this->getVar('map');
		}
		else {
			$attributesToSelect = array();
		}

		if (!$attributesToSelect) {
			$this->getBatchExportModel()
			->setBatchId($this->getBatchModel()->getId())
			->deleteCollection();

			Mage::throwException(Mage::helper('dataflow')->__('Error field mapping! Fields list for mapping is not defined'));
		}

		foreach ($batchExportIds as $batchExportId) {
			$batchExport = $this->getBatchExportModel()->load($batchExportId);
			$row = $batchExport->getBatchData();

			$newRow = array();
			foreach ($attributesToSelect as $field => $mapField) {
				if (isset($row[$field])){
					if (preg_match('/{{is_filterable}}{{is_searchable}}/', $row[$field])){
						$row[$field] = preg_replace('/{{is_filterable}}{{is_searchable}}/', '', $row[$field], 1);
					}
					elseif (preg_match('/{{is_filterable}}/', $row[$field])){
						$row[$field] = preg_replace('/{{is_filterable}}/', '', $row[$field], 1);
					}
					elseif (preg_match('/{{is_searchable}}/', $row[$field])){
						$row[$field] = preg_replace('/{{is_searchable}}/', '', $row[$field], 1);
					}
					$newRow[$mapField] = $row[$field];
				}
				else {
					$newRow[$mapField] = null;
				}
			}

			/* Add any filterable / searchable attribute to the map */
			foreach($row as $field => $val){
				$attr = array();
				if (preg_match('/{{is_filterable}}{{is_searchable}}/', $val)){
					$val = preg_replace('/{{is_filterable}}{{is_searchable}}/', '', $val, 1);
					$attr['field'] = $field;
					$attr['val'] = $val;
//					print ($field ." => ". $val)."<br/>";
				}
				elseif (preg_match('/{{is_filterable}}/', $val)){
					$val = preg_replace('/{{is_filterable}}/', '', $val, 1);
					$attr['field'] = $field;
					$attr['val'] = $val;
//					print ($field ." => ". $val)."<br/>";
				}
				elseif (preg_match('/{{is_searchable}}/', $val)){
					$val = preg_replace('/{{is_searchable}}/', '', $val, 1);
					$attr['field'] = $field;
					$attr['val'] = $val;
//					print ($field ." => ". $val)."<br/>";
				}
				if (!empty($attr)){
//					Set the new field with the c:<field>:<<type>> structure
//					if (!key_exists('c:'.$attr['field'].':string', $newRow) && !key_exists($attr['field'], $newRow)) {
//						$newRow['c:'.$attr['field'].':string'] = $attr['val'];
//					}
//					Set Clean fields names structure
					if (!key_exists($attr['field'], $newRow)){
						$newRow[$attr['field']] = $attr['val'];
					}
				}
			}
			
			

			$batchExport->setBatchData($newRow)
			->setStatus(1)
			->save();
			$this->getBatchModel()->parseFieldList($batchExport->getBatchData());
		}

		return $this;
	}
}
