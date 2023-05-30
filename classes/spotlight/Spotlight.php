<?php

/**
 * @file classes/spotlight/Spotlight.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Spotlight
 *
 * @ingroup spotlight
 *
 * @see SpotlightDAO
 *
 * @brief Basic class describing a spotlight.
 */

namespace APP\spotlight;

use APP\facades\Repo;
use APP\section\Section;
use APP\submission\Submission;
use PKP\core\DataObject;

class Spotlight extends DataObject
{
    // type constants for spotlights
    public const SPOTLIGHT_TYPE_BOOK = 3;
    public const SPOTLIGHT_TYPE_SERIES = 4;
    public const MAX_SPOTLIGHTS_VISIBLE = 3;

    //
    // Get/set methods
    //

    /**
     * Get assoc ID for this spotlight.
     *
     * @return int
     */
    public function getAssocId()
    {
        return $this->getData('assocId');
    }

    /**
     * Set assoc ID for this spotlight.
     *
     * @param int $assocId
     */
    public function setAssocId($assocId)
    {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get assoc type for this spotlight.
     *
     * @return int
     */
    public function getAssocType()
    {
        return $this->getData('assocType');
    }

    /**
     * Set assoc type for this spotlight.
     *
     * @param int $assocType
     */
    public function setAssocType($assocType)
    {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get the press id for this spotlight.
     *
     * @return int
     */
    public function getPressId()
    {
        return $this->getData('pressId');
    }

    /**
     * Set press Id for this spotlight.
     *
     * @param int $pressId
     */
    public function setPressId($pressId)
    {
        return $this->setData('pressId', $pressId);
    }

    /**
     * Get localized spotlight title
     *
     * @return string
     */
    public function getLocalizedTitle()
    {
        return $this->getLocalizedData('title');
    }

    /**
     * Get spotlight title.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->getData('title', $locale);
    }

    /**
     * Set spotlight title.
     *
     * @param string $title
     * @param string $locale
     */
    public function setTitle($title, $locale)
    {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get localized full description
     *
     * @return string
     */
    public function getLocalizedDescription()
    {
        return $this->getLocalizedData('description');
    }

    /**
     * Get spotlight description.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->getData('description', $locale);
    }

    /**
     * Set spotlight description.
     *
     * @param string $description
     * @param string $locale
     */
    public function setDescription($description, $locale)
    {
        return $this->setData('description', $description, $locale);
    }

    /**
     * Fetch a plain text (localized) string for this Spotlight type
     *
     * @return string
     */
    public function getLocalizedType()
    {
        $spotlightTypes = [
            self::SPOTLIGHT_TYPE_BOOK => __('grid.content.spotlights.form.type.book'),
            self::SPOTLIGHT_TYPE_SERIES => __('series.series'),
        ];

        return $spotlightTypes[$this->getAssocType()];
    }

    /**
     * Returns the associated item with this spotlight.
     *
     * @return Section|Submission|null
     */
    public function getSpotlightItem()
    {
        switch ($this->getAssocType()) {
            case self::SPOTLIGHT_TYPE_BOOK:
                return Repo::submission()->get($this->getAssocId());
            case self::SPOTLIGHT_TYPE_SERIES:
                return Repo::section()->get($this->getAssocId(), $this->getPressId());
            default:
                assert(false);
                break;
        }
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\spotlight\Spotlight', '\Spotlight');
    foreach ([
        'SPOTLIGHT_TYPE_BOOK',
        'SPOTLIGHT_TYPE_SERIES',
        'MAX_SPOTLIGHTS_VISIBLE',
    ] as $constantName) {
        define($constantName, constant('\Spotlight::' . $constantName));
    }
}
