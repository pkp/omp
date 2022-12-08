<?php

/**
 * @file classes/press/Series.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Series
 * @ingroup press
 *
 * @see SeriesDAO
 *
 * @brief Describes basic series properties.
 */

namespace APP\press;

use APP\core\Application;
use PKP\context\PKPSection;
use PKP\context\SubEditorsDAO;
use PKP\db\DAORegistry;

class Series extends PKPSection
{
    /**
     * Get ID of press.
     *
     * @return int
     */
    public function getPressId()
    {
        return $this->getContextId();
    }

    /**
     * Set ID of press.
     *
     * @param int $pressId
     */
    public function setPressId($pressId)
    {
        return $this->setContextId($pressId);
    }

    /**
     * Get localized title of section.
     *
     * @param bool $includePrefix
     *
     * @return string
     */
    public function getLocalizedTitle($includePrefix = true)
    {
        $title = $this->getLocalizedData('title');
        if ($includePrefix) {
            $title = $this->getLocalizedPrefix() . ' ' . $title;
        }
        return $title;
    }

    /**
     * Get title of section.
     *
     * @param string $locale
     * @param bool $includePrefix
     *
     * @return string
     */
    public function getTitle($locale, $includePrefix = true)
    {
        $title = $this->getData('title', $locale);
        if ($includePrefix) {
            if (is_array($title)) {
                foreach ($title as $locale => $currentTitle) {
                    $title[$locale] = $this->getPrefix($locale) . ' ' . $currentTitle;
                }
            } else {
                $title = $this->getPrefix($locale) . ' ' . $title;
            }
        }
        return $title;
    }

    /**
     * Get the series full title (with title and subtitle).
     *
     * @return string
     */
    public function getLocalizedFullTitle()
    {
        $fullTitle = $this->getLocalizedTitle();

        if ($subtitle = $this->getLocalizedSubtitle()) {
            $fullTitle = PKPString::concatTitleFields([$fullTitle, $subtitle]);
        }

        return $fullTitle;
    }

    /**
     * Get localized prefix for the series.
     *
     * @return string
     */
    public function getLocalizedPrefix()
    {
        return $this->getLocalizedData('prefix');
    }

    /**
     * Get prefix of series.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getPrefix($locale)
    {
        return $this->getData('prefix', $locale);
    }

    /**
     * Set prefix of series.
     *
     * @param string $prefix
     * @param string $locale
     */
    public function setPrefix($prefix, $locale)
    {
        return $this->setData('prefix', $prefix, $locale);
    }

    /**
     * Get the localized version of the subtitle
     *
     * @return string
     */
    public function getLocalizedSubtitle()
    {
        return $this->getLocalizedData('subtitle');
    }

    /**
     * Get the subtitle for a given locale
     *
     * @param string $locale
     *
     * @return string
     */
    public function getSubtitle($locale)
    {
        return $this->getData('subtitle', $locale);
    }

    /**
     * Set the subtitle for a locale
     *
     * @param string $subtitle
     * @param string $locale
     */
    public function setSubtitle($subtitle, $locale)
    {
        return $this->setData('subtitle', $subtitle, $locale);
    }

    /**
     * Get path to series (in URL).
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getData('path');
    }

    /**
     * Set path to series (in URL).
     *
     * @param string $path
     */
    public function setPath($path)
    {
        return $this->setData('path', $path);
    }

    /**
     * Get series description.
     *
     * @return string
     */
    public function getLocalizedDescription()
    {
        return $this->getLocalizedData('description');
    }

    /**
     * Get series description.
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->getData('description', $locale);
    }

    /**
     * Set series description.
     *
     * @param string
     */
    public function setDescription($description, $locale)
    {
        $this->setData('description', $description, $locale);
    }

    /**
     * Get the featured flag.
     *
     * @return bool
     */
    public function getFeatured()
    {
        return $this->getData('featured');
    }

    /**
     * Set the featured flag.
     *
     * @param bool $featured
     */
    public function setFeatured($featured)
    {
        $this->setData('featured', $featured);
    }

    /**
     * Get the image.
     *
     * @return array
     */
    public function getImage()
    {
        return $this->getData('image');
    }

    /**
     * Set the image.
     *
     * @param array $image
     */
    public function setImage($image)
    {
        return $this->setData('image', $image);
    }

    /**
     * Get online ISSN.
     *
     * @return string
     */
    public function getOnlineISSN()
    {
        return $this->getData('onlineIssn');
    }

    /**
     * Set online ISSN.
     *
     * @param string $onlineIssn
     */
    public function setOnlineISSN($onlineIssn)
    {
        return $this->setData('onlineIssn', $onlineIssn);
    }

    /**
     * Get print ISSN.
     *
     * @return string
     */
    public function getPrintISSN()
    {
        return $this->getData('printIssn');
    }

    /**
     * Set print ISSN.
     *
     * @param string $printIssn
     */
    public function setPrintISSN($printIssn)
    {
        return $this->setData('printIssn', $printIssn);
    }

    /**
     * Get the option how the books in this series should be sorted,
     * in the form: concat(sortBy, sortDir).
     *
     * @return string
     */
    public function getSortOption()
    {
        return $this->getData('sortOption');
    }

    /**
     * Set the option how the books in this series should be sorted,
     * in the form: concat(sortBy, sortDir).
     *
     * @param string $sortOption
     */
    public function setSortOption($sortOption)
    {
        return $this->setData('sortOption', $sortOption);
    }

    /**
     * Returns a string with the full name of all series
     * editors, separated by a comma.
     *
     * @return string
     */
    public function getEditorsString()
    {
        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /** @var SubEditorsDAO $subEditorsDao */
        $editors = $subEditorsDao->getBySubmissionGroupIds([$this->getId()], Application::ASSOC_TYPE_SECTION, $this->getPressId());

        $separator = ', ';
        $str = '';

        foreach ($editors as $editor) {
            if (!empty($str)) {
                $str .= $separator;
            }

            $str .= $editor->getFullName();
            $editor = null;
        }

        return $str;
    }

    /**
     * Return boolean indicating if series should be inactivated.
     *
     * @return int
     */
    public function getIsInactive()
    {
        return $this->getData('isInactive');
    }

    /**
     * Set if series should be inactivated.
     *
     * @param int $isInactive
     */
    public function setIsInactive($isInactive)
    {
        $this->setData('isInactive', $isInactive);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\Series', '\Series');
}
