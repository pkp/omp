<?php

/**
 * @defgroup section Section
 * Implements series.
 */

/**
 * @file classes/section/Section.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Section
 *
 * @ingroup section
 *
 * @see DAO
 *
 * @brief Basic class describing a series.
*/

namespace APP\section;

use APP\core\Application;
use APP\facades\Repo;
use PKP\context\SubEditorsDAO;
use PKP\core\PKPString;
use PKP\db\DAORegistry;
use stdClass;

class Section extends \PKP\section\PKPSection
{
    /**
     * Get localized title of series.
     */
    public function getLocalizedTitle(bool $includePrefix = true): string
    {
        $title = $this->getLocalizedData('title');
        if ($includePrefix) {
            $title = $this->getLocalizedPrefix() . ' ' . $title;
        }
        return $title;
    }

    /**
     * Get title of series.
     */
    public function getTitle(?string $locale, bool $includePrefix = true): string|array|null
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
     * Get the series full title (with title + prefix and subtitle).
     */
    public function getLocalizedFullTitle(): string
    {
        $fullTitle = $this->getLocalizedTitle();
        if ($subtitle = $this->getLocalizedSubtitle()) {
            $fullTitle = PKPString::concatTitleFields([$fullTitle, $subtitle]);
        }
        return $fullTitle;
    }

    /**
     * Get localized prefix of series.
     */
    public function getLocalizedPrefix(): ?string
    {
        return $this->getLocalizedData('prefix');
    }

    /**
     * Get prefix of series.
     */
    public function getPrefix(?string $locale): string|array|null
    {
        return $this->getData('prefix', $locale);
    }

    /**
     * Set prefix of series.
     */
    public function setPrefix(string|array $prefix, ?string $locale = null): void
    {
        $this->setData('prefix', $prefix, $locale);
    }

    /**
     * Get localized subtitle of series
     */
    public function getLocalizedSubtitle(): ?string
    {
        return $this->getLocalizedData('subtitle');
    }

    /**
     * Get subtitle of series
     */
    public function getSubtitle(?string $locale): string|array|null
    {
        return $this->getData('subtitle', $locale);
    }

    /**
     * Set subtitle of series
     */
    public function setSubtitle(string|array $subtitle, ?string $locale = null): void
    {
        $this->setData('subtitle', $subtitle, $locale);
    }

    /**
     * Get the featured flag.
     */
    public function getFeatured(): bool
    {
        return $this->getData('featured');
    }

    /**
     * Set the featured flag.
     */
    public function setFeatured(bool $featured): void
    {
        $this->setData('featured', $featured);
    }

    /**
     * Get the image.
     */
    public function getImage(): ?array
    {
        return $this->getData('image');
    }

    /**
     * Set the image.
     */
    public function setImage(array $image): void
    {
        $this->setData('image', $image);
    }

    /**
     * Get online ISSN.
     */
    public function getOnlineISSN(): ?string
    {
        return $this->getData('onlineIssn');
    }

    /**
     * Set online ISSN.
     */
    public function setOnlineISSN(?string $onlineIssn): void
    {
        $this->setData('onlineIssn', $onlineIssn);
    }

    /**
     * Get print ISSN.
     */
    public function getPrintISSN(): ?string
    {
        return $this->getData('printIssn');
    }

    /**
     * Get ID of primary review form.
     */
    public function getReviewFormId(): ?int
    {
        return $this->getData('reviewFormId');
    }

    /**
     * Set ID of primary review form.
     */
    public function setReviewFormId(?int $reviewFormId): void
    {
        $this->setData('reviewFormId', $reviewFormId);
    }

    /**
     * Set print ISSN.
     */
    public function setPrintISSN(?string $printIssn): void
    {
        $this->setData('printIssn', $printIssn);
    }

    /**
     * Get the option how the books in this series should be sorted,
     * in the form: sortBy-sortDir.
     */
    public function getSortOption(): ?string
    {
        return $this->getData('sortOption');
    }

    /**
     * Set the option how the books in this series should be sorted,
     * in the form: sortBy-sortDir.
     */
    public function setSortOption(?string $sortOption): void
    {
        $this->setData('sortOption', $sortOption);
    }

    /**
     * Get section path.
     *
     * @return string
     *
     * @deprecated 3.6
     */
    public function getPath()
    {
        return $this->getData('urlPath');
    }

    /**
     * Set section path.
     *
     * @param string $path
     *
     * @deprecated 3.6
     */
    public function setPath($path)
    {
        return $this->setData('urlPath', $path);
    }

    /**
     * Returns a string with the full name of all series
     * editors, separated by a comma.
     *
     */
    public function getEditorsString(): string
    {
        $subEditorsDao = DAORegistry::getDAO('SubEditorsDAO'); /** @var SubEditorsDAO $subEditorsDao */
        $assignments = $subEditorsDao->getBySubmissionGroupIds([$this->getId()], Application::ASSOC_TYPE_SECTION, $this->getData('contextId'));
        $editors = Repo::user()
            ->getCollector()
            ->filterByUserIds(
                $assignments
                    ->map(fn (stdClass $assignment) => $assignment->userId)
                    ->filter()
                    ->toArray()
            )
            ->getMany();

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
}
