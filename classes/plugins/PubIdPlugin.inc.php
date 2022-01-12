<?php

/**
 * @file classes/plugins/PubIdPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PubIdPlugin
 * @ingroup plugins
 *
 * @brief Public identifiers plugins common functions
 */

namespace APP\plugins;

use APP\facades\Repo;
use PKP\core\PKPString;
use PKP\db\DAORegistry;

use PKP\plugins\PKPPubIdPlugin;

abstract class PubIdPlugin extends PKPPubIdPlugin
{
    //
    // Protected template methods from PKPPlubIdPlugin
    //
    /**
     * @copydoc PKPPubIdPlugin::getPubObjectTypes()
     */
    public function getPubObjectTypes()
    {
        $pubObjectTypes = parent::getPubObjectTypes();
        $pubObjectTypes['Chapter'] = '\Chapter'; // FIXME: Add namespacing
        return $pubObjectTypes;
    }

    /**
     * @copydoc PKPPubIdPlugin::getPubId()
     */
    public function getPubId($pubObject)
    {
        // Get the pub id type
        $pubIdType = $this->getPubIdType();

        // If we already have an assigned pub id, use it.
        $storedPubId = $pubObject->getStoredPubId($pubIdType);
        if ($storedPubId) {
            return $storedPubId;
        }

        // Determine the type of the publishing object.
        $pubObjectType = $this->getPubObjectType($pubObject);

        // Initialize variables for publication objects.
        $submission = ($pubObjectType == 'Submission' ? $pubObject : null);
        $representation = ($pubObjectType == 'Representation' ? $pubObject : null);
        $submissionFile = ($pubObjectType == 'SubmissionFile' ? $pubObject : null);
        $chapter = ($pubObjectType == 'Chapter' ? $pubObject : null);

        // Get the context id.
        if ($pubObjectType == 'Submission') {
            $contextId = $pubObject->getContextId();
        } else {
            // Retrieve the submission.
            if (is_a($pubObject, 'Chapter') || is_a($pubObject, 'Representation')) {
                $publication = Repo::publication()->get($pubObject->getData('publicationId'));
                $submission = Repo::submission()->get($publication->getData('submissionId'));
            } else {
                assert(is_a($pubObject, 'SubmissionFile'));
                $submission = Repo::submission()->get($pubObject->getData('submissionId'));
            }
            if (!$submission) {
                return null;
            }
            // Now we can identify the context.
            $contextId = $submission->getContextId();
        }
        // Check the context
        $context = $this->getContext($contextId);
        if (!$context) {
            return null;
        }
        $contextId = $context->getId();

        // Check whether pub ids are enabled for the given object type.
        $objectTypeEnabled = $this->isObjectTypeEnabled($pubObjectType, $contextId);
        if (!$objectTypeEnabled) {
            return null;
        }

        // Retrieve the pub id prefix.
        $pubIdPrefix = $this->getSetting($contextId, $this->getPrefixFieldName());
        if (empty($pubIdPrefix)) {
            return null;
        }

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

                // %p - press initials, remove special characters and uncapitalize
                $pubIdSuffix = PKPString::regexp_replace('/%p/', PKPString::regexp_replace('/[^-._;()\/:A-Za-z0-9]/', '', PKPString::strtolower($context->getAcronym($context->getPrimaryLocale()))), $pubIdSuffix);

                // %x - custom identifier
                if ($pubObject->getStoredPubId('publisher-id')) {
                    $pubIdSuffix = PKPString::regexp_replace('/%x/', $pubObject->getStoredPubId('publisher-id'), $pubIdSuffix);
                }

                if ($submission) {
                    // %m - monograph id
                    $pubIdSuffix = PKPString::regexp_replace('/%m/', $submission->getId(), $pubIdSuffix);
                }

                if ($chapter) {
                    // %c - chapter id
                    $pubIdSuffix = PKPString::regexp_replace('/%c/', $chapter->getId(), $pubIdSuffix);
                }

                if ($representation) {
                    // %f - publication format id
                    $pubIdSuffix = PKPString::regexp_replace('/%f/', $representation->getId(), $pubIdSuffix);
                }

                if ($submissionFile) {
                    // %s - file id
                    $pubIdSuffix = PKPString::regexp_replace('/%s/', $submissionFile->getId(), $pubIdSuffix);
                }

                break;

            default:
                $pubIdSuffix = PKPString::regexp_replace('/[^-._;()\/:A-Za-z0-9]/', '', PKPString::strtolower($context->getAcronym($context->getPrimaryLocale())));

                if ($submission) {
                    $pubIdSuffix .= '.' . $submission->getId();
                }

                if ($chapter) {
                    $pubIdSuffix .= '.c' . $chapter->getId();
                }

                if ($representation) {
                    $pubIdSuffix .= '.' . $representation->getId();
                }

                if ($submissionFile) {
                    $pubIdSuffix .= '.' . $submissionFile->getId();
                }
        }
        if (empty($pubIdSuffix)) {
            return null;
        }

        // Costruct the pub id from prefix and suffix.
        $pubId = $this->constructPubId($pubIdPrefix, $pubIdSuffix, $contextId);

        return $pubId;
    }

    /**
     * @copydoc PKPPubIdPlugin::getDAOs()
     */
    public function getDAOs()
    {
        return array_merge(parent::getDAOs(), ['Chapter' => DAORegistry::getDAO('ChapterDAO')]);
    }

    /**
     * @copydoc PKPPubIdPlugin::checkDuplicate()
     */
    public function checkDuplicate($pubId, $pubObjectType, $excludeId, $contextId)
    {
        foreach ($this->getPubObjectTypes() as $type => $fqcn) {
            if ($type === 'Chapter') {
                $excludeTypeId = $type === $pubObjectType ? $excludeId : null;
                if (DAORegistry::getDAO('ChapterDAO')->pubIdExists($this->getPubIdType(), $pubId, $excludeTypeId, $contextId)) {
                    return false;
                }
            }
        }

        return true;
    }
}
