<?php

/**
 * @file classes/codelist/ONIXCodelistItemDAO.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ONIXCodelistItemDAO
 *
 * @see CodelistItem
 *
 * @brief Parent class for operations involving Codelist objects.
 *
 */

namespace APP\codelist;

use PKP\cache\CacheManager;
use PKP\cache\GenericCache;
use PKP\core\Registry;
use PKP\db\XMLDAO;
use PKP\facades\Locale;
use PKP\file\FileManager;
use PKP\file\TemporaryFileManager;
use PKP\i18n\interfaces\LocaleInterface;
use PKP\plugins\Hook;
use PKP\xslt\XSLTransformer;

class ONIXCodelistItemDAO extends \PKP\db\DAO
{
    /** @var string The name of the codelist we are interested in */
    public string $_list;

    public function &_getCache(?string $locale = null): GenericCache
    {
        $locale ??= Locale::getLocale();
        $cacheName = 'Onix' . $this->getListName() . 'Cache';

        $cache = &Registry::get($cacheName, true, null);
        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                $this->getListName() . '_codelistItems',
                $locale,
                [$this, '_cacheMiss']
            );
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime($this->getFilename($locale))) {
                $cache->flush();
            }
        }

        return $cache;
    }

    public function _cacheMiss(GenericCache $cache, ?string $id): null
    {
        $allCodelistItems = &Registry::get('all' . $this->getListName() . 'CodelistItems', true, null);
        if ($allCodelistItems === null) {
            // Add a locale load to the debug notes.
            $notes = &Registry::get('system.debug.notes');
            $locale = $cache->cacheId ?? Locale::getLocale();
            $filename = $this->getFilename($locale);
            $notes[] = ['debug.notes.codelistItemListLoad', ['filename' => $filename]];

            // Reload locale registry file
            $xmlDao = new XMLDAO();
            $listName = $this->getListName(); // i.e., 'List30'
            $handler = new ONIXParserDOMHandler($listName);

            $temporaryFileManager = new TemporaryFileManager();
            $fileManager = new FileManager();

            // Ensure that the temporary file dir exists
            $tmpDir = $temporaryFileManager->getBasePath();
            if (!file_exists($tmpDir)) {
                mkdir($tmpDir);
            }

            $tmpName = tempnam($tmpDir, 'ONX');
            $xslTransformer = new XSLTransformer();
            $xslTransformer->setParameters(['listName' => $listName]);
            $xslTransformer->setRegisterPHPFunctions(true);

            $xslFile = 'lib/pkp/xml/onixFilter.xsl';
            $filteredXml = $xslTransformer->transform($filename, XSLTransformer::XSL_TRANSFORMER_DOCTYPE_FILE, $xslFile, XSLTransformer::XSL_TRANSFORMER_DOCTYPE_FILE, XSLTransformer::XSL_TRANSFORMER_DOCTYPE_STRING);
            if (!$filteredXml) {
                assert(false);
            }

            $data = null;

            if (is_writeable($tmpName)) {
                $fp = fopen($tmpName, 'wb');
                fwrite($fp, $filteredXml);
                fclose($fp);
                $data = $xmlDao->parseWithHandler($tmpName, $handler);
                $fileManager->deleteByPath($tmpName);
            } else {
                fatalError('misconfigured directory permissions on: ' . $tmpDir);
            }

            // Build array with ($charKey => [stuff])

            if (isset($data[$listName])) {
                foreach ($data[$listName] as $code => $codelistData) {
                    $allCodelistItems[$code] = $codelistData;
                }
            }
            if (is_array($allCodelistItems)) {
                asort($allCodelistItems);
            }

            $cache->setEntireCache($allCodelistItems);
        }
        return null;
    }

    /**
     * Get the filename for the ONIX codelist document. Use a localized
     * version if available, but if not, fall back on the master locale.
     */
    public function getFilename(string $locale): string
    {
        $masterLocale = LocaleInterface::DEFAULT_LOCALE;
        $localizedFile = "locale/{$locale}/ONIX_BookProduct_CodeLists.xsd";
        if (Locale::isLocaleValid($locale) && file_exists($localizedFile)) {
            return $localizedFile;
        }

        // Fall back on the version for the master locale.
        return "locale/{$masterLocale}/ONIX_BookProduct_CodeLists.xsd";
    }

    /**
     * Set the name of the list we want.
     */
    public function setListName(string $list)
    {
        $this->_list = $list;
    }

    /**
     * Get the base node name particular codelist database.
     */
    public function getListName(): string
    {
        return $this->_list;
    }

    /**
     * Get the name of the CodelistItem subclass.
     */
    public function newDataObject(): ONIXCodelistItem
    {
        return new ONIXCodelistItem();
    }

    /**
     * Retrieve an array of all the codelist items.
     */
    public function getCodelistItems(string $list, ?string $locale = null): array
    {
        $this->setListName($list);
        $cache = $this->_getCache($locale);
        $returner = [];
        foreach ($cache->getContents() as $code => $entry) {
            $returner[] = $this->_fromRow($code, $entry);
        }
        return $returner;
    }

    /**
     * Retrieve an array of all codelist codes and values for a given list.
     */
    public function getCodes(string $list, array $codesToExclude = [], ?string $codesFilter = null, ?string $locale = null): array
    {
        $this->setListName($list);
        $cache = $this->_getCache($locale);
        $returner = [];
        $cacheContents = $cache->getContents();
        if ($codesFilter = trim($codesFilter ?? '')) {
            $codesFilter = '/' . implode('|', array_map(fn ($term) => preg_quote($term, '/'), preg_split('/\s+/', $codesFilter))) . '/i';
        }
        if (is_array($cacheContents)) {
            foreach ($cache->getContents() as $code => $entry) {
                if ($code != '') {
                    if (!in_array($code, $codesToExclude) && (!$codesFilter || preg_match($codesFilter, $entry[0]))) {
                        $returner[$code] = $entry[0];
                    }
                }
            }
        }
        return $returner;
    }

    /**
     * Determines if a particular code value is valid for a given list.
     */
    public function codeExistsInList(string $code, string $list): bool
    {
        $listKeys = array_keys($this->getCodes($list));
        return ($code != null && in_array($code, $listKeys));
    }

    /**
     * Returns an ONIX code based on a unique value and List number.
     */
    public function getCodeFromValue(string $value, string $list): string
    {
        $codes = $this->getCodes($list);
        $codes = array_flip($codes);
        if (array_key_exists($value, $codes)) {
            return $codes[$value];
        }
        return '';
    }

    /**
     * Internal function to return a Codelist object from a row.
     *
     *
     * @hook ONIXCodelistItemDAO::_fromRow [[&$codelistItem, &$code, &$entry]]
     */
    public function _fromRow(string $code, array $entry): ONIXCodelistItem
    {
        $codelistItem = $this->newDataObject();
        $codelistItem->setCode($code);
        $codelistItem->setText($entry[0]);

        Hook::call('ONIXCodelistItemDAO::_fromRow', [&$codelistItem, &$code, &$entry]);

        return $codelistItem;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\ONIXCodelistItemDAO', '\ONIXCodelistItemDAO');
}
