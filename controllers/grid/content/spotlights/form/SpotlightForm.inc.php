<?php
/**
 * @file controllers/grid/content/spotlights/form/SpotlightForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	 * @param $readOnly boolean
	 */
	function SpotlightForm($pressId, $spotlightId = null) {
		parent::Form('controllers/grid/content/spotlights/form/spotlightForm.tpl');

		$this->_spotlightId = $spotlightId;
		$this->_pressId = $pressId;

		$this->addCheck(new FormValidator($this, 'title', 'required', 'grid.content.spotlights.titleRequired'));
		$this->addCheck(new FormValidator($this, 'type', 'required', 'grid.content.spotlights.typeRequired'));
		$this->addCheck(new FormValidator($this, 'location', 'required', 'grid.content.spotlights.locationRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Extended methods from Form
	//
	/**
	* @see Form::fetch()
	*/
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();

		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');
		$spotlight =& $spotlightDao->getById($this->getSpotlightId());
		$templateMgr->assign_by_ref('spotlight', $spotlight);
		$templateMgr->assign('pressId', $this->getPressId());
		$spotlightTypes = array(
				SPOTLIGHT_TYPE_BOOK => __('grid.content.spotlights.form.type.book'),
				SPOTLIGHT_TYPE_SERIES => __('series.series'),
				SPOTLIGHT_TYPE_AUTHOR => __('user.role.author')
			);

		$templateMgr->assign('spotlightTypes', $spotlightTypes);

		$spotlightLocations = array(
				SPOTLIGHT_LOCATION_HOMEPAGE => __('grid.content.spotlights.category.homepage'),
				SPOTLIGHT_LOCATION_SIDEBAR => __('grid.content.spotlights.category.sidebar')
		);

		if (isset($spotlight)) {
			$templateMgr->assign('title', $spotlight->getTitle(null));
			$templateMgr->assign('description', $spotlight->getDescription(null));
			$templateMgr->assign('location', $spotlight->getLocation());
			$templateMgr->assign('type', $spotlight->getAssocType());
			$templateMgr->assign('assocTitle', $this->getAssocTitle($spotlight->getAssocId(), $spotlight->getAssocType()));
			$templateMgr->assign('assocId', $spotlight->getAssocId());
		} else {
			$templateMgr->assign('type', SPOTLIGHT_TYPE_BOOK); // default
		}

		$templateMgr->assign('spotlightLocations', $spotlightLocations);

		return parent::fetch($request);
	}

	//
	// Extended methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'type', 'description', 'location', 'assocId'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {

		$spotlightDao =& DAORegistry::getDAO('SpotlightDAO');

		$spotlight =& $spotlightDao->getById($this->getSpotlightId(), $this->getPressId());

		if (!$spotlight) {
			// this is a new spotlight
			$spotlight = $spotlightDao->newDataObject();
			$spotlight->setPressId($this->getPressId());
			$existingSpotlight = false;
		} else {
			$existingSpotlight = true;
		}

		$spotlight->setAssocType($this->getData('type'));
		$spotlight->setTitle($this->getData('title'), null); // localized
		$spotlight->setDescription($this->getData('description'), null); // localized
		$spotlight->setLocation($this->getData('location'));
		$spotlight->setAssocId($this->getData('assocId'));

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
				$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
				$publishedMonograph =& $publishedMonographDao->getById($assocId, $this->getPressId());
				$returner = isset($publishedMonograph) ? $publishedMonograph->getLocalizedTitle() : '';
				break;
			case SPOTLIGHT_TYPE_SERIES:
				$seriesDao =& DAORegistry::getDAO('SeriesDAO');
				$series =& $seriesDao->getById($assocId, $this->getPressId());
				$returner = isset($series) ? $series->getLocalizedTitle() : '';
				break;
			case SPOTLIGHT_TYPE_AUTHOR:
				$authorDao =& DAORegistry::getDAO('AuthorDAO');
				$author =& $authorDao->getById($assocId, $this->getPressId());
				$returner = isset($author) ? $author->getFullName() : '';
				break;
			default:
				fatalError('invalid type specified');
		}
		return $returner;
	}
}

?>
