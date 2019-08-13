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
ini_set('display_errors', 1);
ini_set('memory_limit','1024M');
ini_set('max_execution_time',18000);
set_time_limit(18000);

//include_once("createZip.php");
class Celebros_Salesperson_Model_Observer
{
	protected $_errors = array();
	protected $_config;
	protected $_conn;
	protected $_read;
	protected $_fDel;
	protected $_fEnclose;
	protected $_fPath;
	protected $_fType;
	protected $_fStore_id;
	protected $_fStore;
	protected $_fStore_module_enabled;
	protected $_fProducts_Collection;
	protected $_fProduct_Category_Matrix;
	protected $_fSize;
	protected $_updateStock;
	protected $_flushRecordsCount = 500;
	protected $_fileNameTxt = "products.txt";
	protected $_fileNameZip = "products.zip";
	protected $_getChildrenOfGroupProducts = true;
	protected $_bUpload = true;
	protected $_aProductPricingTiers;
	protected $_bLargeExport = true;
	
	public function __construct(){
	}
	
	/**
	 * Retrieve salesperson session
	 *
	 * @return Mage_Catalog_Model_Session
	 */
	protected function _getSession()
	{
		return Mage::getSingleton('salesperson/session');
	}

	/**
	 * Daily update catalog to salesperson server by cron
	 * This method is called from cron process, cron is workink in UTC time and
	 *
	 * @param   Varien_Event_Observer $observer
	 * @return  Celebros_Salesperson_Model_Observer
	 */
	public function catalogUpdate($observer)
	{
		$enabled = Mage::getStoreConfigFlag('salesperson/export_settings/cron_enabled');
		/*$profileId = Mage::getStoreConfig('salesperson/export_settings/profile_id');*/
		if($enabled) $this->export_celebros();

		return $this;
	}

	/**
	 * Update stock after product update in the backend
	 * 
	 * @param Varien_Event_Observer $observer
	 */
	public function updateStockConfig($observer){
		if($this->_updateStock==1)
		{
		$event = $observer->getEvent();
		$product = $event->getProduct();
		$Stock =$product->getData('stock_data');
		if(isset($Stock['is_in_stock']))
			$isInStock=$Stock['is_in_stock'];
		else
			$isInStock=0;
		$sku = $product->getSku();
		if ((int)$isInStock == 0){
			Mage::helper('salesperson')->getSalespersonApi()->RemoveProductFromStock($sku);
		}
		else {
			Mage::helper('salesperson')->getSalespersonApi()->RestoreProductToStock($sku);
		}
		}
	}
	
	
	/**
	 * Update stock after order checkout process in the front-end
	 * 
	 * @param Varien_Event_Observer $observer $observer
	 */
	public function updateStockOrder($observer){
		if($this->_updateStock==1)
		{
		$event = $observer->getEvent();
		$order = $event->getOrder();
		$productModel = Mage::getSingleton('catalog/product');
		$itemModel = Mage::getSingleton('cataloginventory/stock_item');
		foreach ($order->getAllItems() as $item){
			$product_info = $item->getProductOptions();
			$product_id = $product_info['info_buyRequest']['product'];
			$product = $productModel->load($product_id);
			$inventoery = $itemModel->loadByProduct($product);
			$isInStock = $inventoery->getData('is_in_stock');
			$sku = $product->getSku();
			if ((int)$isInStock == 0){
				Mage::helper('salesperson')->getSalespersonApi()->RemoveProductFromStock($sku);
			}
			else {
				Mage::helper('salesperson')->getSalespersonApi()->RestoreProductToStock($sku);
			}
		}
		}
	}

