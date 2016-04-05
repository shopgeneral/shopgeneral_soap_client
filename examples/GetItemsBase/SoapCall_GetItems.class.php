<?php

require_once ROOT.'lib/soap/call/PlentySoapCall.abstract.php';

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
			$oPlentySoapRequest_GetItemsBase = new PlentySoapRequest_GetItemsBase();

			$response	=	$this->getPlentySoap()->GetItemsBase($oPlentySoapRequest_GetItemsBase);
			//var_dump($response);
			
			if( $response->Success == true )
			{
				$this->parseResponse($response);
				
// 				$this->value = $response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
		
// 				$this->getLogger()->debug(__FUNCTION__.' Request Success : ' . $this->$value );
			}
			else
			{
				$this->getLogger()->debug(__FUNCTION__.' Request Error');
			}
			}
			catch(Exception $e)
		
			{
				$this->onExceptionAction ( $e );
			}
		}
		
	private function parseResponse($response)
	{
		if(is_array($response->ItemsBase->item))
		{
			/*
			* If more than one country of delivery
			*/
			foreach ($response->ItemsBase->item as $itemsBase)
			{
				echo $itemsBase->LastInsertedFrom;
				echo $itemsBase->ItemID;
				echo("\n");
			}
		}
		/*
		* only one country of delivery
		*/
		elseif (is_object($response->ItemsBase->item))
		{
			$this->saveInDatabase($response->CountriesOfDelivery->item);
		}
	}
}

?>