<?php

/**
 * @file plugins/metadata/dc11/filter/Dc11SchemaPublicationFormatAdapter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Dc11SchemaPublicationFormatAdapter
 * @ingroup plugins_metadata_dc11_filter
 * @see PublicationFormat
 * @see PKPDc11Schema
 *
 * @brief Adapter that injects/extracts Dublin Core schema compliant meta-data
 * into/from a PublicationFormat object.
 */


import('lib.pkp.classes.metadata.MetadataDataObjectAdapter');

class Dc11SchemaPublicationFormatAdapter extends MetadataDataObjectAdapter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct(&$filterGroup) {
		parent::__construct($filterGroup);
	}


	//
	// Implement template methods from Filter
	//
	/**
	 * @see Filter::getClassName()
	 */
	function getClassName() {
		return 'plugins.metadata.dc11.filter.Dc11SchemaPublicationFormatAdapter';
	}


	//
	// Implement template methods from MetadataDataObjectAdapter
	//
	/**
	 * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
	 * @param $dc11Description MetadataDescription
	 * @param $publicationFormat PublicationFormat
	 */
	function &injectMetadataIntoDataObject(&$dc11Description, &$publicationFormat) {
		// Not implemented
		assert(false);
	}

	/**
	 * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
	 * @param $publicationFormat PublicationFormat
	 * @return MetadataDescription
	 */
	function extractMetadataFromDataObject(&$publicationFormat) {
		assert(is_a($publicationFormat, 'PublicationFormat'));

		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);

		// Retrieve data that belongs to the publication format.
		// FIXME: Retrieve this data from the respective entity DAOs rather than
		// from the OAIDAO once we've migrated all OAI providers to the
		// meta-data framework. We're using the OAIDAO here because it
		// contains cached entities and avoids extra database access if this
		// adapter is called from an OAI context.
		$oaiDao = DAORegistry::getDAO('OAIDAO'); /* @var $oaiDao OAIDAO */
		$publication = Services::get('publication')->get($publicationFormat->getData('publicationId'));
		$monograph = Services::get('submission')->get($publication->getData('submissionId'));
		$press = $oaiDao->getPress($monograph->getPressId());
		$series = $oaiDao->getSeries($monograph->getSeriesId()); /* @var $series Series */
		$dc11Description = $this->instantiateMetadataDescription();

		// Title
	        $publication = $monograph->getCurrentPublication();
		$this->_addLocalizedElements($dc11Description, 'dc:title', $publication->getFullTitles());

		// Creator
		$authors = $monograph->getAuthors();
		foreach($authors as $author) {
			$authorName = $author->getFullName(false, true);
			$affiliation = $author->getLocalizedAffiliation();
			if (!empty($affiliation)) {
				$authorName .= '; ' . $affiliation;
			}
			$dc11Description->addStatement('dc:creator', $authorName);
			unset($authorName);
		}

		// Subject
		$submissionKeywordDao = DAORegistry::getDAO('SubmissionKeywordDAO'); /* @var $submissionKeywordDao SubmissionKeywordDAO */
		$submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO'); /* @var $submissionSubjectDao SubmissionSubjectDAO */
		$supportedLocales = array_keys(AppLocale::getSupportedFormLocales());
		$subjects = array_merge_recursive(
			(array) $submissionKeywordDao->getKeywords($publication->getId(), $supportedLocales),
			(array) $submissionSubjectDao->getSubjects($publication->getId(), $supportedLocales)
		);
		$this->_addLocalizedElements($dc11Description, 'dc:subject', $subjects);

		// Description
		$this->_addLocalizedElements($dc11Description, 'dc:description', $monograph->getAbstract(null));

		// Publisher
		$publisherInstitution = $press->getSetting('publisherInstitution');
		if (!empty($publisherInstitution)) {
			$publishers = [$press->getPrimaryLocale() => $publisherInstitution];
		} else {
			$publishers = $press->getName(null); // Default
		}
		$this->_addLocalizedElements($dc11Description, 'dc:publisher', $publishers);

		// Contributor
		$contributors = $monograph->getSponsor(null);
		if (is_array($contributors)) {
			foreach ($contributors as $locale => $contributor) {
				$contributors[$locale] = array_map('trim', explode(';', $contributor));
			}
			$this->_addLocalizedElements($dc11Description, 'dc:contributor', $contributors);
		}

		// Date
		// FIXME: should we use the publication dates of the publication format? If yes,
		// in which role preference order?
		if (is_a($monograph, 'Submission')) {
			if ($monograph->getDatePublished()) $dc11Description->addStatement('dc:date', date('Y-m-d', strtotime($monograph->getDatePublished())));
		}

		// Type
		$types = array_merge_recursive(
			[AppLocale::getLocale() => __('rt.metadata.pkp.dctype')],
			(array) $monograph->getType(null)
		);
		$this->_addLocalizedElements($dc11Description, 'dc:type', $types);

		// Format
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /* @var $onixCodelistItemDao ONIXCodelistItemDAO */
		$entryKeys = $onixCodelistItemDao->getCodes('List7'); // List7 is for object formats
		if ($publicationFormat->getEntryKey()) {
			$formatName = $entryKeys[$publicationFormat->getEntryKey()];
			$dc11Description->addStatement('dc:format', $formatName);
		}

		// Identifier: URL
		$request = Application::get()->getRequest();
		if (is_a($monograph, 'Submission')) {
			$dc11Description->addStatement('dc:identifier', $request->url($press->getPath(), 'catalog', 'book', [$monograph->getId()]));
		}

		// Public idntifiers (e.g. DOI, URN)
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		foreach ((array) $pubIdPlugins as $plugin) {
			$pubId = $plugin->getPubId($publicationFormat);
			if ($pubId) {
				$dc11Description->addStatement('dc:identifier', $pubId);
			}
			$publicationPubId = $plugin->getPubId($publication);
			if ($publicationPubId) {
				$dc11Description->addStatement('dc:relation', $publicationPubId);
			}
		}

		// Identifier: others
		$identificationCodeFactory = $publicationFormat->getIdentificationCodes();
		while ($identificationCode = $identificationCodeFactory->next()) {
			$dc11Description->addStatement('dc:identifier', $identificationCode->getValue());
		}

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
		$submissionLanguage = $monograph->getData('locale');
		if (!empty($submissionLanguage)) {
			$dc11Description->addStatement('dc:language', AppLocale::getIso3FromLocale($submissionLanguage));
		}

		// Relation   (Add publication file format to monograph / edited volume)
		$pubFormatFiles = Services::get('submissionFile')->getMany([
			'submissionIds' => [$monograph->getId()],
			'assocTypes' => [ASSOC_TYPE_PUBLICATION_FORMAT]
		]);


		foreach ($pubFormatFiles as $file) {
			{
				if ($file->getData('assocId') == $publicationFormat->getData('id')) {
					$relation = $request->url($press->getData('urlPath'), 'catalog', 'view', [$monograph->getId(), $publicationFormat->getId(), $file->getId()]);
					$dc11Description->addStatement('dc:relation', $relation);
				}
			}
		}

		// Coverage
		$coverage = (array) $monograph->getCoverage(null);
		$this->_addLocalizedElements($dc11Description, 'dc:coverage', $coverage);

		// Rights
		$salesRightsFactory = $publicationFormat->getSalesRights();
		while ($salesRight = $salesRightsFactory->next()) {
			$dc11Description->addStatement('dc:rights', $salesRight->getNameForONIXCode());
		}

		Hookregistry::call('Dc11SchemaPublicationFormatAdapter::extractMetadataFromDataObject', [&$this, $monograph, $press, &$dc11Description]);

		return $dc11Description;
	}

	/**
	 * @see MetadataDataObjectAdapter::getDataObjectMetadataFieldNames()
	 * @param $translated boolean
	 */
	function getDataObjectMetadataFieldNames($translated = true) {
		// All DC fields are mapped.
		return [];
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
			if (is_scalar($values)) $values = [$values];
			foreach($values as $value) {
				$description->addStatement($propertyName, $value, $locale);
				unset($value);
			}
		}
	}
}

