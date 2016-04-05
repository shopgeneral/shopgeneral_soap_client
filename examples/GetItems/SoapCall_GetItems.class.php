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
			
			$o = new PlentySoapRequest_GetItemsTexts();
		
			$response	=	$this->getPlentySoap()->GetItemsTexts($o);
		
			if( $response->Success == true )
			{
				$this->value = $response->ResponseMessages->item[0]->SuccessMessages->item[0]->Value;
		
				$this->getLogger()->debug(__FUNCTION__.' Request Success - GetItemsTexts : ' . $this->$value );
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
}

?>