	public function export_celebros()
	{
		
		$export_start = (float) array_sum(explode(' ',microtime()));
		$this->comments_style('header',0,0);
		$this->comments_style('icon','Starting profile execution, please wait...','start');
		$this->comments_style('warning','Warning: Please don\'t close window during importing/exporting data','warning');
		flush();
		
		$bCrossSellEnabled = Mage::getStoreConfigFlag('salesperson/crosssell_settings/crosssell_enabled');
		if($bCrossSellEnabled) $this->export_orders_celebros();

        //Connect to the database
        $this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');

        // Reindex the URL rewrite table for relevant store views (Ones where
        // nav2search is active
        $this->url_rewrite_reindex();

		if($this->_bLargeExport) {
			$model=Mage::getModel('salesperson/ObserverLarge');
			$model->export_celebros();
			return;
		}
		
		//-------------------
		//get the configuration
		   
		//---------------------
	
		unset($product_category_ids);

		$store_view_ids = $this->_read->fetchAll('SELECT store_id FROM '.$this->getTableName("core_store") . ' WHERE store_id <> 0'); //Added for multi store view purposes by Eli Sagy
		
		foreach($store_view_ids as $store_view_id)
		{
			$export_store_start = (float) array_sum(explode(' ',microtime())); 
			$this->export_config($store_view_id['store_id']);
			
			if(!$this->_fStore_export_enabled) continue;	
			
			$this->comments_style('icon','Store code: '.$this->_fStore->getCode().".",'Store');
			
			
			
			// Check if to use flat tables for products
			$cel_use_flat_tables=Mage::getStoreConfigFlag('salesperson/export_settings/flat_export_enabled');
			$mage_flat_enabled=Mage::helper('catalog/product_flat')->isEnabled();
			$use_flat_tables=($cel_use_flat_tables && $mage_flat_enabled);
			
			if ($use_flat_tables)
				$this->comments_style('info','Using flat tables.','Info');
			else
				$this->comments_style('info','Using EAV tables.','Info');
			
			
			$dir = true;
			
			if($this->_fPath!='')
			{
				if (!is_dir($this->_fPath)) $dir=@mkdir($this->_fPath,0777,true);
				if (file_exists($this->_fPath. "/" . $this->_fileNameTxt)) unlink($this->_fPath . "/" . $this->_fileNameTxt);
				$fh = fopen($this->_fPath . "/" . $this->_fileNameTxt, 'ab');	
			}

			if(!$dir) {
				$this->comments_style('error','Could not create the directory in that path','problemwith dir');
				return;
			}
			
			$nameCategory=array();
			$result = $this->getCategoryCollection($store_view_id['store_id']);
		
			//Build category name look up and leaf paths with category ids
			$categoryLookup = array();
			$leafPaths = array();
			foreach($result as $res)
			{
				$categoryId = $res->getId();
				$categoryLookup[$categoryId] = str_replace(",", "&", $res->getName());
				if(!$res->hasChildren()) $leafPaths[$categoryId] = $res->getPathIds();
			}
			
			//Build leaf paths with category names 
			foreach($leafPaths as $categoryId => $arrPath) {

				for($i=1; $i < count($arrPath); $i++){
					$ancestorId = $arrPath[$i];
					if(!isset($categoryLookup[$ancestorId])) continue;
					$categoryName = $categoryLookup[$ancestorId];
					$nameCategory[$categoryId] = isset($nameCategory[$categoryId]) ? 
												$nameCategory[$categoryId] . ">" . $categoryName  : 
												$categoryName;
				}
			}
			
			unset($result);
			
			$searchFilterAttrTable = Mage::helper('salesperson')->is_CE() && Mage::helper('salesperson')->is_1_3() ? "eav_attribute" : "catalog_eav_attribute";
			
			//------------------------------------------
			$configurProdAttVal=array();

			$subSelect1 = $this->_read->select()
             			->from($this->getTableName("catalog_product_super_link"),
							array('parent_id'))
						->where('product_id = cpei.entity_id');
		
			$subSelect2 = $this->_read->select()
             			->from($this->getTableName($searchFilterAttrTable),
							array('attribute_id'))
						->where('is_searchable = 1 OR is_filterable IN ( 1, 2 )');
			
			$subSelect3 = $this->_read->select()
						->distinct()
                        ->from(array('cpei2' => $this->getTableName("catalog_product_entity_int")),
                            array('entity_id'))
						->join(array('eet' => $this->getTableName("eav_entity_type")),
							"eet.entity_type_id = cpei2.entity_type_id AND eet.entity_type_code='catalog_product'",
							array())
						->join(array('ea2' => $this->getTableName("eav_attribute")),
							"ea2.attribute_id = cpei2.attribute_id AND ea2.attribute_code='status'",
							array())							
                        ->where('cpei2.value = 2');
            
            $select = $this->_read->select()
                         ->distinct()
                         ->from(array('eaov' => $this->getTableName("eav_attribute_option_value")),
                            array('value'))
                         ->join(array('cpei' => $this->getTableName("catalog_product_entity_int")),
                            'eaov.option_id = cpei.value',
                            array())
                        ->join(array('cpsa' => $this->getTableName("catalog_product_super_attribute")),
                            'cpei.attribute_id = cpsa.attribute_id',
                            array('product_id'))
                        ->join(array('ea' => $this->getTableName("eav_attribute")),
                            'cpsa.attribute_id = ea.attribute_id',
                            array('attribute_code'))
                        ->where('cpsa.product_id IN (?)', $subSelect1)
                        ->where('cpsa.attribute_id IN (?)', $subSelect2)
            			->where('cpei.entity_id NOT IN (?)', $subSelect3);

			$result = $this->_read->query($select);

			foreach($result as $res)
			{
				if(!isset($configurProdAttVal[$res["product_id"]][$res["attribute_code"]]))
					$configurProdAttVal[$res["product_id"]][$res["attribute_code"]]=$res["value"];
				else
				{
					$configurProdAttVal[$res["product_id"]][$res["attribute_code"]].=",".$res["value"];
				}
			}
			
			unset($result);
			
			//--------------------------------------------------
		
			//select the name of the attributes
			$this->select_attributes($attributes);
		
			//------------------------------------------------------	
			$products_collection=array();
			$removeProducts=array();
			$cate=4;
			$typ=9;
			$chil=10;
			$childrenName="";
			$num=0;
			$s="";
		
			//print the header
			$s=$this->header($attributes,$cate,$typ,$chil);

			//Begin the output
			$sql='select min(cpe.entity_id ) as min, max( cpe.entity_id ) as max
			from '.$this->getTableName("catalog_product_entity").' cpe
			where cpe.type_id = "configurable"';
			$result=$this->_read->fetchAll($sql);
			
			$total_min = $result[0]["min"];
			$total_max = $result[0]["max"];
			if(!is_null($total_min))
			{
				$count=$total_max-$total_min+1;
				$count=$count/$this->_flushRecordsCount;
				if(!is_int($count)) $count=ceil($count);
				$min = $total_min;

				//select the product with the attributes
				//-----------------------------
				while($count>0)
				{
					$max=$min + $this->_flushRecordsCount -1;
					
					if ($use_flat_tables)
					{
						Mage::getResourceSingleton('catalog/product_flat')->setStoreId($this->_fStore_id);
						$products_collection=Mage::getModel("catalog/product")->getCollection();
					}
					else
					{
						$products_collection=Mage::getModel("catalog/product")->setStoreId($this->_fStore_id)->getCollection()->addStoreFilter($this->_fStore_id);
					}
					 
					//Adding attributes to select
					foreach($attributes as $key=>$value) $products_collection->addAttributeToSelect($key);
					$products_collection->addAttributeToSelect("type_id");
					//Adding attributes to filter
					$products_collection->addAttributeToFilter("type_id","configurable");
					$products_collection->addAttributeToFilter("visibility",array("neq"=>1));
					$products_collection->addAttributeToFilter("status",array("eq"=>1));
					$products_collection->addFieldToFilter("entity_id",array("from"=>$min,"to"=>$max));					
					
					$this->updateCategoriesMatrix($min, $max);
					
					$min=$max+1;
					$count--;
					//print the content
					$num+=count($products_collection);
					
					foreach($products_collection as $product)
					{
						$id=$product->getentity_id();
						$product = Mage::getModel('catalog/product')->load($id);
						$sql='SELECT sku AS id FROM '.$this->getTableName("catalog_product_super_link").' INNER JOIN '.$this->getTableName("catalog_product_entity").' ON product_id = entity_id WHERE parent_id = '.$id;
						$childProducts=$this->_read->fetchAll($sql);
						foreach($childProducts as $child)
						{
							$childrenName.=$child["id"].',';
							if(!in_array($child["id"],$removeProducts)) 
								$removeProducts[]=$child["id"].',';
						}
						if($childrenName!="")
							$childrenName=substr_replace($childrenName,"",strlen($childrenName)-1);
						if(isset($configurProdAttVal[$id])){
							$s.=str_replace("\r\n", " ", $this->content($product,$attributes,$cate,$typ,$chil,1,$configurProdAttVal[$id],$nameCategory,$childrenName));
						}else{
							$s.=str_replace("\r\n", " ", $this->content($product,$attributes,$cate,$typ,$chil,1,array(),$nameCategory,$childrenName));
						}
						$s.="\r\n";
						$childrenName="";
					}
					
					//Flushing and cleaning
					unset($products_collection);
					fwrite($fh, $s);
					unset($s);
					$s = "";
				}
			}

			//-------------------------------------------------------------------------------------------
			$sql='SELECT min( cpe.entity_id ) AS min, max( cpe.entity_id ) AS max
				FROM '.$this->getTableName("catalog_product_entity").' cpe
				WHERE cpe.type_id <> "configurable"';
				//AND cpev.store_id ='.$this->_fStore_id;/*Added for multi store view purposes by Eli Sagy*/
			$result=$this->_read->fetchAll($sql);
			$total_min = $result[0]["min"];
			$total_max = $result[0]["max"];

			if(!is_null($total_min))
			{
				$count=$total_max-$total_min+1;
				$count=$count/$this->_flushRecordsCount;
				if(!is_int($count)) $count=ceil($count);
				$min = $total_min;
				while($count>0)
				{
					$max=min($min + $this->_flushRecordsCount - 1, $total_max);
					//echo  "<br/>" .  "max:" . $max;
					
					if ($use_flat_tables)
					{
						Mage::getResourceSingleton('catalog/product_flat')->setStoreId($this->_fStore_id);
						$products_collection=Mage::getModel("catalog/product")->getCollection();
					}
					else
					{
						$products_collection=Mage::getModel("catalog/product")->setStoreId($this->_fStore_id)->getCollection()->addStoreFilter($this->_fStore_id);
					}
					
					//Adding attributes to select
					foreach($attributes as $key=>$value) $products_collection->addAttributeToSelect($key);
					$products_collection->addAttributeToSelect("type_id");
					//Adding attributes to filter
					$products_collection->addAttributeToFilter("type_id",array("neq"=>"configurable"));
					$products_collection->addAttributeToFilter("visibility",array("neq"=>1));
					$products_collection->addAttributeToFilter("status",array("eq"=>1));
					$products_collection->addFieldToFilter("entity_id",array("from"=>$min,"to"=>$max));
					
					$this->updateCategoriesMatrix($min, $max);
					
					$min=$max+1;
					$count--;
					//print the content
					$num+=count($products_collection);
					foreach($products_collection as $product)
					{
						$flg=0;
						if($product->gettype_id()=="simple")
						{
							foreach($removeProducts as $removeProduct)
							{
								if($removeProduct===$product->getentity_id().",")
								{
									$flg=1;
									$num--;
									break;
								}
							}
						}
						if($flg===0)
						{
							$id=$product->getentity_id();
							$product = Mage::getModel('catalog/product')->load($id);
							if($product->gettype_id()=="grouped" && $this->_getChildrenOfGroupProducts)
							{
								$sql='SELECT sku AS id FROM '.$this->getTableName("catalog_product_link").' INNER JOIN '.$this->getTableName("catalog_product_entity").' ON linked_product_id = entity_id WHERE link_type_id = 3 AND product_id = '.$id;
								$childProducts=$this->_read->fetchAll($sql);
								foreach($childProducts as $child)
								{
									$childrenName.=$child["id"].',';
								}
								if($childrenName!="")
									$childrenName=substr_replace($childrenName,"",strlen($childrenName)-1);
							}
							$s.=str_replace("\r\n", " ", $this->content($product,$attributes,$cate,$typ,$chil,0,array(),$nameCategory,$childrenName));
							$s.="\r\n";
						}
						$childrenName="";
					}

					//Flushing and cleaning
					unset($products_collection);
					fwrite($fh, $s);
					unset($s);
					$s = "";
				}
			}
			
			fclose($fh);
			unset($fh);
			
			$export_store_end = (float) array_sum(explode(' ',microtime()));
			
			$this->comments_style('success','Loaded '.$num.' records within ' . round($export_store_end - $export_store_start, 3) . ' sec','load');
			
			$this->comments_style('success','Saved successfully: ' . $this->_fileNameTxt . ' '.$this->_fSize.' byte(s)','save');
			
			//Zip file
			$filePath = $this->_fPath . "/" . $this->_fileNameTxt;
			$zipFilePath = $this->_fPath . "/" . $this->_fileNameZip;
			$this->_zipFile($filePath, $zipFilePath);
	
			//-------------------------------
			   //export

			if($this->_fType==="ftp" && $this->_bUpload)
			{
				$zipFilePath = $this->_fPath . DIRECTORY_SEPARATOR .  $this->_fileNameZip;
				$ftpRes = $this->ftpfile($zipFilePath);
				if(!$ftpRes) $this->comments_style('error','Could not upload to ftp','Could_not_upload_to_ftp');
			}
			
		}
		
		$export_end = (float) array_sum(explode(' ',microtime()));
		
		$this->comments_style('info','Finished profile execution within ' . round($export_end - $export_start, 3) . ' sec.','finish');
		$this->comments_style('finish',0,0);
	}
	
	
	function updateCategoriesMatrix($min, $max) {
		//select the name of the categories
		$this->_fProduct_Category_Matrix = array();
		$product_category_ids = $this->_read->fetchAll('SELECT product_id, category_id FROM '.$this->getTableName("catalog_category_product")
				. ' WHERE product_id BETWEEN ' . $min . ' AND ' . $max); //Added for multi store view purposes by Eli Sagy
		foreach($product_category_ids as $product_category_id)
		{
			if (!isset($this->_fProduct_Category_Matrix[$product_category_id['product_id']])) $this->_fProduct_Category_Matrix[$product_category_id['product_id']] = array();
	
			array_push($this->_fProduct_Category_Matrix[$product_category_id['product_id']], $product_category_id['category_id']);
		}
	}	
	
