<?php
/**
 * Sitewards_Captcha_Model_Sendfriend
 * - override Mage_Sendfriend_Model_Sendfriend
 * - set the correct product url in the email sent
 * - send customer modified product name
 * - validate captcha
 *
 * @category    Sitewards
 * @package     Sitewards_Captcha
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_Captcha_Model_Sendfriend extends Mage_Sendfriend_Model_Sendfriend {

	const SEND_FRIEND_FORM_ID = 'product_sendtofriend_form_captcha';

	/**
	 * Update the send email function to set the correct product url in the email a friend
	 *
	 * @see Mage_Sendfriend_Model_Sendfriend::send()
	 */
	public function send() {

		if ($this->isExceedLimit()){
			Mage::throwException(Mage::helper('sendfriend')->__('You have exceeded limit of %d sends in an hour', $this->getMaxSendsToFriend()));
		}

		/* @var $oTranslate Mage_Core_Model_Translate */
		$oTranslate = Mage::getSingleton('core/translate');
		$oTranslate->setTranslateInline(false);

		/* @var $oMailTemplate Mage_Core_Model_Email_Template */
		$oMailTemplate = Mage::getModel('core/email_template');

		$sMessage = nl2br(htmlspecialchars($this->getSender()->getMessage()));
		$sPName = nl2br(htmlspecialchars($this->getSender()->getProductName()));

		$sSender  = array(
			'name'  => $this->_getHelper()->htmlEscape($this->getSender()->getName()),
			'email' => $this->_getHelper()->htmlEscape($this->getSender()->getEmail())
		);
		$aShopSender = array(
			'name'=> Mage::getStoreConfig('trans_email/ident_general/name'),
			'email'=> Mage::getStoreConfig('trans_email/ident_general/email')
		);

		$oMailTemplate->setDesignConfig(array(
			'area'  => 'frontend',
			'store' => Mage::app()->getStore()->getId()
		));

		foreach ($this->getRecipients()->getEmails() as $iEmailKey => $sEmail) {
			$sName = $this->getRecipients()->getNames($iEmailKey);
			$oMailTemplate->sendTransactional(
				$this->getTemplate(),
				$aShopSender,
				$sEmail,
				$sName,
				array(
					'name'          => $sName,
					'email'         => $sEmail,
					'product_name'  => $sPName,
					'telephone'     => Mage::getStoreConfig('general/store_information/phone'),
					'product_url'   => Mage::app()->getStore()->getBaseUrl().$this->getProduct()->getRequestPath().$this->getProduct()->getUrlPath(),
					'message'       => $sMessage,
					'sender_name'   => $sSender['name'],
					'sender_email'  => $sSender['email'],
					'product_image' => Mage::helper('catalog/image')->init($this->getProduct(),'small_image')->resize(75),
				)
			);
		}

		$oTranslate->setTranslateInline(true);
		$this->_incrementSentCount();

		return $this;
	}

	/**
	 * Additionally checks if the captcha is required for the form and if so, if it is valid
	 *
	 * @see Mage_Sendfriend_Model_Sendfriend::validate()
	 */
	public function validate()
	{
		$aErrors = array();

		$sName = $this->getSender()->getName();
		if (empty($sName)) {
			$aErrors[] = Mage::helper('sendfriend')->__('The sender name cannot be empty.');
		}

		$sEmail = $this->getSender()->getEmail();
		if (empty($sEmail) OR !Zend_Validate::is($sEmail, 'EmailAddress')) {
			$aErrors[] = Mage::helper('sendfriend')->__('Invalid sender email.');
		}

		if (!$this->getRecipients()->getEmails()) {
			$aErrors[] = Mage::helper('sendfriend')->__('At least one recipient must be specified.');
		}

		// validate recipients email addresses
		foreach ($this->getRecipients()->getEmails() as $sEmail) {
			if (!Zend_Validate::is($sEmail, 'EmailAddress')) {
				$aErrors[] = Mage::helper('sendfriend')->__('An invalid email address for recipient was entered.');
				break;
			}
		}

		// validate recipients name
		foreach ($this->getRecipients()->getNames() as $sName) {
			if (empty($sName)) {
				$aErrors[] = Mage::helper('sendfriend')->__('At least one recipient must be specified.');
				break;
			}
		}

		$sProductName = $this->getProduct()->getName();
		if(empty($sProductName)) {
			$aErrors[] = Mage::helper('sendfriend')->__('The product name cannot be empty.');
		}

		$iMaxRecipients = $this->getMaxRecipients();
		if (count($this->getRecipients()->getEmails()) > $iMaxRecipients) {
			$aErrors[] = Mage::helper('sendfriend')->__('No more than %d emails can be sent at a time.', $this->getMaxRecipients());
		}

		$oCaptchaChecker = Mage::getModel('captcha/captchachecker');
		$bCaptchaRequired = $oCaptchaChecker->isRequired(
			Sitewards_Captcha_Block_Sendfriend_Send::CAPTCHA_BLOCK_ID,
			Sitewards_Captcha_Block_Sendfriend_Send::XML_PATH_MAX_TILL_CAPTCHA_TIMES,
			Sitewards_Captcha_Block_Sendfriend_Send::XML_PATH_MAX_TILL_CAPTCHA_TIMEFRAME_SECONDS
		);

		if ($bCaptchaRequired){
			if (!$oCaptchaChecker->isValidCaptcha()){
				$sErrorMessage = Mage::helper('captcha')->__('Incorrect Captcha Code');
				if (!is_array($aErrors)){
					$aErrors = array( $sErrorMessage );
				} else {
					$aErrors[] = $sErrorMessage;
				}
			}
		}

		if (empty($aErrors)) {
			return true;
		}

		return $aErrors;
	}

}