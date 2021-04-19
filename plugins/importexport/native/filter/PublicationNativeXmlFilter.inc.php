<?php

/**
 * @file plugins/importexport/native/filter/PublicationNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Article to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.PKPPublicationNativeXmlFilter');

class PublicationNativeXmlFilter extends PKPPublicationNativeXmlFilter {
	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.PublicationNativeXmlFilter';
	}

	/**
	 * Get the representation export filter group name
	 * @return string
	 */
	function getRepresentationExportFilterGroupName() {
		return 'publication-format=>native-xml';
	}

	//
	// Submission conversion functions
	//
	/**
	 * Create and return a submission node.
	 * @param $doc DOMDocument
	 * @param $entity Publication
	 * @return DOMElement
	 */
	function createEntityNode($doc, $entity) {
		$deployment = $this->getDeployment();
		$entityNode = parent::createEntityNode($doc, $entity);

		$deployment->setPublication($entity);

		// Add the series, if one is designated.
		$seriesNode = $this->createSeriesNode($this, $doc, $entity);
		if ($seriesNode) {
			$entityNode->appendChild($seriesNode);

			$entityNode->setAttribute('series_position', $entity->getData('seriesPosition'));
		}


		$chapters = $entity->getData('chapters');
		if ($chapters && count($chapters) > 0) {
			$this->addChapters($doc, $entityNode, $entity);
		}

		// cover images
		import('lib.pkp.plugins.importexport.native.filter.PKPNativeFilterHelper');
		$nativeFilterHelper = new PKPNativeFilterHelper();
		$coversNode = $nativeFilterHelper->createPublicationCoversNode($this, $doc, $entity);
		if ($coversNode) $entityNode->appendChild($coversNode);

		return $entityNode;
	}

	/**
	 * Add the chapter metadata for a publication to its DOM element.
	 * @param $doc DOMDocument
	 * @param $entityNode DOMElement
	 * @param $entity Publication
	 */
	function addChapters($doc, $entityNode, $entity) {
		$currentFilter = PKPImportExportFilter::getFilter('chapter=>native-xml', $this->getDeployment());

		$chapters = $entity->getData('chapters');
		if ($chapters && count($chapters) > 0) {
			$chaptersDoc = $currentFilter->execute($chapters);
			if ($chaptersDoc && $chaptersDoc->documentElement instanceof DOMElement) {
				$clone = $doc->importNode($chaptersDoc->documentElement, true);
				$entityNode->appendChild($clone);
			} else {
				$deployment = $this->getDeployment();
				$deployment->addError(ASSOC_TYPE_PUBLICATION, $entity->getId(), __('plugins.importexport.chapter.exportFailed'));

				throw new Exception(__('plugins.importexport.chapter.exportFailed'));
			}
		}
	}
}
