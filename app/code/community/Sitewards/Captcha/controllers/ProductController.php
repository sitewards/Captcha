<?php
/**
 * Sitewards_Captcha_ProductController
 * Override Mage_Sendfriend_ProductController action sendmailAction to work with the pop up and not the normal form
 *
 * @category    Sitewards
 * @package     Sitewards_Captcha
 * @copyright   Copyright (c) 2013 Sitewards GmbH (http://www.sitewards.com/)
 */
require_once 'Mage/Sendfriend/controllers/ProductController.php';
class Sitewards_Captcha_ProductController extends Mage_Sendfriend_ProductController {

	/**
	 * Send Email Post Action and increase the counter for the captcha displaying feature
	 * Work with the javascript pop-up and not just the normal form
	 * redirects to the form if the form key is not valid or the fields validation failed.
	 * Otherwise sets body to "success" to reload the page and exit the popup
	 *
	 *
	 * @return void
	 */
	public function sendmailAction() {
		if (!$this->_validateFormKey()) {
			return $this->_redirect('*/*/send', array('_current' => true));
		}

		/* @var $oProduct Mage_Catalog_Model_Product */
		$oProduct			= $this->_initProduct();
		/* @var $oSendToFriendModel Mage_Sendfriend_Model_Sendfriend */
		$oSendToFriendModel	= $this->_initSendToFriendModel();
		$aFormData			= $this->getRequest()->getPost();

		if (!$oProduct || !$aFormData) {
			$this->_forward('noRoute');
			return;
		}

		$iCategoryId = $this->getRequest()->getParam('cat_id', null);
		if ($iCategoryId) {
			/* @var $oCategory Mage_Catalog_Model_Category */
			$oCategory = Mage::getModel('catalog/category')
				->load($iCategoryId);
			$oProduct->setCategory($oCategory);
			Mage::register('current_category', $oCategory);
		}

		$aSender = $this->getRequest()->getPost('sender');

		$oSendToFriendModel->setSender($aSender);
		$oSendToFriendModel->setRecipients($this->getRequest()->getPost('recipients'));
		$oSendToFriendModel->setProduct($oProduct);

		try {
			$mValidate = $oSendToFriendModel->validate();
			if ($mValidate === true) {
				$oSendToFriendModel->send();
				Mage::getSingleton('catalog/session')->addSuccess($this->__('The link to a friend was sent.'));

				// increase counter for captcha limits
				if (Mage::helper('captcha')->isSendFriendCaptchaRequired()) {
					$oCaptchaChecker = Mage::getModel('captcha/captchachecker');
					$oCaptchaChecker->incCounter(Sitewards_Captcha_Block_Sendfriend_Send::CAPTCHA_BLOCK_ID);
				}

				$this->_redirect('*/*/closepopup', array('id' => $oProduct->getId()));
				return;
			} else {
				if (is_array($mValidate)) {
					foreach ($mValidate as $sErrorMessage) {
						Mage::getSingleton('catalog/session')->addError($sErrorMessage);
					}
				} else {
					Mage::getSingleton('catalog/session')->addError($this->__('There were some problems with the data.'));
				}
			}
		} catch (Mage_Core_Exception $oException) {
			Mage::getSingleton('catalog/session')->addError($oException->getMessage());
		} catch (Exception $oException) {
			Mage::getSingleton('catalog/session')
				->addException($oException, $this->__('Some emails were not sent.'));
		}

		// save form data
		Mage::getSingleton('catalog/session')->setSendfriendFormData($aFormData);

		$this->_redirectError(Mage::getURL('*/*/send', array('_current' => true)));
	}

	/**
	 * Renders html page which contains javascript
	 * to close the popup and redirect to product page
	 */
	public function closepopupAction() {
		/* @var $oProduct Mage_Catalog_Model_Product */
		$oProduct = $this->_initProduct();
		$oBlock = $this->getLayout()->createBlock(
			'Mage_Core_Block_Template',
			'closepopup',
			array('template' => 'sitewards/captcha/closepopup.phtml')
		);
		$oBlock->setRedirectUrl($oProduct->getProductUrl());
		$this->getResponse()->setBody(
			$oBlock->toHtml()
		);
	}
}