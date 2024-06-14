<?php

/**
 * @file classes/monograph/Chapter.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Chapter
 *
 * @ingroup monograph
 *
 * @see ChapterDAO
 *
 * @brief Describes a monograph chapter (or section)
 */

namespace APP\monograph;

use APP\facades\Repo;
use Illuminate\Support\LazyCollection;
use PKP\core\PKPString;
use PKP\db\DAORegistry;

class Chapter extends \PKP\core\DataObject
{
    //
    // Get/set methods
    //

    /**
     * Get the combined title and subtitle for all locales
     */
    public function getFullTitles(): array
    {
        $titles = (array) $this->getData('title');
        $fullTitles = [];
        foreach ($titles as $locale => $title) {
            if (!$title) {
                continue;
            }
            $fullTitles[$locale] = $this->getLocalizedFullTitle($locale);
        }
        return $fullTitles;
    }

    /**
     * Get the chapter full title (with title and subtitle).
     */
    public function getLocalizedFullTitle(?string $preferredLocale = null): string
    {
        $title = $this->getLocalizedData('title', $preferredLocale);
        $subtitle = $this->getLocalizedData('subtitle', $preferredLocale);
        if ($subtitle) {
            return PKPString::concatTitleFields([$title, $subtitle]);
        }
        return $title;
    }

    /**
     * Get localized title of a chapter.
     */
    public function getLocalizedTitle(): string
    {
        return $this->getLocalizedData('title');
    }

    /**
     * Get title of chapter (primary locale)
     */
    public function getTitle(?string $locale = null): string|array
    {
        return $this->getData('title', $locale);
    }

    /**
     * Set title of chapter
     */
    public function setTitle(string|array $title, ?string $locale = null): void
    {
        $this->setData('title', $title, $locale);
    }

    /**
     * Get localized subtitle of a chapter.
     */
    public function getLocalizedSubtitle(): ?string
    {
        return $this->getLocalizedData('subtitle');
    }

    /**
     * Get sub title of chapter (primary locale)
     */
    public function getSubtitle(?string $locale = null): string|array|null
    {
        return $this->getData('subtitle', $locale);
    }

    /**
     * Set subtitle of chapter
     */
    public function setSubtitle(string|array $subtitle, ?string $locale = null): void
    {
        $this->setData('subtitle', $subtitle, $locale);
    }

    /**
     * Get sequence of chapter.
     */
    public function getSequence(): float
    {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of chapter.
     */
    public function setSequence(float $sequence): void
    {
        $this->setData('sequence', $sequence);
    }

    /**
     * Get all authors of this chapter.
     */
    public function getAuthors(): LazyCollection
    {
        return Repo::author()->getCollector()
            ->filterByChapterIds([$this->getId()])
            ->filterByPublicationIds([$this->getData('publicationId')])
            ->getMany();
    }

    /**
     * Get the author names for this chapter and return them as a string.
     *
     * @param $preferred If the preferred public name should be used, if exist
     */
    public function getAuthorNamesAsString(bool $preferred = true): string
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
     * @param $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     *
     * @return string
     */
    public function getStoredPubId(string $pubIdType)
    {
        if ($pubIdType === 'doi') {
            return $this->getDoi();
        }
        return $this->getData('pub-id::' . $pubIdType);
    }

    /**
     * Set the stored public ID of the chapter.
     *
     * @param $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     */
    public function setStoredPubId(string $pubIdType, string $pubId): void
    {
        if ($pubIdType == 'doi') {
            if ($doiObject = $this->getData('doiObject')) {
                Repo::doi()->edit($doiObject, ['doi' => $pubId]);
            } else {
                $publication = Repo::publication()->get($this->getData('publicationId'));
                $submission = Repo::submission()->get($publication->getId());
                $contextId = $submission->getData('contextId');

                $newDoiObject = Repo::doi()->newDataObject(
                    [
                        'doi' => $pubId,
                        'contextId' => $contextId
                    ]
                );
                $doiId = Repo::doi()->add($newDoiObject);
                $this->setData('doiId', $doiId);
            }
        } else {
            $this->setData('pub-id::' . $pubIdType, $pubId);
        }
    }

    /**
     * @copydoc DataObject::getDAO()
     */
    public function getDAO(): ChapterDAO
    {
        return DAORegistry::getDAO('ChapterDAO');
    }

    /**
     * Get abstract of chapter (primary locale)
     */
    public function getAbstract(?string $locale = null): string|array
    {
        return $this->getData('abstract', $locale);
    }

    /**
     * Set abstract of chapter
     */
    public function setAbstract(string|array $abstract, ?string $locale = null): void
    {
        $this->setData('abstract', $abstract, $locale);
    }

    /**
     * Get localized abstract of a chapter.
     */
    public function getLocalizedAbstract(): ?string
    {
        return $this->getLocalizedData('abstract');
    }

    /**
     * get date published
     */
    public function getDatePublished(): ?string
    {
        return $this->getData('datePublished');
    }

    /**
     * set date published
     */
    public function setDatePublished(?string $datePublished): void
    {
        $this->setData('datePublished', $datePublished);
    }

    /**
     * get pages
     */
    public function getPages(): ?string
    {
        return $this->getData('pages');
    }

    /**
     * set pages
     */
    public function setPages(?string $pages)
    {
        $this->setData('pages', $pages);
    }

    /**
     * Get source chapter id of chapter.
     */
    public function getSourceChapterId(): ?int
    {
        if (!$this->getData('sourceChapterId')) {
            $this->setSourceChapterId($this->getId());
        }
        return $this->getData('sourceChapterId');
    }

    /**
     * Set source chapter id of chapter.
     *
     */
    public function setSourceChapterId(?int $sourceChapterId): void
    {
        $this->setData('sourceChapterId', $sourceChapterId);
    }

    /**
     * Is a landing page enabled or disabled.
     */
    public function isPageEnabled(): ?bool
    {
        return $this->getData('isPageEnabled');
    }

    /**
     * Enable or disable a landing page.
     */
    public function setPageEnabled(?bool $enable): void
    {
        $this->setData('isPageEnabled', $enable);
    }

    /**
     * Returns current DOI
     */
    public function getDoi(): ?string
    {
        $doiObject = $this->getData('doiObject');
        return $doiObject?->getData('doi');
    }

    /**
     * get license url
     */
    public function getLicenseUrl(): ?string
    {
        return $this->getData('licenseUrl');
    }

    /**
     * set license url
     */
    public function setLicenseUrl(?string $licenseUrl): void
    {
        $this->setData('licenseUrl', $licenseUrl);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\monograph\Chapter', '\Chapter');
}
