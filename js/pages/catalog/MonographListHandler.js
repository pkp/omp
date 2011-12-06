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

		// Attach the view type handlers, if links exist
		$monographsContainer.find('.grid_view').click(
				this.callbackWrapper(this.useGridView));
		$monographsContainer.find('.list_view').click(
				this.callbackWrapper(this.useListView));

		// Attach the organize button handler, if button exists
		$monographsContainer.find('.organize').click(
				this.callbackWrapper(this.organizeButtonHandler_));

		// React to "monograph list changed" events.
		this.bind('monographListChanged',
				this.monographListChangedHandler_);

		// Start in grid view
		this.useGridView();

		// Set up the sortables.
		this.trigger('monographListChanged');
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
		var $monographList = $htmlElement.find('ul.pkp_catalog_monographList');

		// Find the organize links
		var $organizeLinks = $monographList.find('.pkp_catalog_organizeTools');

		if (this.inOrganizeMode_) {
			// We've just entered "Organize" mode.
			$gridViewButton.addClass('ui-state-disabled');
			$listViewButton.addClass('ui-state-disabled');
			$organizeButton.addClass('ui-state-active');
			$organizeLinks.removeClass('pkp_helpers_invisible');
		} else {
			// We've just left "Organize" mode.
			$organizeButton.removeClass('ui-state-active');
			$listViewButton.removeClass('ui-state-disabled');
			$gridViewButton.removeClass('ui-state-disabled');
			$organizeLinks.addClass('pkp_helpers_invisible');
		}
		$monographList.children().trigger('changeDragMode', this.inOrganizeMode_);
		// Update the enabled/disabled state of the sortable list
		this.trigger('monographListChanged');

		// Stop further event processing
		return false;
	};


	/**
	 * Handle the "monograph list changed" event to reset the sortable
	 * JQueryUI initialization.
	 *
	 * @private
	 *
	 * @param {$.pkp.controllers.handler.Handler} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.catalog.MonographListHandler.
			prototype.monographListChangedHandler_ =
			function(callingHandler, event) {

		var $listContainer = this.getHtmlElement()
				.find('ul.pkp_catalog_monographList');

		// In case the list has changed sort order, re-sort it.
		$listContainer.find('li').sortElements(function(aNode, bNode) {
			var a = $.pkp.classes.Handler.getHandler($(aNode));
			var b = $.pkp.classes.Handler.getHandler($(bNode));

			// One is featured and the other is not
			if (a.getFeatured() && !b.getFeatured()) {
				return -1;
			}
			if (b.getFeatured() && !a.getFeatured()) {
				return 1;
			}

			// Both are featured: use sequence.
			if (a.getFeatured() && b.getFeatured()) {
				return a.getSeq() - b.getSeq();
			}

			// Neither are featured: use publication date.
			return b.getDatePublished() - a.getDatePublished();
		});

		// Initialize sortable, but disabled unless "organize" selected.
		this.getHtmlElement().sortable('destroy');
		this.getHtmlElement().sortable({
			disabled: !this.inOrganizeMode_,
			items: 'li.pkp_catalog_monograph:not(.not_sortable)',
			update: this.callbackWrapper(this.sortUpdateHandler_)});

		// No further processing
		return false;
	};


	/**
	 * Handle DOM change events when sortables are rearranged.
	 *
	 * @private
	 *
	 * @param {$.pkp.controllers.handler.Handler} callingHandler The handler
	 *  that triggered the event.
	 * @param {Event} event The event.
	 * @param {Object} ui The UI element that has changed.
	 * @return {boolean} The event handling chain status.
	 */
	$.pkp.pages.catalog.MonographListHandler.prototype.sortUpdateHandler_ = function(callingHandler, event, ui) {
		// Figure out where we are in the DOM and choose a new seq num
		var $monographElement = ui.item;
		var $prevElement = $monographElement.prev();
		var $nextElement = $monographElement.next();
		var newSequence;
		if ($prevElement.length) {
			// Move to the previous nodes's sequence plus one.
			var prevHandler = $.pkp.classes.Handler.getHandler($prevElement);
			newSequence = prevHandler.getSeq() + 1;
		} else if ($nextElement.length) {
			// Move to the next node's sequence minus one.
			var nextHandler = $.pkp.classes.Handler.getHandler($nextElement);
			newSequence = nextHandler.getSeq() - 1;
		} else {
			// It's a one-element list and sorting is irrelevant.
			return false;
		}

		// Tell the monograph what the new sequence number is.
		$monographElement.trigger('setSequence', newSequence);

		// End processing here.
		return false;
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
