<?php

/**
 * @file controllers/grid/settings/genre/form/GenreForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GenreForm
 * @ingroup controllers_grid_settings_genre_form
 *
 * @brief Form for adding/editing a Monograph File Genre.
 */

import('lib.pkp.classes.form.Form');

class GenreForm extends Form {
	/** the id for the genre being edited **/
	var $_genreId;

	/**
	 * Set the genre id
	 * @param $genreId int
	 */
	function setGenreId($genreId) {
	    $this->_genreId = $genreId;
	}

	/**
	 * Get the genre id
	 * @return int
	 */
	function getGenreId() {
	    return $this->_genreId;
	}


	/**
	 * Constructor.
	 */
	function GenreForm($genreId = null) {
		$this->setGenreId($genreId);
		parent::Form('controllers/grid/settings/genre/form/genreForm.tpl');

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'manager.setup.form.genre.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$press =& $request->getPress();

		$genreDao =& DAORegistry::getDAO('GenreDAO');

		if($this->getGenreId()) {
			$genre =& $genreDao->getById($this->getGenreId(), $press->getId());
		}

		if (isset($genre) ) {
			$this->_data = array(
				'genreId' => $this->getGenreId(),
				'name' => $genre->getName(null),
				'designation' => $genre->getDesignation(null),
				'sortable' => $genre->getSortable(),
				'category' => $genre->getCategory()
			);
		} else {
			$this->_data = array(
				'name' => '',
				'designation' => ''
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
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('monographFileCategories', array(GENRE_CATEGORY_DOCUMENT => Locale::translate('submission.document'),
					GENRE_CATEGORY_ARTWORK => Locale::translate('submission.art')));

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('genreId', 'name', 'designation', 'sortable', 'category'));
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save email template.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, $request) {
		$genreDao =& DAORegistry::getDAO('GenreDAO');
		$press =& $request->getPress();

		// Update or insert genre
		if (!$this->getGenreId()) {
			$genre = $genreDao->newDataObject();
		} else {
			$genre =& $genreDao->getById($this->getGenreId());
		}

		$genre->setData('name', $this->getData('name'), null); // Localized
		$genre->setData('designation', $this->getData('designation'), null); // Localized
		$genre->setSortable($this->getData('sortable'));
		$genre->setCategory($this->getData('category'));

		if (!$this->getGenreId()) {
			$this->setGenreId($genreDao->insertObject($genre));
		} else {
			$genreDao->updateObject($genre);
		}

		return true;
	}
}

?>
