<?php

class Request_AddCustomers {

	public function getRequest()
	{
		$oPlentySoapRequest_AddCustomers = new PlentySoapRequest_SetCustomers();
		
		$oPlentySoapObject_Customer							=	new PlentySoapObject_Customer();
		$oPlentySoapObject_Customer->AdditionalName			=	"Maximilians";
		$oPlentySoapObject_Customer->ArchiveNumber			=	null;
		$oPlentySoapObject_Customer->City					=	"Musterstadtsss";
		$oPlentySoapObject_Customer->Company				=	null;
		$oPlentySoapObject_Customer->ContactPerson			=	null;
		$oPlentySoapObject_Customer->CustomerClass			=	0;
		$oPlentySoapObject_Customer->CustomerID				= 	1231234;
		$oPlentySoapObject_Customer->CountryID				=	1;
		$oPlentySoapObject_Customer->CustomerNumber			=	"SOAP_".rand(1000, 9999);
		$oPlentySoapObject_Customer->CustomerRating			=	null;
		$oPlentySoapObject_Customer->CustomerSince 	       	=	time();
		$oPlentySoapObject_Customer->DateOfBirth			=	1021542154;
		$oPlentySoapObject_Customer->DebitorAccount         =	null;
		$oPlentySoapObject_Customer->EbayName               =	"mmustermann";
		$oPlentySoapObject_Customer->ExternalCustomerID		=	"External SOAP_".rand(1000, 9999);
		$oPlentySoapObject_Customer->Fax					=	"0123456789";
		$oPlentySoapObject_Customer->FirstName				=	"Max";
		$oPlentySoapObject_Customer->FormOfAddress			=	0;
		$oPlentySoapObject_Customer->HouseNo				=	123;
		$oPlentySoapObject_Customer->Language				=	"de";
		$oPlentySoapObject_Customer->Mobile					=	"0177/74110815";
		$oPlentySoapObject_Customer->Street					=	"Musterstrasse";
		$oPlentySoapObject_Customer->ResponsibleID			=	0;
		$oPlentySoapObject_Customer->Surname				=	"Mustermann";
		$oPlentySoapObject_Customer->Telephone				=	"74110815";
		$oPlentySoapObject_Customer->ZIP					=	"12345";
		$oPlentySoapObject_Customer->Email					=	"Max@Mustermann.de";

		$oPlentySoapRequest_AddCustomers->Customers[] = $oPlentySoapObject_Customer;
		
		return $oPlentySoapRequest_AddCustomers;
	}

}

?>