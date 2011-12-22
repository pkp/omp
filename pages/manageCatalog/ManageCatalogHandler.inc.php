<?php

/**
 * @file pages/manageCatalog/ManageCatalogHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogHandler
 * @ingroup pages_manageCatalog
 *
 * @brief Handle requests for catalog management.
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.core.JSONMessage');

class ManageCatalogHandler extends Handler {
	/**
	 * Constructor
	 */
	function ManageCatalogHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array(
				'index', // Container
				'features', 'newReleases', 'search',
				'getCategories', 'category', // By category
				'getSeries', 'series', // By series
				'setFeatured'
			)
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		$this->setupTemplate($request);

		// Call parent method.
		parent::initialize($request, $args);
	}


	//
	// Public handler methods
	//
	/**
	 * Show the catalog management home.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		// Render the view.
		$templateMgr =& TemplateManager::getManager();

		import('controllers.modals.submissionMetadata.linkAction.MonographlessCatalogEntryLinkAction');
		$catalogEntryAction = new MonographlessCatalogEntryLinkAction($request);
		$templateMgr->assign('catalogEntryAction', $catalogEntryAction);

		$templateMgr->display('manageCatalog/index.tpl');
	}

	/**
	 * View the tab contents for the Features tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function features($args, &$request) {
		// Set up the monograph list template
		$press =& $request->getPress();
		$this->_setupMonographsTemplate(
			true, 'features',
			ASSOC_TYPE_PRESS, $press->getId()
		);

		$templateMgr =& TemplateManager::getManager();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getPressFeatures($press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Display the monograph list
		$templateMgr->display('manageCatalog/monographs.tpl');
	}

	/**
	 * View the tab contents for the New Releases tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function newReleases($args, &$request) {
		// Set up the monograph list template
		$press =& $request->getPress();
		$this->_setupMonographsTemplate(
			true, 'newReleases',
			ASSOC_TYPE_NEW_RELEASE, $press->getId()
		);

		$templateMgr =& TemplateManager::getManager();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getNewReleases($press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Display the monograph list
		$templateMgr->display('manageCatalog/monographs.tpl');
	}

	/**
	 * List the available categories.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function getCategories($args, &$request) {
		$press =& $request->getPress();
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoryIterator =& $categoryDao->getByPressId($press->getId());
		$categoryArray = array();
		while ($category =& $categoryIterator->next()) {
			$categoryArray[$category->getPath()] = $category->getLocalizedTitle();
			unset($category);
		}
		$json = new JSONMessage(true, $categoryArray);
		return $json->getString();
	}

	/**
	 * List the available series.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function getSeries($args, &$request) {
		$press =& $request->getPress();
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesIterator =& $seriesDao->getByPressId($press->getId());
		$seriesArray = array();
		while ($series =& $seriesIterator->next()) {
			$seriesArray[$series->getPath()] = $series->getLocalizedTitle();
			unset($series);
		}
		$json = new JSONMessage(true, $seriesArray);
		return $json->getString();
	}

	/**
	 * View the content of a category.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function category($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();

		// Get the category
		$categoryDao =& DAORegistry::getDAO('CategoryDAO');
		$categoryPath = array_shift($args);
		$category =& $categoryDao->getByPath($categoryPath, $press->getId());
		$templateMgr->assign('category', $category);

		// Set up the monograph list template
		$this->_setupMonographsTemplate(
			true, 'category',
			ASSOC_TYPE_CATEGORY, $category->getId()
		);

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByCategoryId($category->getId(), $press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Fetch the current features
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$features = $featureDao->getSequencesByAssoc(ASSOC_TYPE_CATEGORY, $category->getId());
		$templateMgr->assign('features', $features);

		// Return the monograph list as a JSON message
		$json = new JSONMessage(true, $templateMgr->fetch('manageCatalog/monographs.tpl'));
		return $json->getString();
	}

	/**
	 * View the content of a series.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function series($args, &$request) {
		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();

		// Get the series
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesPath = array_shift($args);
		$series =& $seriesDao->getByPath($seriesPath, $press->getId());
		$templateMgr->assign('series', $series);

		// Set up the monograph list template
		$this->_setupMonographsTemplate(true, 'series',
			ASSOC_TYPE_SERIES, $series->getId()
		);

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getBySeriesId($series->getId(), $press->getId());
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Return the monograph list as a JSON message
		$json = new JSONMessage(true, $templateMgr->fetch('manageCatalog/monographs.tpl'));
		return $json->getString();
	}

	/**
	 * View the tab contents for the Search Results.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function search($args, &$request) {
		$searchText = array_shift($args);
		$this->_setupMonographsTemplate(false, 'search');

		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();

		// Fetch the monographs to display
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonographs =& $publishedMonographDao->getByPressId($press->getId(), $searchText);
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		// Display the monograph list
		$templateMgr->display('manageCatalog/monographs.tpl');
	}

	/**
	 * Set featured status for a submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function setFeatured($args, &$request) {
		$press =& $request->getPress();

		// Identification of item to set featured state on
		$monographId = (int) array_shift($args);
		$assocType = (int) array_shift($args);
		$assocId = (int) array_shift($args);

		// Description of new state
		$newState = (int) array_shift($args);
		$newSeq = (int) array_shift($args);

		// Validate the monograph ID
		// FIXME: Can this be done with the auth framework without
		// needing the policy throughout?
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph =& $publishedMonographDao->getById($monographId, $press->getId());
		if (!$publishedMonograph) fatalError('Invalid monograph!');

		// Determine the assoc type and ID to be used.
		switch ($assocType) {
			case ASSOC_TYPE_PRESS:
				// Force assocId to press
				$assocId = $press->getId();
				break;
			case ASSOC_TYPE_NEW_RELEASE:
				// Force assocId to press
				$assocId = $press->getId();
				break;
			case ASSOC_TYPE_CATEGORY:
				// Validate specified assocId
				$categoryDao =& DAORegistry::getDAO('CategoryDAO');
				$category =& $categoryDao->getById($assocId, $press->getId());
				if (!$category) fatalError('Invalid category!');
				break;
			case ASSOC_TYPE_SERIES:
				// Validate specified assocId
				$seriesDao =& DAORegistry::getDAO('SeriesDAO');
				$series =& $seriesDao->getById($assocId, $press->getId());
				if (!$series) fatalError('Invalid series!');
				break;
			default:
				fatalError('Invalid feature specified.');
		}

		// Delete the old featured state
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteFeature($monographId, $assocType, $assocId);

		// If necessary, insert the new featured state and resequence.
		if ($newState) {
			$featureDao->insertFeature($monographId, $assocType, $assocId, $newSeq);
			$sequences = $featureDao->resequenceByAssoc($assocType, $assocId);
		} else {
			$sequences = null;
		}

		$json = new JSONMessage(true, $sequences);
		return $json->getString();
	}

	//
	// Private functions
	//
	/**
	 * Set up template including link actions for the catalog view
	 * @param $includeOrganizeAction boolean
	 * @param $listName string Unique identifier of monograph list (for
	 *  disambiguation of HTML element IDs)
	 * @param $assocType Association type of features to fetch
	 *  (ASSOC_TYPE_...)
	 * @param $assocId Association ID of features to fetch
	 */
	function _setupMonographsTemplate($includeOrganizeAction, $listName, $assocType, $assocId) {
		// Loadubmission locale content for monograph listing
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);

		$templateMgr =& TemplateManager::getManager();
		import('lib.pkp.classes.linkAction.request.NullAction');

		// Organize action (if enabled)
		$templateMgr->assign('includeOrganizeAction', $includeOrganizeAction);

		// Add the list name, for ID differentiation
		$templateMgr->assign('listName', $listName);

		// Expose the featured monograph IDs and associated params
		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featuredMonographIds = $featureDao->getSequencesByAssoc($assocType, $assocId);
		$templateMgr->assign('featuredMonographIds', $featuredMonographIds);
		$templateMgr->assign('featureAssocType', $assocType);
		$templateMgr->assign('featureAssocId', $assocId);
	}
}

?>
