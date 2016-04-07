<?php
class SoapCall_UpdateItemsImages extends PlentySoapCall {
	
	
	public function __construct() {
		parent::__construct ( __CLASS__ );
		$this->initMagentoController ();
	}
	
	
	public static function getInstance() {
		if (! isset ( self::$instance ) || ! (self::$instance instanceof SoapCall_UpdateItems)) {
			self::$instance = new SoapCall_UpdateItems ();
		}
		
		return self::$instance;
	}
	
	
	private function initMagentoController() {
		$magentoSoapClient = MagentoSoapClient::getInstance ();
		$magentoSoapClient->doAuthentification ();
		self::$magentoSession = $magentoSoapClient->getSession ();
		self::$magentoClient = $magentoSoapClient->getSoapClient ();
	}
	
	
	private function getImages() {
		$oPlentySoapRequest_GetItemsImages = new PlentySoapRequest_GetItemsImages ();
		$oPlentySoapRequest_GetItemsImages->LastUpdateFrom = $this->lastUpdateFrom;
		$oPlentySoapRequest_GetItemsImages->LastUpdateTo = time ();
		$response = $this->getPlentySoap ()->GetItemsImages ( $oPlentySoapRequest_GetItemsImages );
		return $response;
	}
	
	
	public function getImgUrlToBase64($imgUrl) {
		$image = file_get_contents ( $imgUrl );
		if ($image !== false) {
			return base64_encode ( $image );
		}
	}
	
	
	/*
	 * (non-PHPdoc) @see PlentySoapCall::execute()
	 */
	public function execute() {
		$imageItem = $this->getImages ();
		
		$imgData = $this->getImgUrlToBase64 ( $imageItem->ItemsImages->item [0]->ImageURL );
		
		$file = array (
				'content' => $imgData,
				'mime' => 'image/jpeg' 
		);
		
		$result = self::$magentoClient->call ( self::$magentoSession, 'catalog_product_attribute_media.create', array (
				$itemBase->ItemNo,
				array (
						'file' => $file,
						'label' => 'test',
						'position' => '100',
						'types' => array (
								'thumbnail' 
						),
						'exclude' => 0 
				) 
		) );
	}
}

?>