<?php

/**
 * @file classes/codelist/Thema.php
 *
 * Copyright (c) 2026 Simon Fraser University
 * Copyright (c) 2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Thema
 *
 * @brief Loads and searches the Thema subject category controlled vocabulary.
 *
 * Thema is EDItEUR's subject classification scheme for books (https://ns.editeur.org/thema).
 * The code lists are the official EDItEUR XML and are located in locale/{locale}/thema.xml.
 * The Thema version is recorded in the XML header (<VersionNumber>).
 *
 * Only the subject categories are loaded. The EDItEUR file also carries the
 * qualifier lists (place, language, time period, educational purpose, interest
 * age and style), whose codes begin with a digit and belong to different ONIX
 * subject schemes (94 to 99), so they are skipped when the file is parsed. The
 * "DO NOT USE" placeholder codes are all qualifiers and are excluded with them.
 */

namespace APP\codelist;

use Illuminate\Support\Facades\Cache;
use PKP\components\forms\FieldControlledVocab;
use PKP\context\Context;
use PKP\facades\Locale;
use PKP\i18n\interfaces\LocaleInterface;
use RuntimeException;
use XMLReader;

class Thema
{
    /** Value stored in a subject entry's "source" field to mark it as a Thema code. */
    public const SOURCE = 'thema';

    /**
     * Context setting holding the Thema mode: Context::METADATA_DISABLE (off),
     * Context::METADATA_ENABLE (Thema codes offered alongside free text) or
     * Context::METADATA_REQUIRE (only Thema codes accepted).
     */
    public const SETTING = 'thema';

    /** The per-locale data file name. */
    protected const FILENAME = 'thema.xml';

    /** How long to cache the parsed code list (24 hours), in seconds. */
    protected const CACHE_LIFETIME = 60 * 60 * 24;

    /** Subject category codes begin with a letter (some end in a digit, e.g. AKLC1); qualifier codes begin with a digit. */
    protected const SUBJECT_CODE_PATTERN = '/^[A-Z][A-Z0-9]*$/';

    /**
     * Whether Thema is enabled for a context. Being required implies being enabled.
     */
    public static function isEnabled(?Context $context): bool
    {
        return ($context?->getData(self::SETTING) === Context::METADATA_ENABLE) || self::isRequired($context);
    }

    /**
     * Whether the subjects field is restricted to Thema codes.
     */
    public static function isRequired(?Context $context): bool
    {
        return $context?->getData(self::SETTING) === Context::METADATA_REQUIRE;
    }

    /**
     * Whether one category is a broader ancestor of another. Thema categories
     * nest by prefix, so FJ is an ancestor of FJH.
     */
    public static function isAncestorOf(string $ancestor, string $code): bool
    {
        return $code !== $ancestor && str_starts_with($code, $ancestor);
    }

    /**
     * Get every [ancestor, descendant] pair among a set of codes. EDItEUR
     * advises against assigning a category together with one of its ancestors.
     *
     * @return string[][]
     */
    public static function getAncestorConflicts(array $codes): array
    {
        $codes = array_values(array_unique($codes));
        $conflicts = [];
        foreach ($codes as $ancestor) {
            foreach ($codes as $code) {
                if (self::isAncestorOf($ancestor, $code)) {
                    $conflicts[] = [$ancestor, $code];
                }
            }
        }

        return $conflicts;
    }

    /**
     * Validate one locale's subject entries against the Thema rules and return
     * translated error messages. Thema entries must carry a known code and must
     * not be combined with one of their ancestors. When $required is set,
     * entries that are not Thema entries are rejected.
     *
     * @param array $entries Subject entries as submitted: arrays with name/source/identifier, or plain strings
     *
     * @return string[]
     */
    public function getSubjectErrors(array $entries, bool $required, ?string $locale = null): array
    {
        $errors = [];
        $codes = [];
        $hasNonThemaEntry = false;
        foreach ($entries as $entry) {
            $code = (is_array($entry) && ($entry['source'] ?? null) === self::SOURCE) ? ($entry['identifier'] ?? null) : null;
            if (!$code) {
                $hasNonThemaEntry = true;
                continue;
            }
            if (!$this->isValidCode($code, $locale)) {
                $errors[] = __('manager.setup.metadata.subjects.thema.unknownCode', ['code' => $code]);
            } else {
                $codes[] = $code;
            }
        }

        foreach (self::getAncestorConflicts($codes) as [$ancestor, $descendant]) {
            $errors[] = __('manager.setup.metadata.subjects.thema.ancestorConflict', ['ancestor' => $ancestor, 'descendant' => $descendant]);
        }

        if ($required && $hasNonThemaEntry) {
            $errors[] = __('manager.setup.metadata.subjects.thema.invalid');
        }

        return $errors;
    }

