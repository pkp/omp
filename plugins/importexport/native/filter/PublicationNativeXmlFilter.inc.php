<?php

/**
 * @file plugins/importexport/native/filter/PublicationNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
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

		$context = $deployment->getContext();
		
		$deployment->setPublication($entity);

		// Add the series, if one is designated.
		if ($seriesId = $entity->getData('seriesId')) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var $seriesDao SeriesDAO */
			$series = $seriesDao->getById($seriesId, $context->getId());

			$entityNode->setAttribute('series', $series->getPath());
			$entityNode->setAttribute('series_position', $entity->getData('seriesPosition'));
		}

		$this->addChapters($doc, $entityNode, $entity);

		// cover images
		$coversNode = $this->createPublicationCoversNode($this, $doc, $entity);
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
		$filterDao = DAORegistry::getDAO('FilterDAO'); /** @var $filterDao FilterDAO */
		$nativeExportFilters = $filterDao->getObjectsByGroup('chapter=>native-xml');
		assert(count($nativeExportFilters)==1); // Assert only a single serialization filter
		$exportFilter = array_shift($nativeExportFilters);
		$exportFilter->setDeployment($this->getDeployment());

		$chapters = $entity->getData('chapters');
		$chaptersDoc = $exportFilter->execute($chapters);
		if ($chaptersDoc->documentElement instanceof DOMElement) {
			$clone = $doc->importNode($chaptersDoc->documentElement, true);
			$entityNode->appendChild($clone);
		}
	}

	/**
	 * Create and return an object covers node.
	 * @param $filter NativeExportFilter
	 * @param $doc DOMDocument
	 * @param $object Publication
	 * @return DOMElement
	 */
	function createPublicationCoversNode($filter, $doc, $object) {
		$deployment = $filter->getDeployment();

		$context = $deployment->getContext();

		$coversNode = null;
		$coverImages = $object->getData('coverImage');
		if (!empty($coverImages)) {
			$coversNode = $doc->createElementNS($deployment->getNamespace(), 'covers');
			foreach ($coverImages as $locale => $coverImage) {
				$coverImageName = $coverImage['uploadName'];

				$coverNode = $doc->createElementNS($deployment->getNamespace(), 'cover');
				$coverNode->setAttribute('locale', $locale);
				$coverNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), 'cover_image', htmlspecialchars($coverImageName, ENT_COMPAT, 'UTF-8')));
				$coverNode->appendChild($node = $doc->createElementNS($deployment->getNamespace(), 'cover_image_alt_text', htmlspecialchars($coverImage['altText'], ENT_COMPAT, 'UTF-8')));

				import('classes.file.PublicFileManager');
				$publicFileManager = new PublicFileManager();

				$contextId = $context->getId();
				
				$filePath = $publicFileManager->getContextFilesPath($contextId) . '/' . $coverImageName;
				$embedNode = $doc->createElementNS($deployment->getNamespace(), 'embed', base64_encode(file_get_contents($filePath)));
				$embedNode->setAttribute('encoding', 'base64');
				$coverNode->appendChild($embedNode);
				$coversNode->appendChild($coverNode);
			}
		}
		return $coversNode;
	}

	/**
	 * Parse out the object covers.
	 * @param $filter NativeExportFilter
	 * @param $node DOMElement
	 * @param $object Publication
	 * @param $assocType ASSOC_TYPE_PUBLICATION
	 */
	function parseCovers($filter, $node, $object, $assocType) {
		$deployment = $filter->getDeployment();
		for ($n = $node->firstChild; $n !== null; $n=$n->nextSibling) {
			if (is_a($n, 'DOMElement')) {
				switch ($n->tagName) {
					case 'cover':
						$this->parseCover($filter, $n, $object, $assocType);
						break;
					default:
						$deployment->addWarning($assocType, $object->getId(), __('plugins.importexport.common.error.unknownElement', array('param' => $n->tagName)));
				}
			}
		}
	}
}
