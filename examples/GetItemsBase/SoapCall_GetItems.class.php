<?php

require_once ROOT.'lib/soap/call/PlentySoapCall.abstract.php';
require_once ROOT.'examples/GetItemsBase/MagentoItem.class.php';

class SoapCall_GetItems extends PlentySoapCall
{
	private static $instance = null;
	private $value = null;
	
	public function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	public static function getInstance()
	{
		if( !isset(self::$instance) || !(self::$instance instanceof SoapCall_GetItems) )
		{
			self::$instance = new SoapCall_GetItems();
		}
	
		return self::$instance;
	}
	
	/* (non-PHPdoc)
	 * @see PlentySoapCall::execute()
	 */
	public function execute() {
		// TODO Auto-generated method stub
		try
		{
			$lastUpdateFrom = $this->checkLastUpdate();
			$lastUpdateTill = time();//1459728000;
			
			//$this->setLastUpdate($lastUpdateTill);
			//exit;
			
			
			$oPlentySoapRequest_GetItemsBase = new PlentySoapRequest_GetItemsBase();
			$oPlentySoapRequest_GetItemsBase->LastUpdateFrom = $lastUpdateFrom;
			$oPlentySoapRequest_GetItemsBase->LastUpdateTill = $lastUpdateTill;
			
			
// 			$oPlentySoapObject_RequestItems = new PlentySoapObject_RequestItems();
// 			$oPlentySoapObject_RequestItems->ItemId = 12548;
// 			$oPlentySoapObject_RequestItems->Lang = "en";
			
// 			$oArrayOfPlentysoapobject_requestitems = new ArrayOfPlentysoapobject_requestitems();
// 			$oArrayOfPlentysoapobject_requestitems->item = $oPlentySoapObject_RequestItems;
			
// 			$oPlentySoapRequest_GetItemsTexts = new PlentySoapRequest_GetItemsTexts();
// 			$oPlentySoapRequest_GetItemsTexts->ItemsList = $oArrayOfPlentysoapobject_requestitems;
			
// 			$response	=	$this->getPlentySoap()->GetItemsTexts($oPlentySoapRequest_GetItemsTexts);
			
// 			var_dump($response);
			
// 			exit;

			
			$response	=	$this->getPlentySoap()->GetItemsBase($oPlentySoapRequest_GetItemsBase);
			$totalPages = $response->Pages-1;
			
			$i = 0;
			while($i <= $totalPages){
				$oPlentySoapRequest_GetItemsBase = new PlentySoapRequest_GetItemsBase();
				$oPlentySoapRequest_GetItemsBase->LastUpdateFrom = $lastUpdateFrom;
				$oPlentySoapRequest_GetItemsBase->LastUpdateTill = $lastUpdateTill;
				$oPlentySoapRequest_GetItemsBase->Page = $i;
				$oPlentySoapRequest_GetItemsBase->Lang = "en";
				
				$response	=	$this->getPlentySoap()->GetItemsBase($oPlentySoapRequest_GetItemsBase);
				var_dump($response);

				if( $response->Success == true )
				{
					$this->getLogger()->debug(__FUNCTION__.' Request Page: '.$i.'/'.$totalPages.' Success');
					$this->parseResponse($response);
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
				$this->getLogger()->info(__FUNCTION__.' ItemID: '.$itemBase->ItemID.':  Starte Update');
				$this->convertToMagentoItem($itemBase);
			}
		}
	}
	
	private function checkLastUpdate(){
 		$query = 'SELECT `last_update` FROM `plenty_last_items_update`'.DBUtils::buildWhere( array( 'id' => 1));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		$result = DBQuery::getInstance()->select($query, DBQueryResult);
		return $result->fetchAssoc()["last_update"];
	}
	
	private function setLastUpdate($lastUpdateTill){
		$query = 'REPLACE INTO `plenty_last_items_update` '.DBUtils::buildInsert(	array(	'id' => 1, 'last_update'	=>	$lastUpdateTill));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		DBQuery::getInstance()->replace($query);
	}
	
	private function convertToMagentoItem($itemBase){
		$item = new MagentoItem();
		$item->setSKU($itemBase->ItemNo);
		$item->setName($itemBase->Texts->Name);
		$item->setDescription($itemBase->Texts->LongDescription);
		$item->setShortDescription($itemBase->Texts->ShortDescription);
		$item->setWeight($itemBase->PriceSet->WeightInGramm);
		$item->setStatus($itemBase->Availability->Inactive);
		$item->setPrice($itemBase->PriceSet->Price);
		$item->setSpecialPrice($itemBase->PriceSet->special_price);
		if($itemBase->PriceSet->VAT == 7){
			$item->setTaxClassId(2);
		} elseif ($itemBase->PriceSet->VAT == 19){
			$item->setTaxClassId(1);
		}else {
			$item->setTaxClassId(0);
		}
		$item->setMetaTitle($itemBase->Texts->Name);
		$item->setMetaKeyword($itemBase->Texts->Keywords);
		$item->setMetaDescription($itemBase->Texts->MetaDescription);
		
		$result = $item->getSoapCallResult();
		var_dump($result);
	}
}

?>