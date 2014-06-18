<?php

/**
 * @file classes/codelist/CodelistItemDAO.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CodelistItemDAO
 * @ingroup codelist
 * @see CodelistItem
 *
 * @brief Parent class for operations involving Codelist objects.
 *
 */

import('classes.codelist.CodelistItem');

class CodelistItemDAO extends DAO {

	/**
	 * Constructor.
	 */
	function CodelistItemDAO() {
		parent::DAO();
	}

	/**
	 * Get the codelist item cache.
	 * @param $locale string Locale code (optional)
	 * @return GenericCache
	 */
	function _getCache($locale = null) {
		if ($locale == null) {
			$locale = AppLocale::getLocale();
		}
		$cacheName = $this->getCacheName();

		$cache =& Registry::get($cacheName, true, null);
		if ($cache === null) {
			$cacheManager = CacheManager::getManager();
			$cache = $cacheManager->getFileCache(
				$this->getName() . '_codelistItems', $locale,
				array($this, '_cacheMiss')
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
	 * @param $cache GenericCache
	 * @param $id mixed ID that wasn't found in the cache
	 * @return null
	 */
	function _cacheMiss($cache, $id) {
		$allCodelistItems =& Registry::get('all' . $this->getName() . 'CodelistItems', true, null);
		if ($allCodelistItems === null) {
			// Add a locale load to the debug notes.
			$notes =& Registry::get('system.debug.notes');
			$locale = $cache->cacheId;
			if ($locale == null) {
				$locale = AppLocale::getLocale();
			}
			$filename = $this->getFilename($locale);
			$notes[] = array('debug.notes.codelistItemListLoad', array('filename' => $filename));

			// Reload locale registry file
			$xmlDao = new XMLDAO();
			$nodeName = $this->getName(); // i.e., subject
			$data = $xmlDao->parseStruct($filename, array($nodeName));

			// Build array with ($charKey => array(stuff))
			if (isset($data[$nodeName])) {
				foreach ($data[$nodeName] as $codelistData) {
					$allCodelistItems[$codelistData['attributes']['code']] = array(
						$codelistData['attributes']['text'],
					);
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
	 * @return string
	 */
	function getCacheName() {
		return $this->getName() . 'Cache';
	}

	/**
	 * Get the filename of the codelist database
	 * @param $locale string
	 * @return string
	 */
	function getFilename($locale) {
		assert(false);
	}

	/**
	 * Get the base node name particular codelist database
	 * @return string
	 */
	function getName() {
		assert(false);
	}

	/**
	 * Get the name of the CodelistItem subclass.
	 * @return CodelistItem
	 */
	function newDataObject() {
		assert(false);
	}

	/**
	 * Retrieve a codelist by code.
	 * @param $codelistId int
	 * @return CodelistItem
	 */
	function getByCode($code) {
		$cache = $this->_getCache();
		return $this->_fromRow($code, $cache->get($code));
	}

	/**
	 * Retrieve an array of all the codelist items.
	 * @param $locale an optional locale to use
	 * @return array of CodelistItems
	 */
	function getCodelistItems($locale = null) {
		$cache = $this->_getCache($locale);
		$returner = array();
		foreach ($cache->getContents() as $code => $entry) {
			$returner[] = $this->_fromRow($code, $entry);
		}
		return $returner;
	}

	/**
	 * Retrieve an array of all codelist names.
	 * @param $locale an optional locale to use
	 * @return array of CodelistItem names
	 */
	function getNames($locale = null) {
		$cache = $this->_getCache($locale);
		$returner = array();
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
	 * @param $code string
	 * @param $entry array
	 * @return CodelistItem
	 */
	function _fromRow($code, $entry) {
		$codelistItem = $this->newDataObject();
		$codelistItem->setCode($code);
		$codelistItem->setText($entry[0]);

		HookRegistry::call('CodelistItemDAO::_fromRow', array(&$codelistItem, &$code, &$entry));

		return $codelistItem;
	}
}
?>