	//*********************************************************************************************************
	//*********************************************************************************************************
	//*********************************************************************************************************
       //*********************************************************************************************************
	//*************************************************function************************************************
	public function select_attributes(&$attributes) 
	{
		//select requied attributes
		$attributesreq=array('name'=>array('text',1),'url_key'=>array('text',2),
		'image'=>array('media_image',3),'description'=>array('textarea',5),'short_description'=>array('textarea',6),'status'=>array('select',7)
		,'visibility'=>array('select',8), 'thumbnail'=>array('text',11), 'is_salable'=>array('text',12), 'rating_summary'=>array('text',13), 'reviews_count'=>array('text',14));
		$i=count($attributesreq)+4;//entity_id,category_ides,type_id,childrenName
		//select entity_type_code=catalog product
		$sql='select entity_type_id
		from '.$this->getTableName("eav_entity_type").'
		where entity_type_code = "catalog_product"';
		
		$result=$this->_read->fetchAll($sql);
		$entity_type_id=$result[0]["entity_type_id"];
		//select attribute with type=price
		$sql='SELECT ea.attribute_id, attribute_code, frontend_input
			FROM '.$this->getTableName("eav_attribute").' ea
			WHERE (
			ea.entity_type_id ='.$entity_type_id.'
			)
			AND ea.frontend_input = "price"';
		
		$attributesInfo=$this->_read->fetchAll($sql);
		foreach($attributesInfo as $attribute)
		{
			$attributesreq[$attribute["attribute_code"]]=array("price",$i);
			$i++;
		}
		//select searchable or filterable attribute
		$joinWithCatEav = Mage::helper('salesperson')->is_CE() && Mage::helper('salesperson')->is_1_3() ? "" : "INNER JOIN {$this->getTableName('catalog_eav_attribute')} cea ON ea.attribute_id = cea.attribute_id";
		$whereCond = Mage::helper('salesperson')->is_CE() && Mage::helper('salesperson')->is_1_3() ? "ea.is_searchable =1 OR ea.is_filterable in(1,2)" : "cea.is_searchable =1 OR cea.is_filterable in(1,2)";

		
		$sql='SELECT ea.attribute_id, attribute_code, frontend_input
			FROM '.$this->getTableName("eav_attribute").' ea ' .
			$joinWithCatEav . '
			WHERE (
			ea.entity_type_id ='.$entity_type_id.'
			)
			AND (' .
			$whereCond . '
			)and ea.frontend_input<>"price"';

		$attributesInfo=$this->_read->fetchAll($sql);
		$attributessf=array();
		foreach($attributesInfo as $attribute)
		{
			$attributessf[$attribute["attribute_code"]]=array($attribute["frontend_input"],$i);
			$i++;
		}
		//Union 2 array
		$attributes=array_merge($attributessf,$attributesreq); 
	}
	//-------------------------------------------------------------------
       
