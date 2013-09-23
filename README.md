Sitewards Captcha
==========================

The Sitewards Captcha Extension provides functionality to add captcha to "Email to a Friend" form.

Features
------------------
* adds captcha for unregistered customer to "Email to a Friend" form.
* you can configure "Number of emails without captcha" and "CAPTCHA Timeout (seconds)"
* opens "Email to a Friend" form in popup window.
* adds Product Name field to "Email to a Friend" form.
* by default message field is filled by product description

File list
------------------
* app\code\community\Sitewards\Captcha\Sendfriend\Send.php
	* update the Mage_Sendfriend_Block_Send to allow customer edit product description and name
	* check if captcha required
* app\code\community\Sitewards\Captcha\controllers\ProductController.php
	* Override Mage_Sendfriend_ProductController action sendmailAction to work with the pop up and not the normal form
* app\code\community\Sitewards\Captcha\etc\config.xml
	* Set-up model declaration
	* Set-up helper declaration
	* Set-up layout configuration
	* Set-up translations
		* Frontend
	* Rewrite Mage_Sendfriend router
	* Add product_sendtofriend_form_captcha to the list of captcha forms
* app\code\community\Sitewards\Captcha\etc\system.xml
	* Assign admin config fields to sections
		* Customers->Customer Configuration->Captcha
* app\code\community\Sitewards\Captcha\Helper\Data.php
	* check if captcha is required
* app\code\community\Sitewards\Captcha\Model\CaptchaChecker.php
	* check if captcha is required
	* validate captcha
	* increments successful form submit
* app\code\community\Sitewards\Captcha\Model\Sendfriend.php
	* override Mage_Sendfriend_Model_Sendfriend
	* set the correct product url in the email sent
	* sent customer modified product name
	* validate captcha
* app\design\frontend\base\default\layout\sitewards\captcha.xml
	* Set "Email to a Friend" template
	* Add popup java scripts to product page
* app\design\frontend\base\default\template\sitewards\captcha\sendfriend\send.phtml
	* Added Product Name field
	* Massage field by default is filled by product description
	* If required display captcha
* app\design\frontend\base\default\template\sitewards\captcha\closepopup.phtml
	* Close popup window
	* Redirect to product page
* app\etc\modules\Sitewards_Captcha.xml
	* Activate module
	* Specify community code pool
	* Set-up dependencies
		* Mage_Catalog
		* Mage_Sendfriend
* app\locale\de_DE\Sitewards_Captcha.csv
	* Translation
* js\sitewards\captcha.js
	* Show prototype popup window
	* Close popup window
