<?php

/**
 * @file pages/sitemap/SitemapHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SitemapHandler
 * @ingroup pages_sitemap
 *
 * @brief Produce a sitemap in XML format for submitting to search engines.
 */

import('lib.pkp.pages.sitemap.PKPSitemapHandler');

class SitemapHandler extends PKPSitemapHandler {

	/**
	 * @copydoc PKPSitemapHandler::_createContextSitemap()
	 */
	function _createContextSitemap($request) {
		$doc = parent::_createContextSitemap($request);
		$root = $doc->documentElement;

		$press = $request->getPress();
		$pressId = $press->getId();

		// Catalog
		$root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog')));

		$result = Services::get('submission')->getMany(['status' => STATUS_PUBLISHED, 'contextId' => $pressId, 'count' => 1000]);
		foreach ($result as $submission) {
			// Book
			$root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'view', array($submission->getBestId()))));
			// Files
			// Get publication formats
			$publicationFormats = DAORegistry::getDAO('PublicationFormatDAO')->getApprovedByPublicationId($submission->getCurrentPublication()->getId())->toArray();
			foreach ($publicationFormats as $format) {
				// Consider only available publication formats
				if ($format->getIsAvailable()) {
					// Consider only available publication format files
					$availableFiles = array_filter(
						Application::getSubmissionDAO()->getLatestRevisionsByAssocId(ASSOC_TYPE_PUBLICATION_FORMAT, $format->getId(), $submission->getId()),
						function($a) {
							return $a->getDirectSalesPrice() !== null;
						}
					);
					foreach ($availableFiles as $file) {
						$root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'view', array($submission->getBestId(), $format->getBestId(), $file->getBestId()))));
					}
				}
			}
		}

		// New releases
		$root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'newReleases')));
		// Browse by series
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesResult = $seriesDao->getByPressId($pressId);
		while ($series = $seriesResult->next()) {
			$root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'series', $series->getPath())));
		}
		// Browse by categories
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$categoriesResult = $categoryDao->getByContextId($pressId);
		while ($category = $categoriesResult->next()) {
			$root->appendChild($this->_createUrlTree($doc, $request->url($press->getPath(), 'catalog', 'category', $category->getPath())));
		}

		$doc->appendChild($root);

		// Enable plugins to change the sitemap
		HookRegistry::call('SitemapHandler::createPressSitemap', array(&$doc));

		return $doc;
	}

}


