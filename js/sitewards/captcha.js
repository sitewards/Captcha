/**
 * Show prototype popup window
 *
 * @param string sUrl
 */
function showPopup(sUrl) {
	oPopup = new Window({
		id:'popup_window',
		className: 'magento',
		url: sUrl,
		width: 820,
		height: 600,
		minimizable: false,
		maximizable: false,
		showEffectOptions: {
			duration: 0.4
		},
		hideEffectOptions:{
			duration: 0.4
		},
		destroyOnClose: true
	});
	oPopup.setZIndex(100);
	oPopup.showCenter(true);
}

/**
 * Close popup window
 */
function closePopup() {
	Windows.close('popup_window');
}

document.observe("dom:loaded", function() {
	$$('p.email-friend a')[0].observe('click', function(oEvent) {
		showPopup($(this).readAttribute('href'));
		Event.stop(oEvent);
	});
});


