<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlPublicationFilter.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlPublicationFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a set of monographs.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlPKPPublicationFilter');

class NativeXmlPublicationFilter extends NativeXmlPKPPublicationFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct($filterGroup) {
		parent::__construct($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.NativeXmlPublicationFilter';
	}

	/**
	 * @see Filter::process()
	 * @param $document DOMDocument|string
	 * @return array Array of imported documents
	 */
	function &process(&$document) {
		$importedObjects =& parent::process($document);

		return $importedObjects;
	}

	/**
	 * Populate the submission object from the node
	 * @param $publication Publication
	 * @param $node DOMElement
	 * @return Publication
	 */
	function populateObject($publication, $node) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$seriesPath = $node->getAttribute('series');
		$seriesPosition = $node->getAttribute('series_position');
		if ($seriesPath !== '') {
			$seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var $seriesDao SeriesDAO */
			$series = $seriesDao->getByPath($seriesPath, $context->getId());
			if (!$series) {
				$deployment->addError(ASSOC_TYPE_PUBLICATION, $publication->getId(), __('plugins.importexport.native.error.unknownSeries', array('param' => $seriesPath)));
			} else {
				$publication->setData('seriesId', $series->getId());
				$publication->setData('seriesPosition', $seriesPosition);
			}
		}

		return parent::populateObject($publication, $node);
	}

	/**
	 * Handle an element whose parent is the submission element.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function handleChildElement($n, $publication) {
		switch ($n->tagName) {
			case 'publication_format':
				$this->parsePublicationFormat($n, $publication);
				break;
			case 'chapters':
				$this->parseChapters($n, $publication);
				break;
			case 'covers':
				$this->parsePublicationCovers($this, $n, $publication);
				break;
			default:
				parent::handleChildElement($n, $publication);
		}
	}

	/**
	 * Get the import filter for a given element.
	 * @param $elementName string Name of XML element
	 * @return Filter
	 */
	function getImportFilter($elementName) {
		$deployment = $this->getDeployment();
		$publication = $deployment->getPublication();
		$importClass = null; // Scrutinizer
		switch ($elementName) {
			case 'publication_format':
				$importClass='PublicationFormat';
				break;
			case 'chapter':
				$importClass='Chapter';
				break;
			default:
				$deployment->addError(ASSOC_TYPE_PUBLICATION, $publication->getId(), __('plugins.importexport.common.error.unknownElement', array('param' => $elementName)));
		}
		// Caps on class name for consistency with imports, whose filter
		// group names are generated implicitly.
		$filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
		$importFilters = $filterDao->getObjectsByGroup('native-xml=>' . $importClass);
		$importFilter = array_shift($importFilters);
		return $importFilter;
	}

	/**
	 * Parse a publication format and add it to the submission.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function parsePublicationFormat($n, $publication) {
		$importFilter = $this->getImportFilter($n->tagName);
		assert($importFilter); // There should be a filter

		$existingDeployment = $this->getDeployment();
		$request = Application::get()->getRequest();
		
		$onixDeployment = new Onix30ExportDeployment($request->getContext(), $request->getUser());
		$onixDeployment->setPublication($existingDeployment->getPublication());
		$onixDeployment->setFileDBIds($existingDeployment->getFileDBIds());
		$onixDeployment->setAuthorDBIds($existingDeployment->getAuthorDBIds());
		$importFilter->setDeployment($existingDeployment);
		$formatDoc = new DOMDocument();
		$formatDoc->appendChild($formatDoc->importNode($n, true));
		return $importFilter->execute($formatDoc);
	}

	/**
	 * Parse a publication format and add it to the submission.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function parseChapters($node, $publication) {
		$deployment = $this->getDeployment();

		$chapters = array();

		for ($n = $node->firstChild; $n !== null; $n=$n->nextSibling) {
			if (is_a($n, 'DOMElement')) {
				switch ($n->tagName) {
					case 'chapter':
						$chapter = $this->parseChapter($n, $publication);
						$chapters[] = $chapter;
						break;
					default:
						$deployment->addWarning(ASSOC_TYPE_PUBLICATION, $publication->getId(), __('plugins.importexport.common.error.unknownElement', array('param' => $n->tagName)));
				}
			}
		}

		$publication->setData('chapters', $chapters);
	}

	/**
	 * Parse a publication format and add it to the submission.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function parseChapter($n, $publication) {
		$importFilter = $this->getImportFilter($n->tagName);
		assert($importFilter); // There should be a filter

		$existingDeployment = $this->getDeployment();
		$request = Application::get()->getRequest();

		$importFilter->setDeployment($existingDeployment);
		$chapterDoc = new DOMDocument();
		$chapterDoc->appendChild($chapterDoc->importNode($n, true));
		return $importFilter->execute($chapterDoc);
	}

	/**
	 * Parse out the object covers.
	 * @param $filter NativeExportFilter
	 * @param $node DOMElement
	 * @param $object Publication
	 */
	function parsePublicationCovers($filter, $node, $object) {
		$deployment = $filter->getDeployment();

		$coverImages = array();

		for ($n = $node->firstChild; $n !== null; $n=$n->nextSibling) {
			if (is_a($n, 'DOMElement')) {
				switch ($n->tagName) {
					case 'cover':
						$coverImage = $this->parsePublicationCover($filter, $n, $object);
						$coverImages[key($coverImage)] = reset($coverImage);
						break;
					default:
						$deployment->addWarning(ASSOC_TYPE_PUBLICATION, $object->getId(), __('plugins.importexport.common.error.unknownElement', array('param' => $n->tagName)));
				}
			}
		}

		$object->setData('coverImage', $coverImages);
	}

	/**
	 * Parse out the cover and store it in the object.
	 * @param $filter NativeExportFilter
	 * @param $node DOMElement
	 * @param $object Publication
	 */
	function parsePublicationCover($filter, $node, $object) {
		$deployment = $filter->getDeployment();

		$context = $deployment->getContext();

		$locale = $node->getAttribute('locale');
		if (empty($locale)) $locale = $context->getPrimaryLocale();

		$coverImagelocale = array();
		$coverImage = array();

		for ($n = $node->firstChild; $n !== null; $n=$n->nextSibling) {
			if (is_a($n, 'DOMElement')) {
				switch ($n->tagName) {
					case 'cover_image': 
						$coverImage['uploadName'] = $n->textContent; 
						break;
					case 'cover_image_alt_text':
						$coverImage['altText'] = $n->textContent; 
						break;
					case 'embed':
						import('classes.file.PublicFileManager');
						$publicFileManager = new PublicFileManager();
						$filePath = $publicFileManager->getContextFilesPath($context->getId()) . '/' . $coverImage['uploadName'];
						file_put_contents($filePath, base64_decode($n->textContent));
						break;
					default:
						$deployment->addWarning(ASSOC_TYPE_PUBLICATION, $object->getId(), __('plugins.importexport.common.error.unknownElement', array('param' => $n->tagName)));
				}
			}
		}

		$coverImagelocale[$locale] = $coverImage;

		return $coverImagelocale;
	}

	/**
	 * Get the representation export filter group name
	 * @return string
	 */
	function getRepresentationExportFilterGroupName() {
		return 'publication-format=>native-xml';
	}
}


