<?php

require_once ROOT.'lib/soap/call/PlentySoapCall.abstract.php';
require_once ROOT.'examples/UpdateItems/MagentoItem.class.php';

class SoapCall_UpdateItems extends PlentySoapCall
{
	private static $instance = null;
	private $value = null;
	
	private static $magentoClient = null;
	private static $magentoSession = null;
	
	private $lastUpdateFrom = null;
	
	public function __construct()
	{
		parent::__construct(__CLASS__);
		$this->initMagentoController();
	}
	
	public static function getInstance()
	{
		if( !isset(self::$instance) || !(self::$instance instanceof SoapCall_UpdateItems) )
		{
			self::$instance = new SoapCall_UpdateItems();
		}
	
		return self::$instance;
	}
	
	private function initMagentoController(){
		$magentoSoapClient = MagentoSoapClient::getInstance();
		$magentoSoapClient->doAuthentification();
		self::$magentoSession = $magentoSoapClient->getSession();
		self::$magentoClient = $magentoSoapClient->getSoapClient();
	}
	
	/* (non-PHPdoc)
	 * @see PlentySoapCall::execute()
	 */
	public function execute() {
		// TODO Auto-generated method stub
		try
		{
			$this->lastUpdateFrom = $this->checkLastUpdate();
			$lastUpdateTill = time();//1459728000
			
			$itemsBaseResponse = $this->getItemsBase($this->lastUpdateFrom, $lastUpdateTill);
			
			$totalPages = $itemsBaseResponse->Pages-1;
			$i = 0;
			while($i <= $totalPages){

				$itemsBaseByPageResponse = $this->getItemsBaseByPage($this->lastUpdateFrom, $lastUpdateTill, $i);

				if( $itemsBaseByPageResponse->Success == true )
				{
					$this->getLogger()->info(__FUNCTION__.':: Request Page: '.$i.' :: Success');
					$this->parseResponse($itemsBaseByPageResponse);
				}
				else
				{
					$this->getLogger()->debug(__FUNCTION__.' Request Error');
				}
				
				$i++;
			}

		} catch(Exception $e)
		{
			$this->onExceptionAction ( $e );
		}
		
// 		$this->setLastUpdate($lastUpdateTill);
	}
		
	private function parseResponse($response)
	{
		if(is_array($response->ItemsBase->item))
		{
			foreach ($response->ItemsBase->item as $itemBase)
			{
				$this->getLogger()->info(__FUNCTION__.'::  Starte Update for'.' ItemID: '.$itemBase->ItemID);
				$magentoItem = $this->convertToMagentoItem($itemBase);
				$this->pushItemToMagento($magentoItem);
			}
		}
	}
	
	private function getItemsBase($lastUpdateFrom, $lastUpdateTill){
		$oPlentySoapRequest_GetItemsBase = new PlentySoapRequest_GetItemsBase();
		$oPlentySoapRequest_GetItemsBase->LastUpdateFrom = $this->lastUpdateFrom;
		$oPlentySoapRequest_GetItemsBase->LastUpdateTill = $lastUpdateTill;
		$response = $this->getPlentySoap()->GetItemsBase($oPlentySoapRequest_GetItemsBase);
		return $response;
	}
	
	private function getItemsBaseByPage($lastUpdateFrom, $lastUpdateTill, $page){
		$oPlentySoapRequest_GetItemsBase = new PlentySoapRequest_GetItemsBase();
		$oPlentySoapRequest_GetItemsBase->LastUpdateFrom = $this->lastUpdateFrom;
		$oPlentySoapRequest_GetItemsBase->LastUpdateTill = $lastUpdateTill;
		$oPlentySoapRequest_GetItemsBase->Page = $page;
		$response = $this->getPlentySoap()->GetItemsBase($oPlentySoapRequest_GetItemsBase);
		return $response;
	}
	
	private function getItemTexts($itemID){
		$oPlentySoapObject_RequestItems = new PlentySoapObject_RequestItems();
		$oPlentySoapObject_RequestItems->ItemId = $itemID;
		$oPlentySoapObject_RequestItems->Lang = "en";
			
		$oArrayOfPlentysoapobject_requestitems = new ArrayOfPlentysoapobject_requestitems();
		$oArrayOfPlentysoapobject_requestitems->item = $oPlentySoapObject_RequestItems;
			
		$oPlentySoapRequest_GetItemsTexts = new PlentySoapRequest_GetItemsTexts();
		$oPlentySoapRequest_GetItemsTexts->ItemsList = $oArrayOfPlentysoapobject_requestitems;
		
		$response	=	$this->getPlentySoap()->GetItemsTexts($oPlentySoapRequest_GetItemsTexts);
		return $response;
	}
	
