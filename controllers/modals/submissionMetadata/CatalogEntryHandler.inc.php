<?php

/**
 * @file controllers/modals/submissionMetadata/CatalogEntryHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryHandler
 * @ingroup ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle the request to generate the tab structure on the New Catalog Entry page.
 */

// Import the base Handler.
import('classes.handler.Handler');

class CatalogEntryHandler extends Handler {
	/**
	 * Constructor.
	 */
	/** The monograph **/
	var $_monograph;

	/** The current stage id **/
	var $_stageId;

	function CatalogEntryHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array('fetch', 'saveForm'));
	}


	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->_monograph =& $monographDao->getById($request->getUserVar('monographId'));
		$this->_stageId =& $request->getUserVar('stageId');

		// Load grid-specific translations
		AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_SUBMISSION);
		$this->setupTemplate();
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$this->addPolicy(new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getStageId() {
		return $this->_stageId;
	}


	//
	// Public handler methods
	//
	/**
	 * Display the tabs index page.
	 * @param $request PKPRequest
	 * @param $args array
	 */
	function fetch($request, $args) {
		$templateMgr =& TemplateManager::getManager();

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('stageId', $this->getStageId());

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($monograph->getPressId());
		$formats = array();

		while ($publicationFormat =& $publicationFormats->next()) {
			$formats[] =& $publicationFormat;
			unset($publicationFormat);
		}

		$templateMgr->assign_by_ref('publicationFormats', $formats);

		$this->setupTemplate();
		return $templateMgr->fetchJson('controllers/modals/submissionMetadata/catalogEntryTabs.tpl');
	}
}

?>
