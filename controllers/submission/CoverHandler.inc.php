<?php

/**
 * @file controllers/submission/CategoriesListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CoverHandler
 * @ingroup controllers_submission
 *
 * @brief Component serving up cover images for submissions.
 */

import('lib.pkp.classes.handler.PKPHandler');

class CoverHandler extends PKPHandler {
	/** @var $press Press */
	var $_press;

	/** @var The group ID for this listbuilder */
	var $monographId;

	/**
	 * Constructor
	 */
	function CoverHandler() {
		parent::PKPHandler();
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noContext'));

		// Access may be made either as a member of the public, or
		// via pre-publication access to editorial users.
		$monographAccessPolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);
		// Published monograph access for the public
		$publishedMonographAccessPolicy = new PolicySet(COMBINING_DENY_OVERRIDES);
		import('lib.pkp.classes.security.authorization.internal.SubmissionRequiredPolicy');
		$publishedMonographAccessPolicy->addPolicy(new SubmissionRequiredPolicy($request, $args));
		$monographAccessPolicy->addPolicy($publishedMonographAccessPolicy);

		// Pre-publication access for editorial roles
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$monographAccessPolicy->addPolicy(
			new SubmissionAccessPolicy(
				$request, $args,
				array_intersect_key(
					$roleAssignments,
					array( // Only permit these roles
						ROLE_ID_MANAGER,
						ROLE_ID_SUB_EDITOR,
					)
				)
			)
		);

		$this->addPolicy($monographAccessPolicy);

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Set the monograph ID
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		$this->monographId = $monographId;
	}

	/**
	 * Get the monograph ID
	 * @return int
	 */
	function getMonographId() {
		return $this->monographId;
	}

	/**
	 * Set the current press
	 * @param $press Press
	 */
	function setPress($press) {
		$this->_press = $press;
	}

	/**
	 * Get the current press
	 * @return Press
	 */
	function getPress() {
		return $this->_press;
	}

	/**
	 * Serve the cover image for a published monograph.
	 */
	function cover($args, $request) {
		// this function is only used on the book page i.e. for published monographes
		$publishedMonograph = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		if (!$coverImage = $publishedMonograph->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedMonograph->getPressId(), $publishedMonograph->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['name'], null, true);
	}

	/**
	 * Serve the cover thumbnail for a published monograph.
	 */
	function thumbnail($args, $request) {
		// use ASSOC_TYPE_MONOGRAPH to set the cover at any workflow stage
		// i.e. also if the monograph has not been published yet
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId(), null, false);

		if (!$publishedMonograph || !$coverImage = $publishedMonograph->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default-small.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedMonograph->getPressId(), $publishedMonograph->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['thumbnailName'], null, true);
	}

}

?>
