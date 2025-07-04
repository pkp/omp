<?php

/**
 * @defgroup submission Monographs
 */

/**
 * @file classes/submission/Submission.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Submission
 *
 * @ingroup submission
 *
 * @see DAO
 *
 * @brief Class for a Submission.
 */

namespace APP\submission;

use APP\publication\Publication;
use PKP\submission\PKPSubmission;
use PKP\userGroup\UserGroup;

class Submission extends PKPSubmission
{
    public const WORK_TYPE_EDITED_VOLUME = 1;
    public const WORK_TYPE_AUTHORED_WORK = 2;

    /**
     * @see Submission::getSectionId()
     * @deprecated 3.2.0.0
     */
    public function getSectionId()
    {
        $publication = $this->getCurrentPublication();
        if (!$publication) {
            return 0;
        }
        return $publication->getData('seriesId');
    }

    /**
     * Get the value of a license field from the containing context.
     *
     * @param string $locale Locale code
     * @param int $field PERMISSIONS_FIELD_...
     * @param Publication $publication
     *
     * @return string|array|null
     */
    public function _getContextLicenseFieldValue($locale, $field, $publication = null)
    {
        $context = app()->get('context')->get($this->getData('contextId'));
        $fieldValue = null; // Scrutinizer
        switch ($field) {
            case static::PERMISSIONS_FIELD_LICENSE_URL:
                $fieldValue = $context->getData('licenseUrl');
                break;
            case static::PERMISSIONS_FIELD_COPYRIGHT_HOLDER:
                switch ($context->getData('copyrightHolderType')) {
                    case 'author':
                        if (!$publication) {
                            $publication = $this->getCurrentPublication();
                        }

                        $authorUserGroups = UserGroup::withRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])
                            ->withContextIds([$context->getId()])
                            ->get();

                        $fieldValue = [$context->getPrimaryLocale() => $publication->getAuthorString($authorUserGroups)];
                        break;
                    case 'context':
                    case null:
                        $fieldValue = $context->getName(null);
                        break;
                    default:
                        $fieldValue = $context->getData('copyrightHolderOther');
                        break;
                }
                break;
            case static::PERMISSIONS_FIELD_COPYRIGHT_YEAR:
                $fieldValue = date('Y');
                if (!$publication) {
                    $publication = $this->getCurrentPublication();
                }
                if ($publication->getData('datePublished')) {
                    $fieldValue = date('Y', strtotime($publication->getData('datePublished')));
                }
                break;
            default: assert(false);
        }

        // Return the fetched license field
        if ($locale === null) {
            return $fieldValue;
        }
        if (isset($fieldValue[$locale])) {
            return $fieldValue[$locale];
        }
        return null;
    }

    /**
     * get enableChapterPublicationDates status
     *
     * @return int
     */
    public function getEnableChapterPublicationDates()
    {
        return $this->getData('enableChapterPublicationDates');
    }

    /**
     * set  enableChapterPublicationDates status
     *
     * @param int $enableChapterPublicationDates
     */
    public function setEnableChapterPublicationDates($enableChapterPublicationDates)
    {
        $this->setData('enableChapterPublicationDates', $enableChapterPublicationDates);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\submission\Submission', '\Submission');
}
