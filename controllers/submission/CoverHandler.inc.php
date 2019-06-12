<?php

/**
 * @file controllers/submission/CategoriesListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2000-2017 John Willinsky
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
	var $submissionId;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpPublishedSubmissionAccessPolicy');
		$this->addPolicy(new OmpPublishedSubmissionAccessPolicy($request, $args, $roleAssignments, 'submissionId', false));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Set the monograph ID
	 * @param $submissionId int
	 */
	function setMonographId($submissionId) {
		$this->monographId = $submissionId;
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
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$publishedSubmission = $publishedSubmissionDao->getById($monograph->getId(), null, false);

		if (!$coverImage = $publishedSubmission->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedSubmission->getPressId(), $publishedSubmission->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['name'], null, true);
	}

	/**
	 * Serve the cover thumbnail for a published monograph.
	 */
	function thumbnail($args, $request) {
		// use ASSOC_TYPE_MONOGRAPH to set the cover at any workflow stage
		// i.e. also if the monograph has not been published yet
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$publishedSubmissionDao = DAORegistry::getDAO('PublishedSubmissionDAO');
		$publishedSubmission = $publishedSubmissionDao->getById($monograph->getId(), null, false);

		if (!$publishedSubmission || !$coverImage = $publishedSubmission->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default-small.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedSubmission->getPressId(), $publishedSubmission->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['thumbnailName'], null, true);
	}

}

?>