    /**
     * Get the full [code => description] map for a locale (cached), falling back
     * to the default locale when no localized file is shipped.
     */
    public function getEntries(?string $locale = null): array
    {
        $filename = $this->getFilename($locale ?? Locale::getLocale());

        return Cache::remember(
            'themaSubjects_' . md5($filename),
            self::CACHE_LIFETIME,
            fn () => $this->parseFile($filename)
        );
    }

    /**
     * Configure a subjects form field for a context that uses Thema. When Thema
     * is required, free-text entries are disallowed and a note saying so is
     * added to the field's guidance. The workflow metadata form ($asTooltip)
     * has its tooltip replaced with the Thema selection guidance plus that note;
     * the submission wizard presents descriptions rather than tooltips, so it
     * keeps its own description and only has the note appended to it. Does
     * nothing when Thema is disabled.
     */
    public static function configureSubjectsField(FieldControlledVocab $field, ?Context $context, bool $asTooltip = true): void
    {
        if (!self::isEnabled($context)) {
            return;
        }

        $required = self::isRequired($context);
        $field->allowCustom = !$required;

        $freeTextNote = $required ? __('manager.setup.metadata.subjects.thema.freeTextNotAllowed') : null;
        if ($asTooltip) {
            $field->tooltip = implode(' ', array_filter([__('manager.setup.metadata.subjects.thema.tooltip'), $freeTextNote]));
        } elseif ($freeTextNote) {
            $field->description = implode(' ', array_filter([$field->description, $freeTextNote]));
        }
    }

    /**
     * Get the Thema issue number (e.g. "1.6") recorded in the header of the code
     * list file for a locale (cached), falling back to the default locale's file.
     * Used as the ONIX SubjectSchemeVersion. Null if the header carries none.
     */
    public function getVersion(?string $locale = null): ?string
    {
        $filename = $this->getFilename($locale ?? Locale::getLocale());

        return Cache::remember(
            'themaVersion_' . md5($filename),
            self::CACHE_LIFETIME,
            fn () => $this->parseVersion($filename)
        );
    }

    /**
     * Search the Thema code list by term against both the description and the
     * code, returning controlled-vocabulary autosuggest entries shaped for the
     * subjects field.
     *
     * @return array<array{name: string, source: string, identifier: string}>
     */
    public function search(string $term, ?string $locale = null, int $limit = 30): array
    {
        $term = trim($term);
        $results = [];

        foreach ($this->getEntries($locale) as $code => $description) {
            if ($term === '' || stripos($description, $term) !== false || stripos($code, $term) !== false) {
                $results[] = [
                    'name' => $description,
                    'source' => self::SOURCE,
                    'identifier' => $code,
                ];
                if (count($results) >= $limit) {
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Determine whether a code is a valid Thema subject category.
     */
    public function isValidCode(string $code, ?string $locale = null): bool
    {
        return array_key_exists($code, $this->getEntries($locale));
    }

    /**
     * Get the path to the Thema XML file for a locale, falling back to the
     * default locale's file when a localized version is not available.
     */
    public function getFilename(string $locale): string
    {
        $localizedFile = "locale/{$locale}/" . self::FILENAME;
        if (Locale::isLocaleValid($locale) && file_exists($localizedFile)) {
            return $localizedFile;
        }

        return 'locale/' . LocaleInterface::DEFAULT_LOCALE . '/' . self::FILENAME;
    }

    /**
     * Read the issue number from a Thema XML file's header. Only the header is
     * read: the <IssueNumber> that precedes <ThemaCodes> is the list's version,
     * whereas each <Code> carries its own <IssueNumber> for when it was added.
     */
    protected function parseVersion(string $filename): ?string
    {
        $reader = new XMLReader();
        if (!@$reader->open($filename)) {
            throw new RuntimeException("Unable to open the Thema code list at {$filename}");
        }

        $version = null;
        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT) {
                continue;
            }
            if ($reader->localName === 'ThemaCodes') {
                break;
            }
            if ($reader->localName === 'IssueNumber') {
                $version = trim($reader->readString()) ?: null;
                break;
            }
        }

        $reader->close();
        return $version;
    }

    /**
     * Convert a Thema XML file into a [code => description] map of subject
     * categories, skipping qualifier codes and entries without a description.
     *
     * Throws when the file cannot be opened so that a missing or unreadable
     * code list is reported rather than cached as an empty list.
     */
    protected function parseFile(string $filename): array
    {
        $entries = [];

        $reader = new XMLReader();
        if (!@$reader->open($filename)) {
            throw new RuntimeException("Unable to open the Thema code list at {$filename}");
        }

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'Code') {
                continue;
            }

            $code = $description = null;
            foreach ($reader->expand()->childNodes as $child) {
                match ($child->nodeName) {
                    'CodeValue' => $code = trim($child->textContent),
                    'CodeDescription' => $description = trim($child->textContent),
                    default => null,
                };
            }

            if (
                $description !== null &&
                $description !== '' &&
                preg_match(self::SUBJECT_CODE_PATTERN, (string) $code)
            ) {
                $entries[$code] = $description;
            }
        }

        $reader->close();
        return $entries;
    }
}
