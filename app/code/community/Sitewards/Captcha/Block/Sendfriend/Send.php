<?php
/**
 * Class Sitewards_Captcha_Block_Sendfriend_Send
 * - update the Mage_Sendfriend_Block_Send to allow customer edit product description and name
 * - check if captcha required
 *
 * @category    Sitewards
 * @package     Sitewards_Captcha
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_Captcha_Block_Sendfriend_Send extends Mage_Sendfriend_Block_Send {

	/**
	 * Sendfriend product
	 *
	 * @var object Mage_Catalog_Model_Product
	 */
	protected $_oProduct = null;

	/**
	 * Block id
	 *
	 * @type string
	 */
	const CAPTCHA_BLOCK_ID = 'sendfriend';

	/**
	 * Number of emails without captcha config path
	 *
	 * @type string
	 */
	const XML_PATH_MAX_TILL_CAPTCHA_TIMES = 'customer/captcha/max_till_captcha_times';

	/**
	 * Captcha Timeout config path
	 *
	 * @type string
	 */
	const XML_PATH_MAX_TILL_CAPTCHA_TIMEFRAME_SECONDS = 'customer/captcha/max_till_captcha_timeframe_seconds';

	/**
	 * Load the product object based on the requested Id
	 */
	public function _prepareLayout() {
		$iProductId = $this->getRequest()->getParam('id', null);
		$this->_oProduct = Mage::getModel('catalog/product')->load($iProductId);
	}

	/**
	 * Load the message getData when it has been set,
	 * Else load the product description.
	 *
	 * @return string
	 */
	public function getMessage() {
		$aFormData = $this->getFormData()->getData();
		if(!empty($aFormData)) {
			return $aFormData['sender']['message'];
		} else {
			return $this->getProductDescription();
		}
	}

	/**
	 * Retrieve Current Product Description
	 *
	 * @return string
	 */
	public function getProductDescription() {
		return $this->_oProduct->getDescription();
	}

	/**
	 * Retrieve Current Product Name
	 *
	 * @return string
	 */
	public function getProductName() {
		return $this->_oProduct->getName();
	}

	/**
	 * Check whether captcha should be displayed in the form
	 *
	 * @return boolean
	 */
	public function isCaptchaRequired(){
		if (!Mage::helper('captcha')->isSendFriendCaptchaRequired()) {
			return false;
		}
		$oCaptchaChecker = Mage::getModel('captcha/captchachecker');
		return $oCaptchaChecker->isRequired(
			self::CAPTCHA_BLOCK_ID,
			Mage::getStoreConfig(self::XML_PATH_MAX_TILL_CAPTCHA_TIMES),
			Mage::getStoreConfig(self::XML_PATH_MAX_TILL_CAPTCHA_TIMEFRAME_SECONDS)
		);
	}
}