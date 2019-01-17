/**
 * @defgroup js_controllers_monographList
 */
/**
 * @file js/controllers/monographList/MonographListHandler
 * @class MonographListHandler
 * @ingroup js_controllers_monographList
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Base handler for monograph list.
 *
 */
(function($) {

	/** @type {Object} */
	$.pkp.controllers.monographList = $.pkp.controllers.monographList || {};



	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQueryObject} $monographsContainer The HTML element encapsulating
	 *  the monograph list div.
	 * @param {Object} options Handler options.
	 */
	$.pkp.controllers.monographList.MonographListHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.monographList.MonographListHandler,
			$.pkp.classes.Handler);


	//
	// Template methods.
	//
	/**
	 * Return all monographs inside this list widget.
	 */
	$.pkp.controllers.monographList.MonographListHandler.prototype.getMonographs =
			function() {
		throw new Error('Method must be implemented by subclasses.');
	};


	//
	// Protected methods.
	//
	/**
	 * Format the list in a way that all row items
	 * have the same height.
	 * @protected
	 */
	$.pkp.controllers.monographList.MonographListHandler.prototype.formatList =
			function() {

		var $monographs = this.getMonographs(),
				// Get the max number of monographs that fit in a row.
				monographWidth = $monographs.first().width() + 10,
				containerWidth = this.getHtmlElement().width(),
				maxInRow = Math.floor(containerWidth / monographWidth),
				i;

		// Iterate over our monographs in groups, normalizing the
		// element detail heights.
		for (i = 0; i < $monographs.size(); i += maxInRow) {
			$monographs.slice(i, i + maxInRow).equalizeElementHeights();
		}
	};


/** @param {jQuery} $ jQuery closure. */
}(jQuery));
