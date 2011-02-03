/**
 * @defgroup js_controllers_informationCenter
 */
// Create the modal namespace.
jQuery.pkp.controllers.informationCenter =
			jQuery.pkp.controllers.informationCenter || { };

/**
 * @file js/controllers/informationCenter/InformationCenterHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterHandler
 * @ingroup js_controllers_informationCenter
 *
 * @brief Information center handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.TabHandler
	 *
	 * @param {jQuery} $modal A wrapped HTML element that
	 *  represents the tabbed interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.informationCenter.InformationCenterHandler =
			function($modal, options) {
		this.parent($modal, options);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.informationCenter.InformationCenterHandler,
			$.pkp.controllers.TabHandler
	);

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
