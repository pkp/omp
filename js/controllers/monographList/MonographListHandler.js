/**
 * @defgroup js_controllers_monographList
 */
// Create the controllers_monographList namespace.
$.pkp.controllers.monographList = $.pkp.controllers.monographList || {};

/**
 * @file js/controllers/monographList/MonographListHandler
 * @class MonographListHandler
 * @ingroup js_controllers_monographList
 *
 * @brief Base handler for monograph list.
 *
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $monographsContainer The HTML element encapsulating
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
	 * @return {jQuery}
	 */
	$.pkp.controllers.monographList.MonographListHandler.prototype.getMonographs =
			function() {
		throw Error('Method must be implemented by subclasses.');
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

		var $monographs = this.getMonographs();

		// Get the max number of monographs that fit in a row.
		var monographWidth = $monographs.first().width() + 10;
		var containerWidth = this.getHtmlElement().width();
		var maxInRow = Math.floor(containerWidth / monographWidth);

		// Iterate over our monographs in groups, normalizing the
		// element detail heights.
		for (var $i = 0; $i < $monographs.size(); $i += maxInRow) {
			$monographs.slice($i, $i + maxInRow).equalizeElementHeights();
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
