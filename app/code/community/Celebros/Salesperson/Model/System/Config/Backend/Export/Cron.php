<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */
class Celebros_Salesperson_Model_System_Config_Backend_Export_Cron extends Mage_Core_Model_Config_Data
{
	const CRON_STRING_PATH  = 'crontab/jobs/salesperson_export/schedule/cron_expr';
	const CRON_MODEL_PATH   = 'crontab/jobs/salesperson_export/run/model';

	/**
	 * Cron settings after save
	 *
	 * @return Celebros_Salesperson_Model_System_Config_Backend_Export_Cron
	 */
	protected function _afterSave()
	{
		//$enabled    = $this->getData('groups/export_settings/fields/cron_enabled/value');
		$cron_expr  = $this->getData('groups/export_settings/fields/cron_expr/value');
		try {
				Mage::getModel('core/config_data')
				->load(self::CRON_STRING_PATH, 'path')
				->setValue($cron_expr)
				->setPath(self::CRON_STRING_PATH)
				->save();

				Mage::getModel('core/config_data')
				->load(self::CRON_MODEL_PATH, 'path')
				->setValue((string) Mage::getConfig()->getNode(self::CRON_MODEL_PATH))
				->setPath(self::CRON_MODEL_PATH)
				->save();
		}
		catch (Exception $e) {
			Mage::throwException(Mage::helper('adminhtml')->__($e.'  -  Unable to save Cron expression'));
		}
		
	}
}
