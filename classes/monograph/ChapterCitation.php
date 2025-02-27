<?php

/**
 * @file classes/monograph/ChapterCitation.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2000-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterCitation
 *
 * @ingroup monograph
 *
 * @see ChapterCitationDAO
 *
 * @brief Describes a monograph chapter's citation
 */

namespace APP\monograph;

class ChapterCitation extends \PKP\core\DataObject
{
    /**
     * Constructor.
     *
     * @param string $rawCitation an unparsed citation string
     */
    public function __construct(?string $rawCitation = null)
    {
        parent::__construct();
        $this->setRawCitation($rawCitation);
    }

    /**
     * Replace URLs through HTML links, if the citation does not already contain HTML links
     *
     * @return string
     */
    public function getCitationWithLinks()
    {
        $citation = $this->getRawCitation();
        if (stripos($citation, '<a href=') === false) {
            $citation = preg_replace_callback(
                '#(http|https|ftp)://[\d\w\.-]+\.[\w\.]{2,6}[^\s\]\[\<\>]*/?#',
                function ($matches) {
                    $trailingDot = in_array($char = substr($matches[0], -1), ['.', ',']);
                    $url = rtrim($matches[0], '.,');
                    return "<a href=\"{$url}\">{$url}</a>" . ($trailingDot ? $char : '');
                },
                $citation
            );
        }
        return $citation;
    }

    public function getChapterId(): int
    {
        return $this->getData('chapterId');
    }

    public function setChapterId(int $chapterId)
    {
        $this->setData('chapterId', $chapterId);
    }

    public function getRawCitation(): string
    {
        return $this->getData('rawCitation');
    }

    public function setRawCitation(string $rawCitation)
    {
        $rawCitation = $this->cleanCitationString($rawCitation);
        $this->setData('rawCitation', $rawCitation);
    }

    public function getSequence(): int
    {
        return $this->getData('seq');
    }

    public function setSequence(int $sequence)
    {
        $this->setData('seq', $sequence);
    }

    /**
     * Take a citation string and clean/normalize it
     */
    private function cleanCitationString($citationString)
    {
        $citationString = trim(stripslashes($citationString));
        $citationString = preg_replace('/[\s]+/u', ' ', $citationString);

        return $citationString;
    }
}
