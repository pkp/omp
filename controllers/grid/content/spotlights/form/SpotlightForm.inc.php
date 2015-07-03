<?php
/**
 * @file controllers/grid/content/spotlights/form/SpotlightForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightForm
 * @ingroup controllers_grid_content_spotlights_form
 *
 * @brief Form for reading/creating/editing spotlight items.
 */


import('lib.pkp.classes.form.Form');

class SpotlightForm extends Form {
	/**
	 * @var spotlightId
	 */
	var $_spotlightId;

	/**
	 * @var pressId
	 */
	var $_pressId;

	/**
	 * Constructor
	 * @param $pressId int
	 * @param $spotlightId int leave as default for new spotlight
	 */
	function SpotlightForm($pressId, $spotlightId = null) {
		parent::Form('controllers/grid/content/spotlights/form/spotlightForm.tpl');

		$this->_spotlightId = $spotlightId;
		$this->_pressId = $pressId;

		$this->addCheck(new FormValidatorCustom($this, 'assocId', 'required', 'grid.content.spotlights.itemRequired', create_function('$assocId, $form', 'list($id, $type) = preg_split("/:/", $assocId) ; return is_numeric($id) && $id > 0 && $form->_isValidSpotlightType($type);'), array(&$this)));
		$this->addCheck(new FormValidator($this, 'title', 'required', 'grid.content.spotlights.titleRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Extended methods from Form
	//
	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);

		$spotlightDao = DAORegistry::getDAO('SpotlightDAO');
		$spotlight = $spotlightDao->getById($this->getSpotlightId());
		$templateMgr->assign_by_ref('spotlight', $spotlight);
		$templateMgr->assign('pressId', $this->getPressId());

		if (isset($spotlight)) {
			$templateMgr->assign('title', $spotlight->getTitle(null));
			$templateMgr->assign('description', $spotlight->getDescription(null));
			$templateMgr->assign('assocTitle', $this->getAssocTitle($spotlight->getAssocId(), $spotlight->getAssocType()));
			$templateMgr->assign('assocId', $spotlight->getAssocId() . ':' . $spotlight->getAssocType());
		}

		return parent::fetch($request);
	}

	//
	// Extended methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'description', 'assocId'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {

		$spotlightDao = DAORegistry::getDAO('SpotlightDAO');

		$spotlight = $spotlightDao->getById($this->getSpotlightId(), $this->getPressId());

		if (!$spotlight) {
			// this is a new spotlight
			$spotlight = $spotlightDao->newDataObject();
			$spotlight->setPressId($this->getPressId());
			$existingSpotlight = false;
		} else {
			$existingSpotlight = true;
		}

		list($assocId, $assocType) = preg_split('/:/', $this->getData('assocId'));
		$spotlight->setAssocType($assocType);
		$spotlight->setTitle($this->getData('title'), null); // localized
		$spotlight->setDescription($this->getData('description'), null); // localized
		$spotlight->setAssocId($assocId);

		if ($existingSpotlight) {
			$spotlightDao->updateObject($spotlight);
			$spotlightId = $spotlight->getId();
		} else {
			$spotlightId = $spotlightDao->insertObject($spotlight);
		}

		return $spotlightId;
	}


	//
	// helper methdods.
	//

	/**
	 * Fetch the spotlight Id for this form.
	 * @return int $spotlightId
	 */
	function getSpotlightId() {
		return $this->_spotlightId;
	}

	/**
	 * Fetch the press Id for this form.
	 * @return int $pressId
	 */
	function getPressId() {
		return $this->_pressId;
	}

	/**
	 * Fetch the title of the Spotlight item, based on the assocType and pressId
	 * @param int $assocId
	 * @param int $assocType
	 */
	function getAssocTitle($assocId, $assocType) {

		$returner = null;
		switch ($assocType) {
			case SPOTLIGHT_TYPE_BOOK:
				$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonograph = $publishedMonographDao->getById($assocId, $this->getPressId());
				$returner = isset($publishedMonograph) ? $publishedMonograph->getLocalizedTitle() : '';
				break;
			case SPOTLIGHT_TYPE_SERIES:
				$seriesDao = DAORegistry::getDAO('SeriesDAO');
				$series = $seriesDao->getById($assocId, $this->getPressId());
				$returner = isset($series) ? $series->getLocalizedTitle() : '';
				break;
			case SPOTLIGHT_TYPE_AUTHOR:
				$authorDao = DAORegistry::getDAO('AuthorDAO');
				$author = $authorDao->getById($assocId);
				$returner = isset($author) ? $author->getFullName() : '';
				break;
			default:
				fatalError('invalid type specified');
		}
		return $returner;
	}

	/**
	 * Internal function for spotlight type verification.
	 * @param int $type
	 * @return boolean
	 */
	function _isValidSpotlightType($type) {
		$validTypes = array(SPOTLIGHT_TYPE_AUTHOR, SPOTLIGHT_TYPE_BOOK, SPOTLIGHT_TYPE_SERIES);
		return in_array((int) $type, $validTypes);
	}
}

?>
