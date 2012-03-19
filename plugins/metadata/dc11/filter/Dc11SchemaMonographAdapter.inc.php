<?php

/**
 * @file plugins/metadata/dc11/filter/Dc11SchemaMonographAdapter.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Dc11SchemaMonographAdapter
 * @ingroup plugins_metadata_dc11_filter
 * @see Monograph
 * @see PKPDc11Schema
 *
 * @brief Abstract base class for meta-data adapters that
 *  injects/extracts Dublin Core schema compliant meta-data into/from
 *  an PublishedMonograph object.
 */


import('lib.pkp.classes.metadata.MetadataDataObjectAdapter');

class Dc11SchemaMonographAdapter extends MetadataDataObjectAdapter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function Dc11SchemaMonographAdapter(&$filterGroup) {
		parent::MetadataDataObjectAdapter($filterGroup);
	}


	//
	// Implement template methods from Filter
	//
	/**
	 * @see Filter::getClassName()
	 */
	function getClassName() {
		return 'plugins.metadata.dc11.filter.Dc11SchemaMonographAdapter';
	}


	//
	// Implement template methods from MetadataDataObjectAdapter
	//
	/**
	 * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
	 * @param $dc11Description MetadataDescription
	 * @param $monograph Monograph
	 * @param $authorClassName string the application specific author class name
	 */
	function &injectMetadataIntoDataObject(&$dc11Description, &$monograph, $authorClassName) {
		// Not implemented
		assert(false);
	}

	/**
	 * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
	 * @param $monograph Monograph
	 * @return MetadataDescription
	 */
	function &extractMetadataFromDataObject(&$monograph) {
		assert(is_a($monograph, 'Monograph'));

		AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

		// Retrieve data that belongs to the monograph.
		// FIXME: Retrieve this data from the respective entity DAOs rather than
		// from the OAIDAO once we've migrated all OAI providers to the
		// meta-data framework. We're using the OAIDAO here because it
		// contains cached entities and avoids extra database access if this
		// adapter is called from an OAI context.
		$oaiDao =& DAORegistry::getDAO('OAIDAO'); /* @var $oaiDao OAIDAO */
		$press =& $oaiDao->getPress($monograph->getPressId());
		$series =& $oaiDao->getSeries($monograph->getSeriesId()); /* @var $series Series */
		$dc11Description =& $this->instantiateMetadataDescription();

		// Title
		$this->_addLocalizedElements($dc11Description, 'dc:title', $monograph->getTitle(null));

		// Creator
		$authors = $monograph->getAuthors();
		foreach($authors as $author) {
			$authorName = $author->getFullName(true);
			$affiliation = $author->getLocalizedAffiliation();
			if (!empty($affiliation)) {
				$authorName .= '; ' . $affiliation;
			}
			$dc11Description->addStatement('dc:creator', $authorName);
			unset($authorName);
		}

		// Subject
		$subjects = array_merge_recursive(
				(array) $monograph->getDiscipline(null),
				(array) $monograph->getSubject(null),
				(array) $monograph->getSubjectClass(null));
		$this->_addLocalizedElements($dc11Description, 'dc:subject', $subjects);

		// Description
		$this->_addLocalizedElements($dc11Description, 'dc:description', $monograph->getAbstract(null));

		// Publisher
		$publisherInstitution = $press->getSetting('publisherInstitution');
		if (!empty($publisherInstitution)) {
			$publishers = array($press->getPrimaryLocale() => $publisherInstitution);
		} else {
			$publishers = $press->getName(null); // Default
		}
		$this->_addLocalizedElements($dc11Description, 'dc:publisher', $publishers);

		// Contributor
		$contributors = $monograph->getSponsor(null);
		foreach ($contributors as $locale => $contributor) {
			$contributors[$locale] = array_map('trim', explode(';', $contributor));
		}
		$this->_addLocalizedElements($dc11Description, 'dc:contributor', $contributors);


		// Date
		if (is_a($monograph, 'PublishedMonograph')) {
			if ($monograph->getDatePublished()) $dc11Description->addStatement('dc:date', date('Y-m-d', strtotime($monograph->getDatePublished())));
		}

		// Type
		$types = array_merge_recursive(
			array(AppLocale::getLocale() => __('rt.metadata.pkp.peerReviewed')),
			(array) $monograph->getType(null)
		);
		$this->_addLocalizedElements($dc11Description, 'dc:type', $types);

		// Format
		if (is_a($monograph, 'PublishedMonograph')) {
			$formats = array();
			foreach ($monograph->getPublicationFormats() as $publicationFormat) {
				$dc11Description->addStatement('dc:format', $publicationFormat->getPhysicalFormat());
			}
		}

		// Identifier: URL
		if (is_a($monograph, 'PublishedMonograph')) {
			$dc11Description->addStatement('dc:identifier', Request::url($press->getPath(), 'catalog', 'book', array($monograph->getPubId())));
		}

		// Identifier: DOI

		// Source (press title and pages)
		$sources = $press->getName(null);
		$pages = $monograph->getPages();
		if (!empty($pages)) $pages = '; ' . $pages;
		foreach ($sources as $locale => $source) {
			$sources[$locale] .= '; ';
			$sources[$locale] .=  $pages;
		}
		$this->_addLocalizedElements($dc11Description, 'dc:source', $sources);

		// Language

		// Relation

		// Coverage
		$coverage = array_merge_recursive(
				(array) $monograph->getCoverageGeo(null),
				(array) $monograph->getCoverageChron(null),
				(array) $monograph->getCoverageSample(null));
		$this->_addLocalizedElements($dc11Description, 'dc:coverage', $coverage);

		// Rights
		$this->_addLocalizedElements($dc11Description, 'dc:rights', $press->getSetting('copyrightNotice'));

		Hookregistry::call('Dc11SchemaMonographAdapter::extractMetadataFromDataObject', array(&$this, $monograph, $press, &$dc11Description));

		return $dc11Description;
	}

	/**
	 * @see MetadataDataObjectAdapter::getDataObjectMetadataFieldNames()
	 * @param $translated boolean
	 */
	function getDataObjectMetadataFieldNames($translated = true) {
		// All DC fields are mapped.
		return array();
	}


	//
	// Private helper methods
	//
	/**
	 * Add an array of localized values to the given description.
	 * @param $description MetadataDescription
	 * @param $propertyName string
	 * @param $localizedValues array
	 */
	function _addLocalizedElements(&$description, $propertyName, $localizedValues) {
		foreach(stripAssocArray((array) $localizedValues) as $locale => $values) {
			if (is_scalar($values)) $values = array($values);
			foreach($values as $value) {
				$description->addStatement($propertyName, $value, $locale);
				unset($value);
			}
		}
	}
}
?>
