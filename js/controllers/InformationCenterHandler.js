/**
 * @defgroup js_controllers
 */
// Create the modal namespace.
jQuery.pkp.controllers = jQuery.pkp.controllers || { };

/**
 * @file js/controllers/InformationCenterHandler.js
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterHandler
 * @ingroup js_controllers
 *
 * @brief Information center handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.TabbedHandler
	 *
	 * @param {jQuery} $modal A wrapped HTML element that
	 *  represents the tabbed modal.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.InformationCenterHandler = function($modal, options) {
		this.parent($modal, options);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.InformationCenterHandler,
			$.pkp.controllers.TabbedHandler
	);

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
