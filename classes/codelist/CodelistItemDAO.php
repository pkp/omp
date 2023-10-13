<?php

/**
 * @file classes/codelist/CodelistItemDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CodelistItemDAO
 *
 * @ingroup codelist
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

class CodelistItemDAO extends DAO
{
    /**
     * Get the codelist item cache.
     *
     * @param string $locale Locale code (optional)
     *
     * @return GenericCache
     */
    public function _getCache($locale = null)
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
     *
     * @param GenericCache $cache
     * @param mixed $id ID that wasn't found in the cache
     */
    public function _cacheMiss($cache, $id)
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
     *
     * @return string
     */
    public function getCacheName()
    {
        return $this->getName() . 'Cache';
    }

    /**
     * Get the filename of the codelist database
     *
     * @param string $locale
     *
     * @return string
     */
    public function getFilename($locale)
    {
        assert(false);
    }

    /**
     * Get the base node name particular codelist database
     *
     * @return string
     */
    public function getName()
    {
        assert(false);
    }

    /**
     * Get the name of the CodelistItem subclass.
     *
     * @return CodelistItem
     */
    public function newDataObject()
    {
        assert(false);
    }

    /**
     * Retrieve a codelist by code.
     *
     * @return CodelistItem
     */
    public function getByCode($code)
    {
        $cache = $this->_getCache();
        return $this->_fromRow($code, $cache->get($code));
    }

    /**
     * Retrieve an array of all the codelist items.
     *
     * @param string $locale an optional locale to use
     *
     * @return array of CodelistItems
     */
    public function getCodelistItems($locale = null)
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
     *
     * @param string $locale an optional locale to use
     *
     * @return array of CodelistItem names
     */
    public function getNames($locale = null)
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
     * @param string $code
     * @param array $entry
     *
     * @return CodelistItem
     *
     * @hook CodelistItemDAO::_fromRow [[&$codelistItem, &$code, &$entry]]
     */
    public function _fromRow($code, $entry)
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
