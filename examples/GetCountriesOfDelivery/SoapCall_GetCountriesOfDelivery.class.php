<?php

require_once ROOT.'lib/soap/call/PlentySoapCall.abstract.php';

/**
 * 
 * It might be a better idea to run this call via 
 * PlentySoap.daemon.php
 * So you can keep you local db/system up2date in an easy way
 * 
 */

class SoapCall_GetCountriesOfDelivery extends PlentySoapCall 
{
	
	private static $instance = null;
	
	public function __construct()
	{
		parent::__construct(__CLASS__);
	}
	
	public static function getInstance()
	{
		if( !isset(self::$instance) || !(self::$instance instanceof SoapCall_GetCountriesOfDelivery) )
		{
			self::$instance = new SoapCall_GetCountriesOfDelivery();
		}
	
		return self::$instance;
	}
	
	public function execute() 
	{
		try
		{
			$this->getLogger()->debug(__FUNCTION__.' start');
			
			/*
			 * do soap call
			 */
			$response	=	$this->getPlentySoap()->GetCountriesOfDelivery(new PlentySoapRequest_GetCountriesOfDelivery);
			
			/*
			 * check soap response
			 */
			if( $response->Success == true )
			{
				$this->getLogger()->debug(__FUNCTION__.' Request Success - : GetCountriesOfDelivery');
				
				/*
				 * parse and save the data
				 */
				$this->parseResponse($response);
			}
			else
			{
				$this->getLogger()->debug(__FUNCTION__.' Request Error');
			}
		}
		catch(Exception $e)
		{
			$this->onExceptionAction($e);
		}
	}
	
	/**
	 * Parse the response
	 * 
	 * @param PlentySoapResponse_GetCountriesOfDelivery $response
	 */
	private function parseResponse($response)
	{
		var_dump($response);
		
		if(is_array($response->CountriesOfDelivery->item))
		{
			/*
			 * If more than one country of delivery
			 */
			foreach ($response->CountriesOfDelivery->item as $countryOfDelivery)
			{
				$this->saveInDatabase($countryOfDelivery);
			}
		}
		/*
		 * only one country of delivery 
		 */
		elseif (is_object($response->CountriesOfDelivery->item))
		{
			$this->saveInDatabase($response->CountriesOfDelivery->item);
		}
	}
	
	/**
	 * Save the data in the database
	 * 
	 * @param PlentySoapObject_GetCountriesOfDelivery $countryOfDelivery
	 */
	private function saveInDatabase($countryOfDelivery)
	{
		$query = 'REPLACE INTO `plenty_countries_of_delivery` '.DBUtils::buildInsert(	array(	'country_id'	=>	$countryOfDelivery->CountryID,
																								'active'		=>	$countryOfDelivery->CountryActive,
																								'country_name'	=>	$countryOfDelivery->CountryName,
																								'iso_code_2'	=>	$countryOfDelivery->CountryISO2));
		
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		
		DBQuery::getInstance()->replace($query);
	}
}

?>