<?php
/**
 * Sitewards_Captcha_Model_CaptchaChecker
 * - check if captcha is required
 * - validate captcha
 * - increment successful form submit
 *
 * @category    Sitewards
 * @package     Sitewards_Captcha
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
class Sitewards_Captcha_Model_CaptchaChecker extends Mage_Core_Model_Abstract {

	/**
	 * Customer session
	 *
	 * @var Mage_Core_Model_Abstract
	 */
	private $oCustomerSession = null;


	public function __construct(){
		$this->oCustomerSession = Mage::getSingleton('customer/session');
	}

	/**
	 * Checks if the captcha should be displayed  in the form
	 *
	 *
	 * @param string $sIdBlock unique string to get the right counter
	 * @param integer $iMaxPerTimeframe max. number of sending the form before captcha should be displayed
	 * @param integer $iTimeframeSeconds timeframe within which the counter should get the max value to make the captcha displayed
	 * @return boolean
	 */
	public function isRequired($sIdBlock, $iMaxPerTimeframe, $iTimeframeSeconds){
		$iTimesInTimeframe = 0;
		$aCounter = $this->getCounter($sIdBlock);
		foreach ($aCounter as $iTimestamp){
			if ($iTimestamp > time() - $iTimeframeSeconds){
				$iTimesInTimeframe++;
			}
		}
		if ($iTimesInTimeframe >= $iMaxPerTimeframe){
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Gets the counter for a particular block based on the block id
	 * Returns the array of the form
	 * 	Array(
	 * 		'key' => 'timestamp_of_the_counter_inc',
	 * 		...
	 * 	)
	 *
	 * @param string $sIdBlock
	 * @return array
	 */
	private function getCounter($sIdBlock){
		$aCaptchaCheckerSession = $this->oCustomerSession->getData('captchachecker');
		if (isset($aCaptchaCheckerSession[$sIdBlock])){
			return $aCaptchaCheckerSession[$sIdBlock];
		} else {
			return array();
		}
	}

	/**
	 * Sets the counter
	 * Overwrites the counter for a certain block id with a new counter array
	 *
	 * @param string $sIdBlock
	 * @param array $aCounter
	 * @return Sitewards_Captcha_Model_CaptchaChecker
	 */
	private function setCounter($sIdBlock, $aCounter){
		$aCaptchaCheckerSession = $this->oCustomerSession->getData('captchachecker');
		$aCaptchaCheckerSession[$sIdBlock] = $aCounter;
		$this->oCustomerSession->setData('captchachecker', $aCaptchaCheckerSession);
		return $this;
	}

	/**
	 * Increments the counter with a certain block id
	 *
	 * @param string $sIdBlock
	 * @return Sitewards_Captcha_Model_CaptchaChecker
	 */
	public function incCounter($sIdBlock){
		$aCounter = $this->getCounter($sIdBlock);
		$aCounter[] = time();
		$this->setCounter($sIdBlock, $aCounter);
		return $this;
	}

	/**
	 * Checks if the captcha provided in the post form is valid
	 *
	 * @return boolean
	 */
	public function isValidCaptcha(){
		$aCaptchaFormValues = Mage::app()->getRequest()->getParam('captcha');
		$bValid = true;
		// for the case that we have more than one captcha
		// in the form, every captcha should be validated
		if (is_array($aCaptchaFormValues)){
			foreach ($aCaptchaFormValues as $sCaptchaName => $sCaptchaValue){
				$oCaptchaModel = Mage::helper('captcha')->getCaptcha($sCaptchaName);
				if (!$oCaptchaModel->isCorrect($sCaptchaValue)){
					$bValid = false;
				}
			}
		}
		return $bValid;
	}
}