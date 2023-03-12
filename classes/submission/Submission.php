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
 * @ingroup submission
 *
 * @see DAO
 *
 * @brief Class for a Submission.
 */

namespace APP\submission;

use APP\core\Services;

use APP\facades\Repo;
use PKP\submission\PKPSubmission;

class Submission extends PKPSubmission
{
    public const WORK_TYPE_EDITED_VOLUME = 1;
    public const WORK_TYPE_AUTHORED_WORK = 2;

    /**
     * get press id
     *
     * @return int
     *
     * @deprecated 3.2.0.0
     */
    public function getPressId()
    {
        return $this->getContextId();
    }

    /**
     * set press id
     *
     * @param int $pressId
     *
     * @deprecated 3.2.0.0
     */
    public function setPressId($pressId)
    {
        return $this->setContextId($pressId);
    }

    /**
     * Get the series id.
     *
     * @return int
     *
     * @deprecated 3.2.0.0
     */
    public function getSeriesId()
    {
        return $this->getSectionId();
    }

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
     * Set the series id.
     *
     * @param int $id
     *
     * @deprecated 3.2.0.0
     */
    public function setSeriesId($id)
    {
        $publication = $this->getCurrentPublication();
        if ($publication) {
            $publication->setData('seriesId', $id);
        }
    }

    /**
     * Get the position of this monograph within a series.
     *
     * @return string
     *
     * @deprecated 3.2.0.0
     */
    public function getSeriesPosition()
    {
        $publication = $this->getCurrentPublication();
        if (!$publication) {
            return '';
        }
        return $publication->getData('seriesPosition');
    }

    /**
     * Set the series position for this monograph.
     *
     * @param string $seriesPosition
     *
     * @deprecated 3.2.0.0
     */
    public function setSeriesPosition($seriesPosition)
    {
        $publication = $this->getCurrentPublication();
        if ($publication) {
            $publication->setData('seriesPosition', $seriesPosition);
        }
    }

    /**
     * Get the work type (constant in WORK_TYPE_...)
     *
     * @return int
     *
     * @deprecated 3.2.0.0
     */
    public function getWorkType()
    {
        return $this->getData('workType');
    }

    /**
     * Set the work type (constant in WORK_TYPE_...)
     *
     * @param int $workType
     *
     * @deprecated 3.2.0.0
     */
    public function setWorkType($workType)
    {
        $this->setData('workType', $workType);
    }

    /**
     * Get localized supporting agencies array.
     *
     * @return array
     *
     * @deprecated 3.2.0.0
     */
    public function getLocalizedSupportingAgencies()
    {
        $publication = $this->getCurrentPublication();
        if (!$publication) {
            return [];
        }
        return $publication->getLocalizedData('supportingAgencies');
    }

    /**
     * Get supporting agencies.
     *
     * @param string $locale
     *
     * @return array
     *
     * @deprecated 3.2.0.0
     */
    public function getSupportingAgencies($locale)
    {
        $publication = $this->getCurrentPublication();
        if (!$publication) {
            return [];
        }
        return $publication->getData('supportingAgencies', $locale);
    }

    /**
     * Set supporting agencies.
     *
     * @param array $supportingAgencies
     * @param string $locale
     *
     * @deprecated 3.2.0.0
     */
    public function setSupportingAgencies($supportingAgencies, $locale)
    {
        $publication = $this->getCurrentPublication();
        if ($publication) {
            $publication->setData('seriesPosition', $supportingAgencies, $locale);
        }
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
        $context = Services::get('context')->get($this->getData('contextId'));
        $fieldValue = null; // Scrutinizer
        switch ($field) {
            case PERMISSIONS_FIELD_LICENSE_URL:
                $fieldValue = $context->getData('licenseUrl');
                break;
            case PERMISSIONS_FIELD_COPYRIGHT_HOLDER:
                switch ($context->getData('copyrightHolderType')) {
                    case 'author':
                        if (!$publication) {
                            $publication = $this->getCurrentPublication();
                        }
                        $authorUserGroups = Repo::userGroup()->getCollector()->filterByRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])->filterByContextIds([$context->getId()])->getMany();
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
            case PERMISSIONS_FIELD_COPYRIGHT_YEAR:
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
     * get cover page server-side file name
     *
     * @return string
     *
     * @deprecated 3.2.0.0
     */
    public function getCoverImage()
    {
        $publication = $this->getCurrentPublication();
        if (!$publication) {
            return '';
        }
        return $publication->getData('coverImage');
    }

    /**
     * get cover page alternate text
     *
     * @return string
     *
     * @deprecated 3.2.0.0
     */
    public function getCoverImageAltText()
    {
        $publication = $this->getCurrentPublication();
        if (!$publication) {
            return '';
        }
        $coverImage = $publication->getData('coverImage');
        return empty($coverImage['altText']) ? '' : $coverImage['altText'];
    }

    /**
     * Get a string indicating all authors or, if it is an edited volume, editors.
     *
     * @param bool $preferred If the preferred public name should be used, if exist
     *
     * @return string
     *
     * @deprecated 3.2.0.0
     */
    public function getAuthorOrEditorString($preferred = true)
    {
        if ($this->getWorkType() != self::WORK_TYPE_EDITED_VOLUME) {
            $userGroupIds = Repo::author()->getSubmissionAuthors($this, true)
                ->pluck('userGroupId')
                ->unique()
                ->toArray();

            $userGroups = Repo::userGroup()->getCollector()
                ->filterByUserIds($userGroupIds)
                ->getMany();

            return $this->getCurrentPublication()->getAuthorString($userGroups);
        }

        return $this->getCurrentPublication()->getEditorString();
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
    define('WORK_TYPE_EDITED_VOLUME', Submission::WORK_TYPE_EDITED_VOLUME);
    define('WORK_TYPE_AUTHORED_WORK', Submission::WORK_TYPE_AUTHORED_WORK);
}
