<?php

/**
 * @file classes/plugins/PubIdPlugin.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PubIdPlugin
 *
 * @ingroup plugins
 *
 * @brief Public identifiers plugins common functions
 */

namespace APP\plugins;

use APP\facades\Repo;
use APP\monograph\Chapter;
use APP\monograph\ChapterDAO;
use APP\submission\Submission;
use Illuminate\Support\Str;
use PKP\context\Context;
use PKP\core\DataObject;
use PKP\db\DAORegistry;
use PKP\plugins\PKPPubIdPlugin;
use PKP\submission\Representation;
use PKP\submissionFile\SubmissionFile;

abstract class PubIdPlugin extends PKPPubIdPlugin
{
    //
    // Protected template methods from PKPPubIdPlugin
    //
    /**
     * @copydoc PKPPubIdPlugin::getPubObjectTypes()
     */
    public function getPubObjectTypes()
    {
        $pubObjectTypes = parent::getPubObjectTypes();
        $pubObjectTypes['Chapter'] = Chapter::class;
        return $pubObjectTypes;
    }

    /**
     * Get the public identifier.
     *
     * @param object $pubObject
     * 	Publication, Representation, SubmissionFile, Chapter
     *
     * @return string
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
        $submission = null;
        // Publication is actually handled differently now, but keep it here however for now.
        $publication = ($pubObjectType == 'Publication' ? $pubObject : null);
        $representation = ($pubObjectType == 'Representation' ? $pubObject : null);
        $submissionFile = ($pubObjectType == 'SubmissionFile' ? $pubObject : null);
        $chapter = ($pubObjectType == 'Chapter' ? $pubObject : null);

        // Retrieve the submission.
        if (is_a($pubObject, 'Chapter') || is_a($pubObject, 'Representation')) {
            $publication = Repo::publication()->get($pubObject->getData('publicationId'));
            $submission = Repo::submission()->get($publication->getData('submissionId'));
        } else { // Publication or SubmissionFile
            $submission = Repo::submission()->get($pubObject->getData('submissionId'));
        }
        if (!$submission) {
            return null;
        }
        // Now we can identify the context.
        $contextId = $submission->getData('contextId');

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

                $pubIdSuffix = $this::generateCustomPattern($context, $pubIdSuffix, $pubObject, $submission, $chapter, $representation, $submissionFile);

                break;

            default:
                $pubIdSuffix = $this::generateDefaultPattern($context, $submission, $chapter, $representation, $submissionFile);
        }
        if (empty($pubIdSuffix)) {
            return null;
        }

        // Construct the pub id from prefix and suffix.
        $pubId = $this->constructPubId($pubIdPrefix, $pubIdSuffix, $contextId);

        return $pubId;
    }

    /**
     * Generate the default, semantic-based pub-id suffix pattern
     *
     */
    public static function generateDefaultPattern(
        Context $context,
        ?Submission $submission = null,
        ?Chapter $chapter = null,
        ?Representation $representation = null,
        ?SubmissionFile $submissionFile = null
    ): string {
        $pubIdSuffix = preg_replace('/[^-._;()\/A-Za-z0-9]/', '', Str::lower($context->getAcronym($context->getPrimaryLocale())));

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

        return $pubIdSuffix;
    }

    /**
     * Generate the custom, user-defined pub-id suffix pattern
     *
     */
    public static function generateCustomPattern(
        Context $context,
        string $pubIdSuffix,
        DataObject $pubObject,
        ?Submission $submission = null,
        ?Chapter $chapter = null,
        ?Representation $representation = null,
        ?SubmissionFile $submissionFile = null
    ): string {
        // %p - press initials
        $pubIdSuffix = preg_replace('/%p/', preg_replace('/[^-._;()\/A-Za-z0-9]/u', '', Str::lower($context->getAcronym($context->getPrimaryLocale()))), $pubIdSuffix);
        /** @var Chapter $pubObject */
        // %x - custom identifier
        if ($pubObject->getStoredPubId('publisher-id')) {
            $pubIdSuffix = preg_replace('/%x/', $pubObject->getStoredPubId('publisher-id'), $pubIdSuffix);
        }

        if ($submission) {
            // %m - monograph id
            $pubIdSuffix = preg_replace('/%m/', $submission->getId(), $pubIdSuffix);
        }

        if ($chapter) {
            // %c - chapter id
            $pubIdSuffix = preg_replace('/%c/', $chapter->getId(), $pubIdSuffix);
        }

        if ($representation) {
            // %f - publication format id
            $pubIdSuffix = preg_replace('/%f/', $representation->getId(), $pubIdSuffix);
        }

        if ($submissionFile) {
            // %s - file id
            $pubIdSuffix = preg_replace('/%s/', $submissionFile->getId(), $pubIdSuffix);
        }

        return $pubIdSuffix;
    }

    /**
     * @copydoc PKPPubIdPlugin::getDAOs()
     */
    public function getDAOs()
    {
        return array_merge(parent::getDAOs(), [DAORegistry::getDAO('ChapterDAO')]);
    }

    /**
     * @copydoc PKPPubIdPlugin::checkDuplicate()
     */
    public function checkDuplicate($pubId, $pubObjectType, $excludeId, int $contextId)
    {
        /** @var ChapterDAO */
        $chapterDao = DAORegistry::getDAO('ChapterDAO');
        foreach ($this->getPubObjectTypes() as $type => $fqcn) {
            if ($type === 'Chapter') {
                $excludeTypeId = $type === $pubObjectType ? $excludeId : null;
                if ($chapterDao->pubIdExists($this->getPubIdType(), $pubId, $excludeTypeId, $contextId)) {
                    return false;
                }
            }
        }

        return parent::checkDuplicate($pubId, $pubObjectType, $excludeId, $contextId);
    }
}
