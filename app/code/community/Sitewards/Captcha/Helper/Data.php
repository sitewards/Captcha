<?php
/**
 * Sitewards_Captcha_Helper_Data
 * - check if captcha is required
 *
 * @category    Sitewards
 * @package     Sitewards_Captcha
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_Captcha_Helper_Data extends Mage_Captcha_Helper_Data {

	/**
	 * Returns if captcha is set to display for send to friend form
	 *
	 * @return bool
	 */
	public function isSendFriendCaptchaRequired() {
		$oCaptchaModel = Mage::helper('captcha')->getCaptcha(Sitewards_Captcha_Model_Sendfriend::SEND_FRIEND_FORM_ID);
		if (!$oCaptchaModel->isRequired()) {
			return false;
		}
		return true;
	}
}