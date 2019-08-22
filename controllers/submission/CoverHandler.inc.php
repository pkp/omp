<?php

/**
 * @file controllers/submission/CoverHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
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

	/** @var The monograph ID for this handler */
	var $monographId;

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.OmpPublishedSubmissionAccessPolicy');
		$this->addPolicy(new OmpPublishedSubmissionAccessPolicy($request, $args, $roleAssignments));
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
	 * Serve the cover image for a published submission.
	 */
	function cover($args, $request) {
		// this function is only used on the book page i.e. for published submissiones
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		if (!$coverImage = $submission->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($submission->getData('contextId'), $submission->getId());
		$simpleMonographFileManager->downloadByPath($simpleMonographFileManager->getBasePath() . $coverImage['name'], null, true);
	}

	/**
	 * Serve the cover thumbnail for a published submission.
	 */
	function thumbnail($args, $request) {
		// use ASSOC_TYPE_MONOGRAPH to set the cover at any workflow stage
		// i.e. also if the monograph has not been published yet
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		if (!$submission || !$coverImage = $submission->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default-small.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($submission->getData('contextId'), $submission->getId());
		$simpleMonographFileManager->downloadByPath($simpleMonographFileManager->getBasePath() . $coverImage['thumbnailName'], null, true);
	}

}


