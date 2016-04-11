<?php

require_once ROOT.'lib/soap/call/PlentySoapCall.abstract.php';
require_once ROOT.'lib/soap/client/MagentoSoapClient.php';

class SoapCall_UpdateCategories extends PlentySoapCall {
	
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
		if (! isset ( self::$instance ) || ! (self::$instance instanceof SoapCall_UpdateCategories)) {
			self::$instance = new SoapCall_UpdateCategories();
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

		//$this->setLastUpdate($this->lastUpdateTill);
	}
	
	private function updateMagentoParentID($magentoCategoryID ,$magentoParentID){
		$query = 'UPDATE plenty_magento_category_mapping SET magento_parent = '.$magentoParentID.' WHERE magento_id = '.$magentoCategoryID;
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		DBQuery::getInstance()->update($query);
	}
	
	private function insertIDsIntoDB($catName, $plentyID, $magentoID, $plentyParent ){
		$query = 'REPLACE INTO `plenty_magento_category_mapping` '.DBUtils::buildInsert(	
				array(	'plenty_id' => $plentyID, 'magento_id'	=>	$magentoID, 'plenty_parent'	=>	$plentyParent, 'name' =>  $catName));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		DBQuery::getInstance()->replace($query);
	}
	
	private function getMagentoID($plentyID){
		$query = 'SELECT `magento_id` FROM `plenty_magento_category_mapping` WHERE `plenty_id` = '.$plentyID;
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		$result = DBQuery::getInstance()->select($query, 'DBQueryResult');
		return $result->fetchAssoc()["magento_id"];
	}
	
	private function getMagentoParentID($plentyID){
		$query = 'SELECT `magento_id` FROM `plenty_magento_category_mapping` WHERE `plenty_id` = (SELECT `plenty_parent` FROM `plenty_magento_category_mapping` WHERE `plenty_id` = '.$plentyID.')';
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		$result = DBQuery::getInstance()->select($query, 'DBQueryResult');
		return $result->fetchAssoc()["magento_id"];
	}
	
	private function checkLastUpdate(){
		$query = 'SELECT `last_update` FROM `plenty_last_category_update`'.DBUtils::buildWhere( array( 'id' => 1));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		$result = DBQuery::getInstance()->select($query, 'DBQueryResult');
		return $result->fetchAssoc()["last_update"];
	}
	
	private function setLastUpdate($lastUpdateTill){
		$query = 'REPLACE INTO `plenty_last_itemsImages_update` '.DBUtils::buildInsert(	array(	'id' => 1, 'last_update'	=>	$this->lastUpdateTo));
		$this->getLogger()->debug(__FUNCTION__.' '.$query);
		DBQuery::getInstance()->replace($query);
	}
}

?>