	public function header($attributes,$cate,$typ,$chil)
	{
		$str="";
		$header=array();
		$name=1;
		$url_key =2;
		$image=3;
		$map = Mage::helper('salesperson/mapping');
		
		foreach($attributes as $key=>$value)  {
			$key = $map->getMapping($key);
			$header[$value[1]]=$this->_fEnclose.$key.$this->_fEnclose;
		}
		
		//---------------------------
		$header[$name]=$this->_fEnclose.$map->getMapping('title').$this->_fEnclose;
		$header[$url_key]=$this->_fEnclose.$map->getMapping('link').$this->_fEnclose;
		$header[$image]=$this->_fEnclose.$map->getMapping('image_link').$this->_fEnclose;
		$str.=$this->_fEnclose.$map->getMapping('id').$this->_fEnclose.$this->_fDel;
		$header[$cate]=$this->_fEnclose.$map->getMapping('category').$this->_fEnclose;
		$header[$typ]=$this->_fEnclose.$map->getMapping('type').$this->_fEnclose;
		$header[$chil]=$this->_fEnclose.$map->getMapping('children_skus').$this->_fEnclose;

		ksort($header);
		$att= implode($this->_fDel,$header);
		$str.=$att."\r\n";
		return $str;
	}
	//------------------------------------------------------------------
	public function content($product,$attributes,$cate,$typ,$chil,$config,$children,$nameCategory,$childrenName)
	{
        $sql="";
	    $result="";
	   	$products=array();
	    $att="";
	   	// $getcategoties=array();
	    $entity_id=$product->getentity_id();
	    $this->setProductPricingTiers($product);
		
		foreach($attributes as $att_code => $header_metadata)
		{
			$att_type = $header_metadata[0];
			$header_index = $header_metadata[1];
			
			if($config == 1 && array_key_exists($att_code,$children))
			{
				$products[$header_index]= $children[$att_code];
			}
			else
			{
				$products[$header_index] = $this->getProductAttributeValue($product, $att_code, $att_type);
			}
			
			//Create a copy of the product images in /media/sales_catalog, in order to remove dependency on Magento cache 
			$bCreateCopyOfImagesCatalog = true;
			if ($bCreateCopyOfImagesCatalog && $att_code == 'image') $products[$header_index] = $this->_copyImageToSalespersonCatalog($products[$header_index], $entity_id);
			
			$products[$header_index]=str_replace('"','""',$products[$header_index]);
			$products[$header_index] = str_replace( array("\r\n","\r","\n", "\t", "", "\v") ,' ', $products[$header_index]);		
			$pattern = '/&#[0-9a-fA-F]*;/';
			$products[$header_index] = preg_replace($pattern, '', $products[$header_index]);
			$products[$header_index]=$this->_fEnclose.$products[$header_index].$this->_fEnclose;
		}
	    //------------------------------
	
	    $products[0]=$this->_fEnclose.$entity_id.$this->_fEnclose;
	    //---------------------------------------------------
	     //Create the Query to get the products:getcategoty
	   	$products[$cate]=$this->_fEnclose;
		if (isset($this->_fProduct_Category_Matrix[$product->getId()]))
		{
			$getcategoties=$this->_fProduct_Category_Matrix[$product->getId()];
			if($getcategoties)
			{
				$has_categories = false;
				foreach($getcategoties as $getcategory)
				{
					if (isset($nameCategory[$getcategory]))
					{
						$products[$cate].=$nameCategory[$getcategory].',';
						$has_categories = true; 
					}
				}
				if ($has_categories) $products[$cate]=substr_replace($products[$cate],"",strlen($products[$cate])-1);
			}
		}

	    $products[$cate].=$this->_fEnclose;
	    //-----------------------
	    $products[$typ]=$this->_fEnclose.$product->gettype_id().$this->_fEnclose;
	    //---------------------------
	    $products[$chil]=$this->_fEnclose.$childrenName.$this->_fEnclose;
	    //----------------------
	    ksort($products);
	     $att=implode($this->_fDel, $products);
	   return $att;
	}
	
