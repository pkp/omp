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
		import('classes.security.authorization.OmpPublishedMonographAccessPolicy');
		$this->addPolicy(new OmpPublishedMonographAccessPolicy($request, $args, $roleAssignments));
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
		$publishedMonograph = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		if (!$coverImage = $publishedMonograph->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default-small.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedMonograph->getPressId(), $publishedMonograph->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['thumbnailName'], null, true);
	}

	/**
	 * Serve the cover catalog image for a published monograph.
	 */
	function catalog($args, $request) {
		$publishedMonograph = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLISHED_MONOGRAPH);
		if (!$coverImage = $publishedMonograph->getCoverImage()) {
			// Can't use Request::redirectUrl; FireFox doesn't
			// seem to like it for images.
			header('Location: ' . $request->getBaseUrl() . '/templates/images/book-default.png');
			exit;
		}

		import('classes.file.SimpleMonographFileManager');
		$simpleMonographFileManager = new SimpleMonographFileManager($publishedMonograph->getPressId(), $publishedMonograph->getId());
		$simpleMonographFileManager->downloadFile($simpleMonographFileManager->getBasePath() . $coverImage['catalogName'], null, true);
	}
}

?>
