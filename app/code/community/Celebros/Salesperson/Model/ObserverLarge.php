<?php
ini_set('memory_limit','1535M');
set_time_limit(7200);
ini_set('max_execution_time',7200);
ini_set('display_errors', 1);
ini_set('output_buffering', 0);



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
//include_once("createZip.php");
class Celebros_Salesperson_Model_ObserverLarge extends Celebros_Salesperson_Model_Observer
{
	protected static $_profilingResults;
	protected $bExportProductLink = true;
	protected $_product_entity_type_id = null;
	protected $_category_entity_type_id = null;
	protected $prod_file_name="product_file";
	protected $isLogProfiler=true;

	function __construct() {

		$this->_fStore_id = 1;
		$this->export_config($this->_fStore_id);
		$this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');
		$this->_product_entity_type_id = $this->get_product_entity_type_id();
		$this->_category_entity_type_id = $this->get_category_entity_type_id();
	}
	
	private function logProfiler($msg, $isSpaceLine=false)
	{
		if (!($this->isLogProfiler))
		  return;
		  
		Mage::log(date("Y-m-d, H:i:s:: ").$msg, null, 'celebros_profiler',true);
		
		if ($isSpaceLine)
			Mage::log('', null, 'celebros_profiler',true);
	}
	
	public function export_celebros() {
		//self::startProfiling(__FUNCTION__);
		
		$this->logProfiler('===============');
		$this->logProfiler('Starting Export');
		$this->logProfiler('===============',true);
		
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);

		
		echo "<BR>".date('Y/m/d H:i:s');
		echo "<BR>Starting export<BR>";
		echo "===============<BR>";
		echo "Memory usage: ".memory_get_usage(true)."<BR>";

		echo "<BR>Exporting tables<BR>";
		echo "================<BR>";
		echo "Memory usage: ".memory_get_usage(true)."<BR>";
		echo str_repeat(" ", 4096);
		flush();

		$this->logProfiler('Exporting tables');
		$this->logProfiler('----------------',true);
		
		$this->export_tables();
		
		echo "<BR>Exporting products<BR>";
		echo "==================<BR>";
		echo "Memory usage: ".memory_get_usage(true)."<BR>";
		echo str_repeat(" ", 4096);
		flush();

		$this->logProfiler('Writing products file');
		$this->logProfiler('---------------------',true);
		
		$this->export_products();

		echo "<BR>Creating ZIP file<BR>";
		echo "=================<BR>";
		echo "Memory usage: ".memory_get_usage(true)."<BR>";
		echo str_repeat(" ", 4096);
		flush();

		$this->logProfiler('Creating ZIP file');
		$this->logProfiler('-----------------',true);
		
		$zipFilePath = $this->zipLargeFiles();
		$this->ftpfile($zipFilePath);

		echo "<BR>Finished<BR>";
		echo "========<BR>";
		echo "Memory usage: ".memory_get_usage(true)."<BR>";
		echo "Memory peek usage: ".memory_get_peak_usage(true)."<BR>";

		echo "<BR><BR>".date('Y/m/d H:i:s');
		echo str_repeat(" ", 4096);
		flush();

		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		$this->logProfiler('Mem peek usage: '.memory_get_peak_usage(true),true);				
		//self::stopProfiling(__FUNCTION__);
		