	protected function getProductAttributeValue($product, $att_code, $att_type)
	{
		$attributeValue = "";
		$func = 'get'.$att_code;
		
		switch ($att_type) {
			
			case "select":
			case "multiselect":
				$attributeValue = ($product->getData($att_code) == "") ? "" : trim($product->getResource()->getAttribute($att_code)->getFrontend()->getValue($product), " , ");
				break;
			case "media_image":
				$attributeValue = $product->getImageUrl();
				//$attributeValue = $product->getMediaConfig()->getMediaUrl($product->getData($att_code));
				break;

			case "textarea":
				$attributeValue = $product->{$func}();
				break;
				
			case "price":
				if($att_code==="price") $attributeValue = $this->getCalculatedPrice($product);
				elseif($att_code==="giftcard_amounts") $attributeValue = $this->getAllGiftcardAmounts($product);
				//elseif($att_code==="minimal_price") $attributeValue = $product->getMinimalPrice();
				else $attributeValue = $product->getData($att_code);
				break;
				
			default:
				if($att_code==="url_key") $attributeValue = Mage::helper('catalog/product')->getProductUrl($product);
				elseif($att_code==="thumbnail") $attributeValue = Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(66);
				//elseif ($att_code==="is_salable") $attributeValue = $product->isSaleable();
				elseif($att_code==="rating_summary") $attributeValue = $this->getRatingSummary($product);
				elseif($att_code==="reviews_count") $attributeValue = $this->getReviewsCount($product);
				else $attributeValue = $product->getData($att_code);
				break;
		}
		$attributeValue = substr($attributeValue, 0, 4000);
		return $attributeValue;
	}
	
	public function getRatingSummary($product){
		Mage::getModel('review/review')->getEntitySummary($product, $this->_fStore_id);
		$oRating_summary = $product->getRatingSummary();
		return $oRating_summary->getData("rating_summary");
	}
	
	public function getReviewsCount($product){
		Mage::getModel('review/review')->getEntitySummary($product, $this->_fStore_id);
		$oRating_summary = $product->getRatingSummary();
		return $oRating_summary->getData("reviews_count");
	}
	
	public function setProductPricingTiers($product){
		if($product->getTierPriceCount()) {
			$this->_aProductPricingTiers = array();
			$tiers = $product->getTierPrice();
			$this->_aProductPricingTiers = $tiers;
			
			/*$websiteId = $this->_fStore->getWebsite()->getId();
			foreach ($tiers as $tier) {
				//var_dump($tier["website_id"]);
				if($tier["website_id"] != $websiteId) continue;
				$this->_aProductPricingTiers[] = $tier;
			}*/
		}
		else {
			$this->_aProductPricingTiers = null;
		}
	}
	
	public function getCalculatedPrice($product)
	{
		$price = "";
		if ($product->getData("type_id") == "giftcard")
		{
			$min_amount = PHP_INT_MAX;
			if ($product->getData("open_amount_min") != null && Mage::getModel('catalog/product')->load($product->getId())->getData("allow_open_amount")) $min_amount = $product->getData("open_amount_min");
			foreach($product->getData("giftcard_amounts") as $amount)
			{
				if($min_amount > $amount["value"]) $min_amount = $amount["value"];
			}
			$price =  $min_amount;
		}
		elseif($this->_fStore->getConfig('salesperson/export_settings/min_tier_price') && is_array($this->_aProductPricingTiers)){
			$last = count($this->_aProductPricingTiers) - 1;
			$price = $this->_aProductPricingTiers[$last]["website_price"];
		}
		else {
			$price = $product->getPrice();
		}
		
		if($price == 0){
			//return; // XXX
			if($product->getData("type_id") == "bundle"){
				$priceModel  = $product->getPriceModel();
				list($minimalPriceTax, $maximalPriceTax) = $priceModel->getTotalPrices($product, null, null, false);
				$price = $this->_fStore->getConfig('salesperson/export_settings/min_tier_price') ? $minimalPriceTax : $maximalPriceTax;
			}
			elseif($product->getData("type_id") == "grouped")
			{
				$price = $product->getMinimalPrice();
			}
		}

		return number_format($price, 2, ".", ""); 
	}
	
	public function getTierPriceString() {
		$tier_price = "";
		if(is_array($this->_aProductPricingTiers) && count($this->_aProductPricingTiers)) {
			$arr = array();
			foreach ($this->_aProductPricingTiers as $tier) {
				$price_qty = $tier["price_qty"];
				$website_price = $tier["website_price"];
				$arr[$price_qty] = $website_price;
			}
			
			$tier_price = Mage::helper('salesperson')->array_implode( "=>", ",", $arr);
		}
		
		
		return $tier_price;
	}
	
