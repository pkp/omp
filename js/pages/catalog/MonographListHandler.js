/**
 * @file js/pages/catalog/MonographListHandler.js
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographListHandler
 * @ingroup js_pages_catalog
 *
 * @brief Handler for monograph list.
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
	$.pkp.pages.catalog.MonographListHandler =
			function($monographsContainer, options) {

		this.parent($monographsContainer, options);

		// Initialize sortable, but disabled until "organize" selected.
		$monographsContainer.find('#monographListContainer ul')
				.sortable({disabled: true, items: 'li:not(.not_sortable)'});

		// Attach the view type handlers, if links exist
		$monographsContainer.find('.grid_view').click(
				this.callbackWrapper(this.useGridView));
		$monographsContainer.find('.list_view').click(
				this.callbackWrapper(this.useListView));

		// Attach the organize button handler, if button exists
		$monographsContainer.find('.organize').click(
				this.callbackWrapper(this.organizeButtonHandler_));

		// Start in grid view
		this.useGridView();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.pages.catalog.MonographListHandler,
			$.pkp.classes.Handler);


	//
	// Private Properties
	//
	/**
	 * Whether or not we're currently in Organize mode
	 * @private
	 * @type {boolean}
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.inOrganizeMode_ = false;


	/**
	 * Whether or not we're currently in Grid mode
	 * @private
	 * @type {boolean?}
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.inGridMode_ = null;


	//
	// Public Methods
	//
	/**
	 * Switch to List View mode.
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.useListView =
			function() {

		var $htmlElement = $(this.getHtmlElement());
		$htmlElement.find('.pkp_catalog_monographList')
			.removeClass('grid_view')
			.addClass('list_view');

		// Control enabled/disabled state of buttons
		var $actionsContainer = $htmlElement.find('.submission_actions');
		$actionsContainer.find('.list_view').addClass('ui-state-active');
		$actionsContainer.find('.grid_view').removeClass('ui-state-active');

		this.inGridMode_ = false;

		// In case called as event handler, stop further processing
		return false;
	};


	/**
	 * Switch to Grid View mode.
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.useGridView =
			function() {

		var $htmlElement = $(this.getHtmlElement());
		$htmlElement.find('.pkp_catalog_monographList')
			.removeClass('list_view')
			.addClass('grid_view');

		// Control enabled/disabled state of buttons
		var $actionsContainer = $htmlElement.find('.submission_actions');
		$actionsContainer.find('.grid_view').addClass('ui-state-active');
		$actionsContainer.find('.list_view').removeClass('ui-state-active');

		this.inGridMode_ = true;

		// In case called as event handler, stop further processing
		return false;
	};


	//
	// Private Methods
	//
	/**
	 * Callback that will be activated when "organize" is clicked
	 *
	 * @private
	 *
	 * @return {boolean} Always returns false.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.organizeButtonHandler_ =
			function() {

		// Toggle the "organize" flag.
		this.inOrganizeMode_ = !this.inOrganizeMode_;

		var $htmlElement = $(this.getHtmlElement());

		// Find the button elements
		var $actionsContainer = $htmlElement.find('.submission_actions');
		var $gridViewButton = $actionsContainer.find('.grid_view');
		var $listViewButton = $actionsContainer.find('.list_view');
		var $organizeButton = $actionsContainer.find('.organize');

		// Find the monograph list
		var $monographList = $htmlElement.find('#monographListContainer ul');

		// Find the organize links
		var $organizeLinks = $monographList.find('.pkp_catalog_organizeTools');

		if (this.inOrganizeMode_) {
			// We've just entered "Organize" mode.
			$gridViewButton.addClass('ui-state-disabled');
			$listViewButton.addClass('ui-state-disabled');
			$organizeButton.addClass('ui-state-active');
			$monographList.sortable('option', 'disabled', false);
			$organizeLinks.removeClass('pkp_helpers_invisible');
		} else {
			// We've just left "Organize" mode.
			$organizeButton.removeClass('ui-state-active');
			$listViewButton.removeClass('ui-state-disabled');
			$gridViewButton.removeClass('ui-state-disabled');
			$monographList.sortable('option', 'disabled', true);
			$organizeLinks.addClass('pkp_helpers_invisible');
		}

		// Stop further event processing
		return false;
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
