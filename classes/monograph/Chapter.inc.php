<?php

/**
 * @file classes/monograph/Chapter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Chapter
 * @ingroup monograph
 *
 * @see ChapterDAO
 *
 * @brief Describes a monograph chapter (or section)
 */

namespace APP\monograph;

use APP\i18n\AppLocale;
use PKP\core\PKPString;

use PKP\db\DAORegistry;
use APP\facades\Repo;
use Illuminate\Support\LazyCollection;

class Chapter extends \PKP\core\DataObject
{
    //
    // Get/set methods
    //

    /**
     * Get localized data for this object.
     *
     * It selects the locale in the following order:
     * - $preferredLocale
     * - the user's current locale
     * - the first locale we find data for
     *
     * @todo Chapters should have access to their publication's locale
     *  and should fall back to that after the user's current locale
     *  and before the last fall back to the first data available.
     *
     * @param string $key
     * @param string $preferredLocale
     */
    public function getLocalizedData($key, $preferredLocale = null)
    {
        // 1. Preferred locale
        if ($preferredLocale && $this->getData($key, $preferredLocale)) {
            return $this->getData($key, $preferredLocale);
        }
        // 2. User's current locale
        if (!empty($this->getData($key, AppLocale::getLocale()))) {
            return $this->getData($key, AppLocale::getLocale());
        }
        // 3. The first locale we can find data for
        $data = $this->getData($key, null);
        foreach ((array) $data as $value) {
            if (!empty($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Get the chapter full title (with title and subtitle).
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
     * Get localized title of a chapter.
     */
    public function getLocalizedTitle()
    {
        return $this->getLocalizedData('title');
    }

    /**
     * Get title of chapter (primary locale)
     *
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale = null)
    {
        return $this->getData('title', $locale);
    }

    /**
     * Set title of chapter
     *
     * @param string $title
     * @param string $locale
     */
    public function setTitle($title, $locale = null)
    {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get localized sub title of a chapter.
     */
    public function getLocalizedSubtitle()
    {
        return $this->getLocalizedData('subtitle');
    }

    /**
     * Get sub title of chapter (primary locale)
     *
     * @param string $locale
     *
     * @return string
     */
    public function getSubtitle($locale = null)
    {
        return $this->getData('subtitle', $locale);
    }

    /**
     * Set sub title of chapter
     *
     * @param string $subtitle
     * @param string $locale
     */
    public function setSubtitle($subtitle, $locale = null)
    {
        return $this->setData('subtitle', $subtitle, $locale);
    }

    /**
     * Get sequence of chapter.
     *
     * @return float
     */
    public function getSequence()
    {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of chapter.
     *
     * @param float $sequence
     */
    public function setSequence($sequence)
    {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get all authors of this chapter.
     *
     * @return LazyCollection Iterator of authors
     */
    public function getAuthors()
    {
        return Repo::author()->getMany(
            Repo::author()
                ->getCollector()
                ->filterByChapterIds([$this->getId()])
                ->filterByPublicationIds([$this->getData('publicationId')])
        );
    }

    /**
     * Get the author names for this chapter and return them as a string.
     *
     * @param bool $preferred If the preferred public name should be used, if exist
     *
     * @return string
     */
    public function getAuthorNamesAsString($preferred = true)
    {
        $authorNames = [];
        $authors = $this->getAuthors();
        foreach ($authors as $author) {
            $authorNames[] = $author->getFullName($preferred);
        }
        return join(', ', $authorNames);
    }

    /**
     * Get stored public ID of the chapter.
     *
     * @param string $pubIdType @literal string One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>). @endliteral
     *
     * @return int
     */
    public function getStoredPubId($pubIdType)
    {
        return $this->getData('pub-id::' . $pubIdType);
    }

    /**
     * Set the stored public ID of the chapter.
     *
     * @param string $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     * @param string $pubId
     */
    public function setStoredPubId($pubIdType, $pubId)
    {
        $this->setData('pub-id::' . $pubIdType, $pubId);
    }

    /**
     * @copydoc DataObject::getDAO()
     */
    public function getDAO()
    {
        return DAORegistry::getDAO('ChapterDAO');
    }

    /**
     * Get abstract of chapter (primary locale)
     *
     * @param string $locale
     *
     * @return string
     */
    public function getAbstract($locale = null)
    {
        return $this->getData('abstract', $locale);
    }

    /**
     * Set abstract of chapter
     *
     * @param string $abstract
     * @param string $locale
     */
    public function setAbstract($abstract, $locale = null)
    {
        return $this->setData('abstract', $abstract, $locale);
    }
    /**
     * Get localized abstract of a chapter.
     */
    public function getLocalizedAbstract()
    {
        return $this->getLocalizedData('abstract');
    }

    /**
     * get date published
     *
     * @return date
     */
    public function getDatePublished()
    {
        return $this->getData('datePublished');
    }

    /**
     * set date published
     *
     * @param date $datePublished
     */
    public function setDatePublished($datePublished)
    {
        return $this->setData('datePublished', $datePublished);
    }

    /**
     * get pages
     *
     * @return string
     */
    public function getPages()
    {
        return $this->getData('pages');
    }

    /**
     * set pages
     *
     * @param string $pages
     */
    public function setPages($pages)
    {
        $this->setData('pages', $pages);
    }

    /**
     * Get source chapter id of chapter.
     *
     * @return null|int
     */
    public function getSourceChapterId()
    {
        if(!$this->getData('sourceChapterId')) {
            $this->setSourceChapterId($this->getId());
        }
        return $this->getData('sourceChapterId');
    }

    /**
     * Set source chapter id of chapter.
     *
     * @param null|int $sourceChapterId
     */
    public function setSourceChapterId(?int $sourceChapterId) : void
    {
        $this->setData('sourceChapterId', $sourceChapterId);
    }

    /**
     * Is a landing page enabled or disabled.
     *
     * @return null|int
     */
    public function isPageEnabled()
    {
        return $this->getData('isPageEnabled');
    }

    /**
     * Enable or disable a landing page.
     *
     * @param null|int $enable
     */
    public function setPageEnabled(?int $enable) : void
    {
        $this->setData('isPageEnabled', $enable);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\monograph\Chapter', '\Chapter');
}