	public function getAllGiftcardAmounts($product)
	{
		if($product->getData("type_id") == "giftcard")
		{
			$list = "";
			if(Mage::getModel('catalog/product')->load($product->getId())->getData("allow_open_amount")) $list = $product->getData("open_amount_min")."-".$product->getData("open_amount_max");
			foreach($product->getData("giftcard_amounts") as $amount)
			{
				if($list != "") $list .= ", ";
				$list .= $amount["value"];
			}
			return $list;
		}
		return null;
		//die($priceObject[0]["value"]);
	}
	
	//--------------------------------
	public function ftpfile($zipFilePath)
	{
		if (!file_exists($zipFilePath)) {
			$this->comments_style('error','No ' . $zipFilePath . ' file found','No_zip_file_found');
			return false;
		}	
	
	    $ioConfig=array();
		
	    if ($this->_fFTPHost != '')
	    {
			$ioConfig['host'] = $this->_fFTPHost;
	    }
	    else
	    {
			$this->comments_style('error','Empty host specified','Empty_host');
			return false;
	    }
	    if ($this->_fFTPPort != '')
	    {
			$ioConfig['port'] = $this->_fFTPPort;
	    }
	  
	    if ($this->_fFTPUser != '')
	    {
			$ioConfig['user'] = $this->_fFTPUser;
	    }
	    else
	    {
			$ioConfig['user']='anonymous';
			$ioConfig['password']='anonymous@noserver.com';
	    }
	    if ($this->_fFTPPassword != '')
	    {
		    $ioConfig['password'] = $this->_fFTPPassword;
	    }
	    
		$ioConfig['passive'] = $this->_fFTPPassive;
	    
	    if ($this->_fPath != '')
	    {
			$ioConfig['path']= $this->_fPath;
	    }
	    $this->_config = $ioConfig;
	    $this->_conn =@ftp_connect($this->_config['host'], $this->_config['port']);
	     if (!$this->_conn)
	     {
		$this->comments_style('error','Could not establish FTP connection, invalid host or port','invalid_ftp_host/port');
	       return false;
		}
		if (!@ftp_login($this->_conn, $this->_config['user'], $this->_config['password']))
		{
			$this->close();
			$this->comments_style('error','Could not establish FTP connection, invalid user name or password','Invalid_ftp_user_name_or_password');
			return false;
	    }
	    /*if ($this->_config['path']!='')
	    {
		if (!$this->ftp_is_dir($this->_conn, $this->_config['path']))
		{
		 if(!$this->make_directory($this->_conn,$this->_config['path']))
		 {
			$this->close();
			$this->comments_style('error','Invalid path(or couldn\'t create the folders because of permissions)','invalid_path');
			return false;
		 }
		}
	    }*/
	
		if (!@ftp_pasv($this->_conn, true)) {
		    $this->close();
		    $this->comments_style('error','Invalid file transfer mode','Invalid_file_transfer_mode');
		    return false;
		}
		
		if (!file_exists($zipFilePath)) {
			$this->comments_style('error','No ' . $zipFilePath . ' file found','No_zip_file_found');
		}		
		
		$upload = @ftp_put($this->_conn, basename($zipFilePath), $zipFilePath, FTP_BINARY);
		if(!$upload){
		     $this->comments_style('error','File upload failed','File_upload_failed');
			 $upload=false;
		}
	
		return $upload;
	}
	//----------------------------------------------------
	public function make_directory($ftp_stream, $dir)
	{
		// if directory already exists or can be immediately created return true
		if ($this->ftp_is_dir($ftp_stream, $dir) || @ftp_mkdir($ftp_stream, $dir)) return true;
		// otherwise recursively try to make the directory
		if (!$this->make_directory($ftp_stream, dirname($dir))) return false;
		// final step to create the directory
		return @ftp_mkdir($ftp_stream, $dir);
	}  
        //----------------------------------------
	public function ftp_is_dir($ftp_stream, $dir)
	{
	// get current directory
	$original_directory = ftp_pwd($ftp_stream);
	// test if you can change directory to $dir
	// suppress errors in case $dir is not a file or not a directory
	if ( @ftp_chdir( $ftp_stream, $dir ) ) {
	    // If it is a directory, then change the directory back to the original directory
	   ftp_chdir( $ftp_stream, $original_directory );
	    return true;
	} else {
	    return false;
	}
	}
	//----------------------------------------------------
	   public function close()
	{
	    return ftp_close($this->_conn);
	}
	//-----------------------------------------
	public function comments_style($kind,$text,$alt)
	{
	    switch($kind)
	    {
	    case 'header':
		echo 	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html><head><style type="text/css">
			ul { list-style-type:none; padding:0; margin:0; }
			li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif;  }
			img { margin-right:5px; }
			</style><title>Salesperson Exporter</title></head>
			<body><ul>';
	    break;
	    case 'icon':
		echo 	'<li style="background-color: rgb(128, 128, 128); color:rgb(255,255,255);">
			<img style="margin-right: 5px;" src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/note_msg_icon.gif" alt='.$alt.'/>
			'.$text.'
			</li>';
	    break;
	    case 'info':
	    	echo 	'<li>
			<img style="margin-right: 5px;" src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/note_msg_icon.gif" alt='.$alt.'/>
			'.$text.'
			</li>';
	    	break;
            case 'warning':
		echo 	'<li style="background-color: rgb(255, 255, 128);">
			<img style="margin-right: 5px;" src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/fam_bullet_error.gif" alt='.$alt.'/>
			'.$text.'
			</li>';
	    break;
	    case 'success':
		echo '<li style="background-color: rgb(128, 255, 128);">
		    <img src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/fam_bullet_success.gif" alt='.$alt.'/>
		    '.$text.'
		    </li>';
	    break;
	    case 'error':
		echo '<li style="background-color: rgb(255, 187, 187);">
		    <img src="'.Mage::getBaseUrl().'../skin/adminhtml/default/default/images/error_msg_icon.gif" alt='.$alt.'/>
		    '.$text.'
		    </li>';
	    break;
	    default:
		echo '</ul></body></html>';
		}
	}
	    //-------------------------------------------------------------------
	public function export_config($store_id)
	{
		$this->_fStore_id = $store_id;
		Mage::app()->setCurrentStore($store_id);
		$this->_fStore = Mage::app()->getStore($store_id);
		$this->_fStore_export_enabled = $this->_fStore->getConfig('salesperson/export_settings/export_enabled');
		//feature is not in use
		if( isset($_GET["upload"]) && $_GET["upload"] == "false") $this->_bUpload = false;
		else if( getenv ("upload") && getenv ("upload") == "false") $this->_bUpload = false;
		else $this->_bUpload = true;
		//end
		$this->_fDel = $this->_fStore->getConfig('salesperson/export_settings/delimiter');
		if($this->_fDel==='\t') $this->_fDel="	";
		$this->_fEnclose = $this->_fStore->getConfig('salesperson/export_settings/enclosed_values');		
		$this->_fType = $this->_fStore->getConfig('salesperson/export_settings/type');
		$this->_fPath = Mage::app()->getStore(0)->getConfig('salesperson/export_settings/path').'/'.$this->_fStore->getWebsite()->getCode().'/'.$this->_fStore->getCode();
		$this->_fFTPHost = $this->_fStore->getConfig('salesperson/export_settings/ftp_host');
		$this->_fFTPPort = $this->_fStore->getConfig('salesperson/export_settings/ftp_port');
		$this->_fFTPUser = $this->_fStore->getConfig('salesperson/export_settings/ftp_user');
		$this->_fFTPPassword = $this->_fStore->getConfig('salesperson/export_settings/ftp_password');
		$this->_fFTPPassive = $this->_fStore->getConfig('salesperson/export_settings/passive');
		//feature is not in use
		$this->_fEnableCron = $this->_fStore->getConfig('salesperson/export_settings/cron_enabled');
		$this->_fExportProfileId = $this->_fStore->getConfig('salesperson/export_settings/profile_id');
		$this->CronExpression = $this->_fStore->getConfig('salesperson/export_settings/cron_expr');
		//end
	}
	//---------------------------
	public function getCategoryCollection($storeId)
    {
		$categoriesPool = $collection = Mage::getModel('catalog/category')->getCollection()->addNameToResult()
       	         ->addAttributeToFilter("is_active",array("neq"=>0))      
	             ->setStoreId($storeId);

		foreach($categoriesPool as $category)
		{
			if (!in_array($storeId, $category->getStoreIds())) $collection->removeItemByKey($category->getId());
		}

       	return $collection;
    }

	//-----------------------------------------
	public function getTableName($tableName)
	{
	$newTableName= Mage::getSingleton('core/resource')->getTableName($tableName);
	return $newTableName;
	}
	
	protected function _copyImageToSalespersonCatalog($imageUrl, $productId){
		
		$resultUrl = "";
		$mediaDir = Mage::getBaseDir("media");
		$pos = strpos($imageUrl, "media") + strlen("media");
		$catalogRelativeImagePath = substr($imageUrl, $pos + 1, strlen($imageUrl) - $pos - 1);
		$sourceImagePath = $mediaDir . DIRECTORY_SEPARATOR . $catalogRelativeImagePath;
		if(file_exists($sourceImagePath)){
				$fileName = basename($sourceImagePath);
				$destinationFolder = $mediaDir . DIRECTORY_SEPARATOR . "sales_catalog" . DIRECTORY_SEPARATOR . "product" . DIRECTORY_SEPARATOR . $productId;
				if(!is_dir($destinationFolder)){
    				if(!mkdir($destinationFolder, 0777, true))
    				{
    						echo "failed to create directory $destinationFolder ...\n";
               	exit();
    				}
				}

				/*$newCatalogRelativePath = str_replace("catalog", "sales_catalog", $catalogRelativePath);
				$destinationFolder = $mediaDir . DIRECTORY_SEPARATOR . dirname($newCatalogRelativePath) ;
				if(!is_dir($destinationFolder)){
    				if(!mkdir($destinationFolder, 0777, true))
    				{
    						echo "failed to create directory $destinationFolder ...\n";
               	exit();
    				}
				}*/
				
				$destinationImagePath = $destinationFolder . DIRECTORY_SEPARATOR . $fileName;					
				//var_dump($destinationImagePath); exit();
				if (!copy($sourceImagePath, $destinationImagePath)) {
            echo "failed to copy $sourceImagePath to $destinationImagePath ...\n";
           	exit();
   			}
		}
		
		$resultUrl = substr($imageUrl, 0, $pos) . DIRECTORY_SEPARATOR . "sales_catalog" . DIRECTORY_SEPARATOR . "product" . DIRECTORY_SEPARATOR . $productId . DIRECTORY_SEPARATOR . $fileName;
		return $resultUrl;
	}
	
	public function getUrl(Varien_Event_Observer $observer)
	{
		$category = $observer->getCategory();
		$url = Mage::helper('salesperson')->getResultUrl($category->getName());
		$category->setData('url', $url);
	}

	public function export_orders_celebros()
	{
		$enclosed = '"';
		$delimeter = "	";
		$newLine = "\r\n";
	
		$this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');
		$store_view_ids = $this->_read->fetchAll('SELECT store_id FROM '.$this->getTableName("core_store") . ' WHERE store_id <> 0'); //Added for multi store view purposes by Eli Sagy
	
		foreach($store_view_ids as $store_view_id)
		{
			$this->export_config($store_view_id['store_id']);
	
			if(!$this->_fStore_export_enabled) continue;
	
			$header = array("OrderID", "ProductID", "Date", "Count", "Sum");
			$glue = $enclosed . $delimeter . $enclosed;
			$strResult = $enclosed . implode($glue, $header) . $enclosed . $newLine;
	
			$sql = $this->_getOrdersSql();
			$stm = $this->_read->query($sql);
	
			while($row = $stm->fetch()) {
				$record["OrderID"] = $row["order_id"];
				$record["ProductID"] = $row["product_id"];
				$created_at_time = strtotime($row["created_at"]);
				$record["Date"] = date("Y-m-d", $created_at_time);
				$record["Count"] = (int) $row["qty_ordered"];
				$record["Sum"] = $row["row_total"];;
				$strResult .= $enclosed . implode($glue, $record) . $enclosed . $newLine;
			}
	
			//Create, flush, zip and ftp the orders file
			$this->_createAndUploadOrders($strResult);
			$this->comments_style('success',"Exported orders of store '{$this->_fStore_id}'. Memory peak was: " . memory_get_peak_usage(),'orders');
		}
	}
	
	protected function _getOrdersSql(){
		$sql = "";
		$from = date("Y-m-d H:i:s", (time()-60*60*24*30*6)); //return approximately last 6 months orders
		$to = date("Y-m-d H:i:s", time());
		$orderItemTable = $this->getTableName("sales_flat_order_item");
		
		if($this->_isStoreIdColumnExist($orderItemTable))
		{
			$sql = "SELECT order_id, product_id, created_at, qty_ordered, row_total, row_total_incl_tax
			FROM {$orderItemTable}
			WHERE store_id = {$this->_fStore_id} AND created_at between '{$from}' AND '{$to}' AND parent_item_id IS NULL
			";
		}
		else
		{
			$ordersTable = $this->_getOrdersTable();
			$sql = "SELECT item.order_id, item.product_id, item.created_at, item.qty_ordered, item.row_total
			FROM
				(SELECT order_id, product_id, created_at, qty_ordered, row_total
				FROM {$orderItemTable}
				WHERE created_at between '{$from}' AND '{$to}' AND parent_item_id IS NULL) as item
			JOIN
				(SELECT entity_id
				FROM {$ordersTable}
				WHERE store_id = {$this->_fStore_id} AND created_at between '{$from}' AND '{$to}') as sales_order
			ON sales_order.entity_id = item.order_id
			";
		}
		return $sql;
	}
	
	protected function _isStoreIdColumnExist($table) {
		$sql = "SHOW COLUMNS FROM {$table} LIKE 'store_id'";
		return (bool)$this->_read->fetchOne($sql);
	}
	
	protected function _getOrdersTable() {
		$table = $this->getTableName("sales_flat_order");
		$sql = "SHOW TABLES LIKE '{$table}'";
		$bExist = (bool)$this->_read->fetchOne($sql);
		return ($bExist) ? $table : $this->getTableName("sales_order");
	}
	
	protected function _createAndUploadOrders($str){
		
		//Create directory to put the file
		if(!$this->_createDir($this->_fPath)) {
			$this->comments_style('error','Could not create the directory in ' . $this->_fPath . ' path','problemwith dir');
			return;
		}
		
		$filePath = $this->_fPath . DIRECTORY_SEPARATOR .  "Data_history.txt";
		$zipFilePath = $this->_fPath . DIRECTORY_SEPARATOR .  "Data_history.zip";
		
		//Create file
		if((!$fh = $this->_createFile($filePath))) {
			$this->comments_style('error','Could not create the file in ' . $filePath,'problemwith file');
			return;
		}
		
		//Flush string orders data to file
		$this->_stringToTextFile($str, $fh);
		fclose($fh);
		
		//Zip file
		$this->_zipFile($filePath, $zipFilePath);

		//Ftp file
		if($this->_fType==="ftp" && $this->_bUpload)
		{
			$ftpRes = $this->ftpfile($zipFilePath);
			if(!$ftpRes) $this->comments_style('error','Could not upload ' . $zipFilePath . ' to ftp','Could_not_upload_to_ftp');
		}
	}
	
	protected function _createDir($dirPath) {
		if (!is_dir($dirPath)) $dir=@mkdir($dirPath, 0777,true);
		return $dirPath;
	
	}	
	
	protected function _createFile($filePath) {
		if (file_exists($filePath)) unlink($filePath);
		$fh = fopen($filePath, 'ab');
		return $fh ;
		
	}
		
	protected function _stringToTextFile($str, $fh){
		fwrite($fh, $str);
	}
	
	protected function _zipFile($filePath, $zipFilePath)
	{
		$out = false;
	
		if (!file_exists($filePath)) {
			$this->comments_style('error','No ' . $filePath . ' file found','No_txt_file_found');
			exit();
			return false;
		}
	
		$zip = new ZipArchive();
		if ($zip->open($zipFilePath, ZipArchive::CREATE) == true) {
			$out = $zip->addFile($filePath, basename($filePath));
			if(!$out) $this->comments_style('error','Could not add ' . $filePath . 'to zip archive','Could_not_add_txt_file_to_zip_file');
			$zip->close();
			unlink($filePath);
		}
		else
		{
			$this->comments_style('error','Could not create ' . $zipFilePath . ' file','Could_not_create_zip_file');
		}
	
		return $out;
	}

    public function url_rewrite_reindex()
    {
        $this->_read=Mage::getSingleton('core/resource')->getConnection('core_read');


        $store_view_ids = $this->_read->fetchAll('SELECT store_id,name FROM '.$this->getTableName("core_store") . ' WHERE store_id <> 0');

        foreach($store_view_ids as $store_view)
        {
            $store_view_id=$store_view['store_id'];
            $store_view_name=$store_view['name'];


            Mage::log("Salesperson- Checking need for reindexing of URL rewrite for store: {$store_view_name}, ID:{$store_view_id}",null,'celebros.log',true);

            if(Mage::getStoreConfigFlag('salesperson/nav_to_search_settings/nav_to_search',$store_view_id))
            {
                $rewrite_start = (float) array_sum(explode(' ',microtime()));
                Mage::helper('salesperson')->updateCategoriesUrlRewrites($store_view_id);
                $rewrite_end = (float) array_sum(explode(' ',microtime()));

                Mage::log("Salesperson- Reindexed URL rewrite for store: {$store_view_name}, ID:{$store_view_id}. Total Time: ".round($rewrite_end - $rewrite_start, 1) . ' sec.',null,'celebros.log',true);
            }
        }
    }


    public function after_reindex_process_catalog_url($observer)
    {
        $this->url_rewrite_reindex();
    }
}
