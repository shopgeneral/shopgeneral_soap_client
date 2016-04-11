<?php

require_once ROOT.'lib/soap/call/PlentySoapCall.abstract.php';
require_once ROOT.'lib/soap/client/MagentoSoapClient.php';

class SoapCall_DeletionRun extends PlentySoapCall {
	
	private static $instance = null;
	
	private $lastUpdateFrom = null;
	private $lastUpdateTo = null;
	
	private static $magentoClient = null;
	private static $magentoSession = null;
	
	public function __construct() {
		parent::__construct ( __CLASS__ );
		$this->initMagentoController ();
	}
	
	public static function getInstance() {
		if (! isset ( self::$instance ) || ! (self::$instance instanceof SoapCall_DeletionRun)) {
			self::$instance = new SoapCall_DeletionRun();
		}
		return self::$instance;
	}
	
	private function initMagentoController() {
		$magentoSoapClient = MagentoSoapClient::getInstance ();
		$magentoSoapClient->doAuthentification ();
		self::$magentoSession = $magentoSoapClient->getSession ();
		self::$magentoClient = $magentoSoapClient->getSoapClient ();
	}
	
	/*
	 * (non-PHPdoc) @see PlentySoapCall::execute()
	 */
	public function execute() {
		$this->lastUpdateFrom = $this->checkLastUpdate();
		$this->lastUpdateTo = time();
		
		$oPlentySoapRequest_GetDeleteLog = new PlentySoapRequest_GetDeleteLog();
		$oPlentySoapRequest_GetDeleteLog->TimestampFrom = $this->lastUpdateFrom;
		$oPlentySoapRequest_GetDeleteLog->TimestampTo = $this->lastUpdateTo;
		
		$response = $this->getPlentySoap()->GetDeleteLog($oPlentySoapRequest_GetDeleteLog);
		
		var_dump($response);
		
		exit;
		$this->setLastUpdate($this->lastUpdateTo);
		exit;
	}
	
	private function checkLastUpdate(){
		$query = 'SELECT `last_update` FROM `plenty_last_deletion_update`'.DBUtils::buildWhere( array( 'id' => 1));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		$result = DBQuery::getInstance()->select($query, 'DBQueryResult');
		return $result->fetchAssoc()["last_update"];
	}
	
	private function setLastUpdate($lastUpdateTill){
		$query = 'REPLACE INTO `plenty_last_deletion_update` '.DBUtils::buildInsert(	array(	'id' => 1, 'last_update'	=>	$this->lastUpdateTo));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		DBQuery::getInstance()->replace($query);
	}
}

?>