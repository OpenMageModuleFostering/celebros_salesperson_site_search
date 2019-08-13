<?php
/**
 * Celebros Qwiser - Magento Extension
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality. 
 * If you wish to customize it, please contact Celebros.
 *
 * @category    Celebros
 * @package     Celebros_Salesperson
 * @author		Omniscience Co. - Dan Aharon-Shalom (email: dan@omniscience.co.il)
 *
 */

class Celebros_Salesperson_Model_SalespersonCrossSellApi extends Mage_Core_Model_Abstract
{
	
	protected $_serverAddress;
	
	protected $_siteKey;
	
	protected $_requestHandle;
	
	/**
	 * Init resource model
	 *
	 */
	protected function _construct()
	{
		$this->_init('salesperson/SalespersonCrossSellApi');
		if (Mage::getStoreConfig('salesperson/crosssell_settings/crosssell_customer_name') != '' && Mage::getStoreConfig('salesperson/crosssell_settings/crosssell_address') != '' && Mage::getStoreConfig('salesperson/crosssell_settings/crosssell_request_handle') != ''){
			$this->_serverAddress = Mage::getStoreConfig('salesperson/crosssell_settings/crosssell_address');
			if (preg_match('/http:\/\//',$this->_serverAddress)){
				$this->_serverAddress = preg_replace('/http::\/\//','', $this->_serverAddress);
			}
			$this->_siteKey = Mage::getStoreConfig('salesperson/crosssell_settings/crosssell_customer_name');
			$this->_requestHandle = Mage::getStoreConfig('salesperson/crosssell_settings/crosssell_request_handle');
		}
	}

	public function getRecommendationsIds($id) {
		$arrIds = array();
		
		$url = "http://{$this->_serverAddress}/JsonEndPoint/ProductsRecommendation.aspx?siteKey={$this->_siteKey}&RequestHandle={$this->_requestHandle}&RequestType=1&SKU={$id}&Encoding=utf-8";
		
		$jsonData =  $this->_get_data($url);

		$obj = json_decode($jsonData);

		for($i=0; isset($obj->Items) && $i < count($obj->Items); $i++) {
			$arrIds[] = (int) $obj->Items[$i]->Fields->SKU;
		}

		return $arrIds; 
	}
	
	protected function _get_data($url){
		//var_dump($url);
		$data = null;
		try {
			$ch = curl_init();
			$timeout = 5;
			curl_setopt($ch,CURLOPT_URL,$url);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
			$data = curl_exec($ch);
			$curlError = curl_error($ch);
			curl_close($ch);
			if(!empty($curlError)) {
				Mage::throwException('get_data: ' . $curlError .' Request Url: ' . $url);
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
		}
		
		return $data;
	}
}