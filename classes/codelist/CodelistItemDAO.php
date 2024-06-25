<?php

/**
 * @file classes/codelist/CodelistItemDAO.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CodelistItemDAO
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
use PKP\db\DAO;
use PKP\db\XMLDAO;
use PKP\facades\Locale;
use PKP\plugins\Hook;

abstract class CodelistItemDAO extends DAO
{
    /**
     * Get the codelist item cache.
     */
    public function _getCache(?string $locale = null): GenericCache
    {
        $locale ??= Locale::getLocale();
        $cacheName = $this->getCacheName();

        $cache = & Registry::get($cacheName, true, null);
        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                $this->getName() . '_codelistItems',
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

    /**
     * Handle a cache miss
     */
    public function _cacheMiss(GenericCache $cache, string $id)
    {
        $allCodelistItems = & Registry::get('all' . $this->getName() . 'CodelistItems', true, null);
        if ($allCodelistItems === null) {
            // Add a locale load to the debug notes.
            $notes = & Registry::get('system.debug.notes');
            $locale = $cache->cacheId ?? Locale::getLocale();
            $filename = $this->getFilename($locale);
            $notes[] = ['debug.notes.codelistItemListLoad', ['filename' => $filename]];

            // Reload locale registry file
            $xmlDao = new XMLDAO();
            $nodeName = $this->getName(); // i.e., subject
            $data = $xmlDao->parseStruct($filename, [$nodeName]);

            // Build array with ($charKey => [stuff])
            if (isset($data[$nodeName])) {
                foreach ($data[$nodeName] as $codelistData) {
                    $allCodelistItems[$codelistData['attributes']['code']] = [
                        $codelistData['attributes']['text'],
                    ];
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
     * Get the cache name for this particular codelist database
     */
    public function getCacheName(): string
    {
        return $this->getName() . 'Cache';
    }

    /**
     * Get the filename of the codelist database
     */
    abstract public function getFilename(string $locale): string;

    /**
     * Get the base node name particular codelist database
     *
     */
    abstract public function getName(): string;

    /**
     * Get the name of the CodelistItem subclass.
     */
    abstract public function newDataObject(): CodelistItem;

    /**
     * Retrieve a codelist by code.
     */
    public function getByCode(string $code): CodelistItem
    {
        $cache = $this->_getCache();
        return $this->_fromRow($code, $cache->get($code));
    }

    /**
     * Retrieve an array of all the codelist items.
     */
    public function getCodelistItems(?string $locale = null): array
    {
        $cache = $this->_getCache($locale);
        $returner = [];
        foreach ($cache->getContents() as $code => $entry) {
            $returner[] = $this->_fromRow($code, $entry);
        }
        return $returner;
    }

    /**
     * Retrieve an array of all codelist names.
     */
    public function getNames(?string $locale = null): array
    {
        $cache = $this->_getCache($locale);
        $returner = [];
        $cacheContents = $cache->getContents();
        if (is_array($cacheContents)) {
            foreach ($cache->getContents() as $code => $entry) {
                $returner[] = $entry[0];
            }
        }
        return $returner;
    }

    /**
     * Internal function to construct and populate a Codelist object
     *
     * @hook CodelistItemDAO::_fromRow [[&$codelistItem, &$code, &$entry]]
     */
    public function _fromRow(string $code, array $entry): CodelistItem
    {
        $codelistItem = $this->newDataObject();
        $codelistItem->setCode($code);
        $codelistItem->setText($entry[0]);

        Hook::call('CodelistItemDAO::_fromRow', [&$codelistItem, &$code, &$entry]);

        return $codelistItem;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\CodelistItemDAO', '\CodelistItemDAO');
}