		//$html = self::getProfilingResultsString();
		//$this->log_profiling_results($html);
		//echo $html;
	}
	
	protected function log_profiling_results($html) {
		$fh = $this->create_file("profiling_results.log", "html");
		$this->write_to_file($html, $fh);
	}
	
	protected function get_status_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_product_entity_type_id} AND attribute_code='status'";
		return $this->_read->fetchOne($sql);
	}
		
	protected function get_product_entity_type_id() {
		$table = $this->getTableName("eav_entity_type");
		$sql = "SELECT entity_type_id
		FROM {$table}
		WHERE entity_type_code='catalog_product'";
		return $this->_read->fetchOne($sql);
	}	
	
	protected function get_category_entity_type_id() {
		$table = $this->getTableName("eav_entity_type");
		$sql = "SELECT entity_type_id
		FROM {$table}
		WHERE entity_type_code='catalog_category'";
		return $this->_read->fetchOne($sql);
	}	
	
	protected function get_visibility_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_product_entity_type_id} AND attribute_code='visibility'";
		return $this->_read->fetchOne($sql);
	}
	
	protected function get_category_name_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_category_entity_type_id} AND attribute_code='name'";
		return $this->_read->fetchOne($sql);
	}

	protected function get_category_is_active_attribute_id() {
		$table = $this->getTableName("eav_attribute");
		$sql = "SELECT attribute_id
		FROM {$table}
		WHERE entity_type_id ={$this->_category_entity_type_id} AND attribute_code='is_active'";
		return $this->_read->fetchOne($sql);
	}
	
	protected function export_tables() {
		//self::startProfiling(__FUNCTION__);
		
		$table = $this->getTableName("eav_attribute");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT attribute_id, attribute_code, backend_type, frontend_input
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}";
		$this->export_table($sql, "attributes_lookup");
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);


		$table = $this->getTableName("catalog_product_entity");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, type_id, sku
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		

		$table = $this->getTableName("catalog_product_entity_int");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$status_attribute_id = $this->get_status_attribute_id();
		$sql = "SELECT DISTINCT entity_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0
				AND attribute_id = {$status_attribute_id}
				AND value = 2";
		$this->export_table($sql, "disabled_products");
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);

		
		$table = $this->getTableName("catalog_product_entity_int");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$visibility_attribute_id = $this->get_visibility_attribute_id();
		$sql = "SELECT DISTINCT entity_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0
				AND attribute_id = $visibility_attribute_id
				AND value = 1";
		$this->export_table($sql, "not_visible_individually_products");		
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		

		$table = $this->getTableName("catalog_product_entity_varchar");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		
		
		$table = $this->getTableName("catalog_product_entity_int");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		

		$table = $this->getTableName("catalog_product_entity_text");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);


		$table = $this->getTableName("catalog_product_entity_decimal");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);

		
		$table = $this->getTableName("catalog_product_entity_datetime");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, value, attribute_id
				FROM {$table}
				WHERE entity_type_id ={$this->_product_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);


		$table = $this->getTableName("eav_attribute_option_value");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT option_id, value
				FROM {$table}
				WHERE store_id = 0";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		
		
		$table = $this->getTableName("eav_attribute_option");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT option_id, attribute_id
				FROM {$table}";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		

		$table = $this->getTableName("catalog_category_product");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT category_id, product_id
				FROM {$table}";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
	
		
		$table = $this->getTableName("catalog_category_entity");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT entity_id, parent_id, path
				FROM {$table}";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);

		
		$table = $this->getTableName("catalog_category_entity_varchar");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$name_attribute_id = $this->get_category_name_attribute_id();
		$sql = "SELECT entity_id, value
				FROM {$table}
				WHERE attribute_id = {$name_attribute_id}
				AND store_id =0";
		$this->export_table($sql, "category_lookup");
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);
		
		
		$table = $this->getTableName("catalog_category_entity_int");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$is_active_attribute_id = $this->get_category_is_active_attribute_id();
		$sql = "SELECT entity_id
				FROM {$table}
				WHERE `attribute_id` = {$is_active_attribute_id}
				AND value = 0
				AND entity_type_id ={$this->_category_entity_type_id}
				AND store_id =0";
		$this->export_table($sql, "disabled_categories");
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);


		$table = $this->getTableName("catalog_product_super_link");		
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT product_id, parent_id
				FROM {$table}";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);

		
		$table = $this->getTableName("catalog_product_super_attribute");
		$this->logProfiler("START {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true));
		$sql = "SELECT product_id, attribute_id
				FROM {$table}";
		$this->export_table($sql, $table);
		$this->logProfiler("FINISH {$table}");
		$this->logProfiler('Mem usage: '.memory_get_usage(true),true);		

		//self::stopProfiling(__FUNCTION__);
	}
	
	protected function export_table($sql, $filename) {
		$fh = $this->create_file($filename);
		stream_set_write_buffer($fh,4096);
		
		$header = "";
		$str = "";
		$i = 0;

		$stm = $this->_read->query($sql . " LIMIT 0, 100000000");
		
		$hasData=($row = $stm->fetch());
		
		if ($hasData)
		{
			$header = "^" . implode("^	^",array_keys($row)) . "^" . "\r\n";
			$this->write_to_file($header, $fh);
		}

		$rowCount=0;
		while($hasData)
		{
			do
			{
				$str.= "^" . implode("^	^",$row) . "^" . "\r\n";
				$rowCount++;
				
				if (($rowCount%1000)==0)
				{
					$this->logProfiler("Write block start");
					$this->write_to_file($str , $fh);
					$this->logProfiler("Write block end");
					$str="";
				}		
				
				$hasData=($row=$stm->fetch());
			} while($hasData);
		}
		
		if (($rowCount%1000)!=0)
		{
			$this->logProfiler("Write last block start");
			$this->write_to_file($str , $fh);
			$this->logProfiler("Write last block end");
		}
		
		$this->logProfiler("Total rows: {$rowCount}");
		
		fclose($fh);
		//self::stopProfiling(__FUNCTION__. "({$filename})");
	}
	
	protected function create_file($name, $ext = "txt") {
		//self::startProfiling(__FUNCTION__);
		if (!is_dir($this->_fPath)) $dir=@mkdir($this->_fPath,0777,true);
		$filePath = $this->_fPath . DIRECTORY_SEPARATOR . $name . "." . $ext;
		if (file_exists($filePath)) unlink($filePath);
		$fh = fopen($filePath, 'ab');
		//self::stopProfiling(__FUNCTION__);
		return $fh;
	}
	
	protected function write_to_file($str, $fh){
		//self::startProfiling(__FUNCTION__);
		fwrite($fh, $str);

		//self::stopProfiling(__FUNCTION__);
	}
	
	public function zipLargeFiles() {
		//self::startProfiling(__FUNCTION__);
		
		$out = false;
		$zipPath = $this->_fPath . DIRECTORY_SEPARATOR . "products_file.zip";//$this->_fileNameZip;
		
		$dh=opendir($this->_fPath);
		$filesToZip = array(); 
		while(($item=readdir($dh)) !== false && !is_null($item)){
			$filePath = $this->_fPath . DIRECTORY_SEPARATOR . $item;
			$ext = pathinfo($filePath, PATHINFO_EXTENSION);
			if(is_file($filePath) && ($ext == "txt" || $ext == "log")) {
				$filesToZip[] = $filePath;
			}
		}
		
		for($i=0; $i < count($filesToZip); $i++) {
			$filePath = $filesToZip[$i];
			$out = $this->zipLargeFile($filePath, $zipPath);
		}

		//self::stopProfiling(__FUNCTION__);
		return $out ? $zipPath : false;
	}
	
	public function zipLargeFile($filePath, $zipPath)
	{
		//self::startProfiling(__FUNCTION__);
		
		$out = false;
	
		$zip = new ZipArchive();
		if ($zip->open($zipPath, ZipArchive::CREATE) == true) {
			$fileName = basename($filePath);
			$out = $zip->addFile($filePath, basename($filePath));
			if(!$out) throw new  Exception("Could not add file '{$fileName}' to_zip_file"); 
			$zip->close();
			$ext = pathinfo($fileName, PATHINFO_EXTENSION);
			if($ext != "log") unlink($filePath);
		}
		else
		{
			throw new  Exception("Could not create zip file");
		}
		
		//self::stopProfiling(__FUNCTION__);
		return $out;
	}


		protected function export_products()
		{
			echo "Begining products export<BR>Memory usage: ".memory_get_usage(true)."<BR>";
		
			$fh = $this->create_file($this->prod_file_name);
			stream_set_write_buffer($fh,4096);
		
			$fields = array("id", "price", "image_link", "thumbnail", "type_id", "product_sku");		
			$attributes = array("price", "image", "thumbnail", "type");
		
			if($this->bExportProductLink) $fields[] = "link";
			if($this->bExportProductLink) $attributes[] = "url_key";
		
			if(count($fields)- 2 != count($attributes)) throw new Exception('Fields (without id):' . implode("^	^",$fields) . ' are not equal to ' . implode("^	^",$fields));
		
			$header = "^" . implode("^	^",$fields) . "^" . "\r\n";
			$this->write_to_file($header, $fh);
		
			// *********************************
		
			$table = $this->getTableName("catalog_product_entity");
			$sql = "SELECT entity_id, type_id, sku
					FROM {$table}
					WHERE entity_type_id ={$this->_product_entity_type_id}";
		
			$stm = $this->_read->query($sql . " LIMIT 0, 100000000");
		
			$productNum=0;
			$rowCount=0;
			$str='';
			
			$product=Mage::getSingleton('catalog/product');
			
			$hasData=($row=$stm->fetch());
			
			while($hasData)
			{
				do
				{
					$product->load(($row["entity_id"]));
					
					if ($product->isSaleable())
					{
						$values["id"] = $product->getentity_id();
						$values["price"] = $this->getCalculatedPrice($product);
						$values["image_link"] = $product->getMediaConfig()->getMediaUrl($product->getData("image"));
						$values["thumbnail"] = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(66);
						$values["type_id"] = $product->gettype_id();
						$values["product_sku"] = $product->getSku();
	
						if($this->bExportProductLink)
						{
							$values["link"] = $product->getProductUrl();
						}

						$str.= "^" . implode("^	^",$values) . "^" . "\r\n";
						
						$productNum++;

						if (($productNum%1000)==0)
						{
							$this->logProfiler("Product num: {$productNum}");
							$this->logProfiler('Mem usage: '.memory_get_usage(true));
							$this->logProfiler("Write block start");
							$this->write_to_file($str , $fh);
							$this->logProfiler("Write block end",true);
							$str='';
						}		
					}
					
					//$product->cleanCache();
					$product->clearInstance();
					$product->reset();
					
					$rowCount++;
										
/*					if (($rowCount%1000)==0)
					{
						$this->logProfiler("Number of processed rows: {$rowCount}");
						$this->logProfiler("Fetch start");
						$hasData=($row=$stm->fetch());
						$this->logProfiler("Fetch end", true);
					}
					else
*/
					$hasData=($row=$stm->fetch());
				} while($hasData);

				if (($productNum%1000)!=0)
				{
					$this->logProfiler("Product num: {$productNum}");
					$this->logProfiler("Write last block start");
					$this->write_to_file($str , $fh);
					$this->logProfiler("Write last block end",true);
				}

			}
			
			$this->logProfiler("Finished outer while",true);
		
			fclose($fh);
	}
	
	
	protected static function startProfiling($key) {
		
		if(!isset(self::$_profilingResults[$key])) {
			$profile = new stdClass();
			$profile->average =0 ;
			$profile->count = 0;
			$profile->max = 0;
			self::$_profilingResults[$key] = $profile;
		}
		
		$profile = self::$_profilingResults[$key];
		if(isset($profile->start) && $profile->start > $profile->end) throw new Exception("The start of profiling timer '{$key}' is called before the stop of it was called");
		
		$profile->start = (float) array_sum(explode(' ',microtime()));
	}
	
	protected static function stopProfiling($key) {
		if(!isset(self::$_profilingResults[$key])) throw new Exception("The stop of profiling timer '{$key}' was called while the start was never declared");
	
		$profile = self::$_profilingResults[$key];
		if($profile->start == -1) throw new Exception("The start time of '{$key}' profiling is -1");
		
		$profile->end = (float) array_sum(explode(' ',microtime()));
		$duration = $profile->end - $profile->start;
		if($profile->max < $duration) $profile->max = $duration;
		
		$profile->average = ($profile->average * $profile->count + $duration)/($profile->count +1);
		$profile->count++;
	}
	
	protected static function getProfilingResultsString() {
		$html = "";
		if(count(self::$_profilingResults)) {
			$html.= "In sec:";
			$html.=  '<table border="1">';
			$html.=  "<tr><th>Timer</th><th>Total</th><th>Average</th><th>Count</th><th>Peak</th></tr>";
			foreach(self::$_profilingResults as $key =>$profile) {
				$total = $profile->average * $profile->count;
				$html.=  "<tr><td>{$key}</td><td>{$total}</td><td>{$profile->average}</td><td>{$profile->count}</td><td>{$profile->max}</td></tr>";
			}
			$html.=  "</table>";
		}
		
		$html.= 'PHP Memory peak usage: ' . memory_get_peak_usage();
		
		return $html;
	}

}