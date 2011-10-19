<?php

/**
 * @file controllers/grid/settings/series/form/SeriesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesForm
 * @ingroup controllers_grid_settings_series_form
 *
 * @brief Form for adding/edditing a series
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class SeriesForm extends Form {
	/** the id for the series being edited **/
	var $_seriesId;

	/**
	 * Constructor.
	 */
	function SeriesForm($seriesId = null) {
		$this->setSeriesId($seriesId);
		parent::Form('controllers/grid/settings/series/form/seriesForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.setup.form.series.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$press =& $request->getPress();

		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		$categoryIterator =& $categoryDao->getByPressId($press->getId());

		$categories = array(0 => __('common.none'));
		while ($category =& $categoryIterator->next()) {
			$categories[$category->getId()] = $category->getLocalizedTitle();
			unset($category);
		}

		$seriesId = $this->getSeriesId();
		if ($seriesId) {
			$series =& $seriesDao->getById($seriesId, $press->getId());
		}

		if (isset($series) ) {
			$this->_data = array(
				'seriesId' => $seriesId,
				'title' => $series->getTitle(null),
				'categories' => $categories,
				'categoryId' => $series->getCategoryId(),
				'description' => $series->getDescription(null),
				'featured' => $series->getFeatured()
			);
		} else {
			$this->_data = array(
				'categories' => $categories
			);
		}
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('seriesId', $this->getSeriesId());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('seriesId', 'title', 'categoryId', 'description', 'featured', 'seriesEditors'));
	}

	/**
	 * Save series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$press =& $request->getPress();

		// Get or create the series object
		if ($this->getSeriesId()) {
			$series =& $seriesDao->getById($this->getSeriesId(), $press->getId());
		} else {
			import('classes.press.Series');
			$series = new Series();
			$series->setPressId($press->getId());
		}

		// Populate/update the series object from the form
		$series->setCategoryId($this->getData('categoryId'));
		$series->setTitle($this->getData('title'), null); // Localized
		$series->setDescription($this->getData('description'), null); // Localized
		$series->setFeatured($this->getData('featured'));

		// Insert or update the series in the DB
		if ($this->getSeriesId()) {
			$seriesDao->updateObject($series);
		} else {
			$this->setSeriesId($seriesDao->insertObject($series));
		}

		// Save the series editor associations. (See insert/deleteEntry.)
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		ListBuilderHandler::unpack($request, $request->getUserVar('seriesEditors'));

		return true;
	}

	/**
	 * Get the series ID for this series.
	 * @return int
	 */
	function getSeriesId() {
		return $this->_seriesId;
	}

	/**
	 * Set the series ID for this series.
	 * @param $seriesId int
	 */
	function setSeriesId($seriesId) {
		$this->_seriesId = $seriesId;
	}

	/**
	 * Persist a signoff insertion
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {
		$press =& $request->getPress();
		$seriesId = $this->getSeriesId();
		$userId = array_shift($newRowId);

		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');

		// Make sure the membership doesn't already exist
		if ($seriesEditorsDao->editorExists($press->getId(), $this->getSeriesId(), $userId)) {
			return false;
		}

		// Otherwise, insert the row.
		$seriesEditorsDao->insertEditor($press->getId(), $this->getSeriesId(), $userId, true, true);
		return true;
	}

	/**
	 * Delete a series editor association with this series.
	 * @see ListbuilderHandler::deleteEntry
	 * @param $request PKPRequest
	 * @param $rowId int
	 */
	function deleteEntry(&$request, $rowId) {
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$press =& $request->getPress();

		$seriesEditorsDao->deleteEditor($press->getId(), $this->getSeriesId(), $rowId);
		return true;
	}
}

?>
