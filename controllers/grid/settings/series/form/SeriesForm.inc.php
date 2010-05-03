<?php

/**
 * @file controllers/grid/settings/series/form/SeriesForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesForm
 * @ingroup controllers_grid_series_form
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
		$this->addCheck(new FormValidator($this, 'title', 'required', 'manager.setup.form.series.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$args, &$request) {
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
				'title' => $series->getLocalizedTitle(),
				'divisions' => $divisionsArray,
				'currentDivision' => $series->getDivisionId(),
				'affiliation' => $series->getLocalizedAffiliation()
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
	 * Display
	 */
	function display() {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('seriesId', 'title', 'division', 'affiliation'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save email template.
	 */
	function execute($args, $request) {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$press =& $request->getPress();

		// Update or insert group group
		if (!isset($this->seriesId)) {
			import('classes.press.Series');
			$series = new Series();

			$series->setPressId($press->getId());
			$series->setTitle($this->getData('title'), Locale::getLocale()); // Localized
			$series->setAffiliation($this->getData('affiliation'), Locale::getLocale()); // Localized
			$series->setDivisionId($this->getData('division'));

			$this->seriesId = $seriesDao->insertObject($series);
		} else {
			$series =& $seriesDao->getById($this->seriesId);
			$series->setPressId($press->getId());
			$series->setTitle($this->getData('title'), Locale::getLocale()); // Localized
			$series->setAffiliation($this->getData('affiliation'), Locale::getLocale()); // Localized
			$series->setDivisionId($this->getData('division'));

			$seriesDao->updateObject($series);
		}
		return true;
	}
}

?>
