<?php

/**
 * @file classes/plugins/PubIdPlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubIdPlugin
 * @ingroup plugins
 *
 * @brief Public identifiers plugins common functions
 */

import('lib.pkp.classes.plugins.PKPPubIdPlugin');

abstract class PubIdPlugin extends PKPPubIdPlugin {

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Protected template methods from PKPPlubIdPlugin
	//
	/**
	 * @copydoc PKPPubIdPlugin::getPubId()
	 */
	function getPubId($pubObject) {
		// Get the pub id type
		$pubIdType = $this->getPubIdType();

		// If we already have an assigned pub id, use it.
		$storedPubId = $pubObject->getStoredPubId($pubIdType);
		if ($storedPubId) return $storedPubId;

		// Determine the type of the publishing object.
		$pubObjectType = $this->getPubObjectType($pubObject);

		// Initialize variables for publication objects.
		$submission = ($pubObjectType == 'Submission' ? $pubObject : null);
		$representation = ($pubObjectType == 'Representation' ? $pubObject : null);
		$submissionFile = ($pubObjectType == 'SubmissionFile' ? $pubObject : null);

		// Get the context id.
		if ($pubObjectType == 'Submission') {
			$contextId = $pubObject->getContextId();
		} else {
			// Retrieve the submission.
			assert(is_a($pubObject, 'Representation') || is_a($pubObject, 'SubmissionFile'));
			$submissionDao = Application::getSubmissionDAO();
			$submission = $submissionDao->getById($pubObject->getSubmissionId(), null, true);
			if (!$submission) return null;
			// Now we can identify the context.
			$contextId = $submission->getContextId();
		}
		// Check the context
		$context = $this->getContext($contextId);
		if (!$context) return null;
		$contextId = $context->getId();

		// Check whether pub ids are enabled for the given object type.
		$objectTypeEnabled = $this->isObjectTypeEnabled($pubObjectType, $contextId);
		if (!$objectTypeEnabled) return null;

		// Retrieve the pub id prefix.
		$pubIdPrefix = $this->getSetting($contextId, $this->getPrefixFieldName());
		if (empty($pubIdPrefix)) return null;

		// Generate the pub id suffix.
		$suffixFieldName = $this->getSuffixFieldName();
		$suffixGenerationStrategy = $this->getSetting($contextId, $suffixFieldName);
		switch ($suffixGenerationStrategy) {
			case 'customId':
				$pubIdSuffix = $pubObject->getData($suffixFieldName);
				break;

			case 'pattern':
				$suffixPatternsFieldNames = $this->getSuffixPatternsFieldNames();
				$pubIdSuffix = $this->getSetting($contextId, $suffixPatternsFieldNames[$pubObjectType]);

				// %p - press initials
				$pubIdSuffix = PKPString::regexp_replace('/%p/', PKPString::strtolower($context->getAcronym($context->getPrimaryLocale())), $pubIdSuffix);

				// %x - custom identifier
				if ($pubObject->getStoredPubId('publisher-id')) {
					$pubIdSuffix = PKPString::regexp_replace('/%x/', $pubObject->getStoredPubId('publisher-id'), $pubIdSuffix);
				}

				if ($submission) {
					// %m - monograph id
					$pubIdSuffix = PKPString::regexp_replace('/%m/', $submission->getId(), $pubIdSuffix);
				}

				if ($representation) {
					// %f - publication format id
					$pubIdSuffix = PKPString::regexp_replace('/%f/', $representation->getId(), $pubIdSuffix);
				}

				if ($submissionFile) {
					// %s - file id
					$pubIdSuffix = PKPString::regexp_replace('/%s/', $submissionFile->getFileId(), $pubIdSuffix);
				}

				break;

			default:
				$pubIdSuffix = PKPString::strtolower($context->getAcronym($context->getPrimaryLocale()));

				if ($submission) {
					$pubIdSuffix .= '.' . $submission->getId();
				}

				if ($representation) {
					$pubIdSuffix .= '.' . $representation->getId();
				}

				if ($submissionFile) {
					$pubIdSuffix .= '.' . $submissionFile->getFileId();
				}
		}
		if (empty($pubIdSuffix)) return null;

		// Costruct the pub id from prefix and suffix.
		$pubId = $this->constructPubId($pubIdPrefix, $pubIdSuffix, $contextId);

		return $pubId;
	}


}

?>