	private function pushItemToMagento($magentoItem){
		$attributeSets = self::$magentoClient->call(self::$magentoSession, 'product_attribute_set.list');
		$attributeSet = current($attributeSets);
	
		try{
			$result = self::$magentoClient->call(self::$magentoSession, 'catalog_product.create', $magentoItem->getProductCreateEntityArray("neu", $attributeSet));
		} catch(Exception $e)
		{
			if($e->getMessage() === "Der Wert des Attributs \"SKU\" muss einmalig sein."){
				$result = self::$magentoClient->call(self::$magentoSession, 'catalog_product.update', $magentoItem->getProductCreateEntityArray("update", $attributeSet));
			}
		}
	}
	
	private function convertToMagentoItem($itemBase){
		
		$itemTexts = $this->getItemTexts($itemBase->ItemID);

		$item = new MagentoItem();
		$item->setSKU($itemBase->ItemNo);
		$item->setName($itemBase->Texts->Name);
		$item->setDescription($itemTexts->ItemTexts->item[0]->LongDescription);
		$item->setShortDescription($itemTexts->ItemTexts->item[0]->ShortDescription);
		$item->setWeight($itemBase->PriceSet->WeightInGramm);
		if($itemBase->Availability->Inactive === 0){
			$item->setStatus(1);
		}else {
			$item->setStatus(0);
		}
		$item->setUrlKey($itemTexts->ItemTexts->item[0]->UrlContent);
		$item->setPrice($itemBase->PriceSet->Price);
		$item->setSpecialPrice($itemBase->PriceSet->Price1);
		if($itemBase->PriceSet->VAT == 7){
			$item->setTaxClassId(2);
		} elseif ($itemBase->PriceSet->VAT == 19){
			$item->setTaxClassId(1);
		}else {
			$item->setTaxClassId(0);
		}
		$item->setMetaTitle($itemBase->Texts->Name);
		$item->setMetaKeyword($itemBase->Texts->Keywords);
		$item->setMetaDescription($itemTexts->ItemTexts->item[0]->MetaDescription);
	
		return $item;
		
		// 		$imageItem = $this->getImages();
		
		// 		$imgData = $this->getImgUrlToBase64($imageItem->ItemsImages->item[0]->ImageURL);
		
		// 		$file = array(
		// 				'content' => $imgData,
		// 				'mime' => 'image/jpeg'
		// 		);
		
		// 		$result = self::$magentoClient->call(
		// 				self::$magentoSession,
		// 				'catalog_product_attribute_media.create',
		// 				array(
		// 						$itemBase->ItemNo,
		// 						array('file'=>$file, 'label'=>'test', 'position'=>'100', 'types'=>array('thumbnail'), 'exclude'=>0)
		// 				)
		// 		);
	}
	
	private function checkLastUpdate(){
		$query = 'SELECT `last_update` FROM `plenty_last_items_update`'.DBUtils::buildWhere( array( 'id' => 1));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		$result = DBQuery::getInstance()->select($query, 'DBQueryResult');
		return $result->fetchAssoc()["last_update"];
	}
	
	private function setLastUpdate($lastUpdateTill){
		$query = 'REPLACE INTO `plenty_last_items_update` '.DBUtils::buildInsert(	array(	'id' => 1, 'last_update'	=>	$lastUpdateTill));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		DBQuery::getInstance()->replace($query);
	}
	
	
// 	private function getImages(){
// 		$oPlentySoapRequest_GetItemsImages = new PlentySoapRequest_GetItemsImages();
// 		$oPlentySoapRequest_GetItemsImages->LastUpdateFrom = $this->lastUpdateFrom;
// 		$oPlentySoapRequest_GetItemsImages->LastUpdateTo = time();
// 		$response =	$this->getPlentySoap()->GetItemsImages($oPlentySoapRequest_GetItemsImages);
// 		return $response;
// 	}
	
// 	public function getImgUrlToBase64($imgUrl){
// 		$image = file_get_contents($imgUrl);
// 		if ($image !== false){
// 			return base64_encode($image);
// 		}
// 	}
}

?>