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
	var $seriesId;

	/**
	 * Constructor.
	 */
	function SeriesForm($seriesId = null) {
		$this->seriesId = $seriesId;
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

		$divisionDao =& DAORegistry::getDAO('DivisionDAO');
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		$divisions =& $divisionDao->getByPressId($press->getId());

		$divisionsArray = array();
		while ($division =& $divisions->next()) {
			$divisionsArray[] = array('id' => $division->getId(), 'title' => $division->getLocalizedTitle());
			unset($division);
		}

		if($this->seriesId) {
			$series =& $seriesDao->getById($this->seriesId);
		}

		if (isset($series) ) {
			$this->_data = array(
				'seriesId' => $this->seriesId,
				'title' => $series->getTitle(null),
				'divisions' => $divisionsArray,
				'currentDivision' => $series->getDivisionId(),
				'affiliation' => $series->getAffiliation(null)
			);
		} else {
			$this->_data = array(
				'title' => '',
				'divisions' => $divisionsArray,
				'affiliation' => ''
			);
		}

		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		$this->_data['rowId'] = isset($args['rowId']) ? $args['rowId'] : null;
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('seriesId', 'title', 'division', 'affiliation'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save series.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, $request) {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$press =& $request->getPress();

		// Update or insert series
		if (!isset($this->seriesId)) {
			import('classes.press.Series');
			$series = new Series();
			$series->setPressId($press->getId());
			$series->setDivisionId($this->getData('division'));
			$series = $this->_setSeriesLocaleFields($series, $request);
			$this->seriesId = $seriesDao->insertObject($series);
		} else {
			$series =& $seriesDao->getById($this->seriesId);
			$series->setPressId($press->getId());
			$series->setDivisionId($this->getData('division'));
			$series = $this->_setSeriesLocaleFields($series, $request);
			$seriesDao->updateObject($series);
		}
		return true;
	}


	//
	// Private helper methods
	//
	/**
	 * Set locale fields on a Series object.
	 * @param Series
	 * @param Request
	 * @return UserGroup
	 */
	function _setSeriesLocaleFields($series, $request) {

		$title = $this->getData('title');
		$affiliation = $this->getData('affiliation');

		$series->setData('title', $title, null);
		$series->setData('affiliation', $affiliation, null);

		return $series;
	}
}

?>
