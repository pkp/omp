<?php

/**
 * @file plugins/importexport/onix30/filter/MonographONIX30XmlFilter.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographONIX30XmlFilter
 * @ingroup plugins_importexport_onix30
 *
 * @brief Base class that converts a monograph to an ONIX 3.0 document
 */

import('lib.pkp.plugins.importexport.native.filter.NativeExportFilter');

class MonographONIX30XmlFilter extends NativeExportFilter {

	/** var $_doc DOMDocument */
	var $_doc;

	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct($filterGroup) {
		$this->setDisplayName('ONIX 3.0 XML monograph export');
		parent::__construct($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.onix30.filter.MonographONIX30XmlFilter';
	}


	//
	// Implement template methods from Filter
	//
	/**
	 * @see Filter::process()
	 * @param $monograph Monograph the monograph to export
	 * @return DOMDocument
	 */
	function &process(&$monograph) {

		// Note:  There are ONIX fields that can only be assembled from a PublishedMonograph class.
		// e.g. the Audience components. Since this filter can also be used for native import/import
		// export, check to see if we have have a published monograph and use it, otherwise fall back
		// with safe defaults.

		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$publishedMonograph = $publishedMonographDao->getById($monograph->getId());
		if ($publishedMonograph) {
			$monograph = $publishedMonograph;
		}

		// Create the XML document
		$doc = new DOMDocument('1.0');
		$this->_doc = $doc;

		$deployment = $this->getDeployment();

		// create top level ONIXMessage element
		$rootNode = $doc->createElementNS($deployment->getNamespace(), 'ONIXMessage');
		$rootNode->appendChild($this->createHeaderNode($doc, $monograph));

		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormats = $publicationFormatDao->getBySubmissionId($monograph->getId());

		// Append all publication formats as Product nodes.
		while ($publicationFormat = $publicationFormats->next()) {
			$rootNode->appendChild($this->createProductNode($doc, $monograph, $publicationFormat));
		}
		$doc->appendChild($rootNode);
		$rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
		$rootNode->setAttribute('xsi:schemaLocation', $deployment->getNamespace() . ' ' . $deployment->getSchemaFilename());
		$rootNode->setAttribute('release', '3.0');

		return $doc;
	}

	//
	// ONIX conversion functions
	//
	/**
	 * Create and return a node representing the ONIX Header metadata for this submission.
	 * @param $doc DOMDocument
	 * @param $submission Submission
	 * @return DOMElement
	 */
	function createHeaderNode($doc, $submission) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$headNode = $doc->createElementNS($deployment->getNamespace(), 'Header');
		$senderNode = $doc->createElementNS($deployment->getNamespace(), 'Sender');

		// Assemble SenderIdentifier element.
		$senderIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'SenderIdentifier');
		$senderIdentifierNode->appendChild($this->_buildTextNode($doc, 'SenderIDType', $context->getSetting('codeType')));
		$senderIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $context->getSetting('codeValue')));

		$senderNode->appendChild($senderIdentifierNode);

		// Assemble SenderName element.
		$senderNode->appendChild($this->_buildTextNode($doc, 'SenderName', $context->getLocalizedName()));
		$senderNode->appendChild($this->_buildTextNode($doc, 'ContactName', $context->getContactName()));
		$senderNode->appendChild($this->_buildTextNode($doc, 'EmailAddress', $context->getContactEmail()));

		$headNode->appendChild($senderNode);

		// add SentDateTime element.
		$headNode->appendChild($this->_buildTextNode($doc, 'SentDateTime', date('Ymd')));

		return $headNode;
	}

	/**
	 * Create and return a node representing the ONIX Product metadata for this submission.
	 * @param $doc DOMDocument
	 * @param $submission Submission
	 * @param $publicationFormat PublicationFormat
	 * @return DOMElement
	 */
	function createProductNode($doc, $submission, $publicationFormat) {

		$deployment = $this->getDeployment();
		$context = $deployment->getContext();
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');

		$productNode = $doc->createElementNS($deployment->getNamespace(), 'Product');

		$productNode->appendChild($this->_buildTextNode($doc, 'RecordReference', Request::url($context->getPath(), 'monograph', 'view', array($submission->getId()))));
		$productNode->appendChild($this->_buildTextNode($doc, 'NotificationType', '03'));
		$productNode->appendChild($this->_buildTextNode($doc, 'RecordSourceType', '04')); // Bibliographic agency

		$identificationCodes = $publicationFormat->getIdentificationCodes();

		while ($code = $identificationCodes->next()) {
			$productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
			$productIdentifierNode->appendChild($this->_buildTextNode($doc, 'ProductIDType', $code->getCode())); // GTIN-13 (ISBN-13 as GTIN)
			$productIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $code->getValue()));
			$productNode->appendChild($productIdentifierNode);

			unset($productIdentifierNode);
			unset($code);
		}

		// Deal with the possibility of a DOI pubId from the plugin.
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		if (is_array($pubIdPlugins)) {
			foreach ($pubIdPlugins as $plugin) {
				if ($plugin->getEnabled() && $plugin->getPubIdType() == 'doi' && $publicationFormat->getStoredPubId('doi')) {
					$productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
					$productIdentifierNode->appendChild($this->_buildTextNode($doc, 'ProductIDType', '06')); // DOI
					$productIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $publicationFormat->getStoredPubId('doi'))); // GTIN-13 (ISBN-13 as GTIN)
					$productNode->appendChild($productIdentifierNode);

					unset($productIdentifierNode);
				}
				unset($plugin);
			}
		}
		unset($pubIdPlugins);

		/* --- Descriptive Detail --- */
		$descDetailNode = $doc->createElementNS($deployment->getNamespace(), 'DescriptiveDetail');

		$descDetailNode->appendChild($this->_buildTextNode($doc, 'ProductComposition',
				$publicationFormat->getProductCompositionCode() ? $publicationFormat->getProductCompositionCode() : '00')); // single item, trade only, etc.  Default to single item if not specified.

		$descDetailNode->appendChild($this->_buildTextNode($doc, 'ProductForm', $publicationFormat->getEntryKey()));  // paperback, hardcover, etc

		if ($publicationFormat->getProductFormDetailCode() != '') {
			$descDetailNode->appendChild($this->_buildTextNode($doc, 'ProductFormDetail', $publicationFormat->getProductFormDetailCode())); // refinement of ProductForm
		}

		/* --- Physical Book Measurements --- */
		if ($publicationFormat->getPhysicalFormat()) {
			// '01' => 'Height', '02' => 'Width', '03' => 'Thickness', '08' => 'Weight'
			if ($publicationFormat->getHeight() != '') {
				$measureNode = $this->_createMeasurementNode($doc, $deployment, '01', $publicationFormat->getHeight(), $publicationFormat->getHeightUnitCode());
				$descDetailNode->appendChild($measureNode);
				unset($measureNode);
			}

			if ($publicationFormat->getWidth() != '') {
				$measureNode = $this->_createMeasurementNode($doc, $deployment, '02', $publicationFormat->getWidth(), $publicationFormat->getWidthUnitCode());
				$descDetailNode->appendChild($measureNode);
				unset($measureNode);
			}

			if ($publicationFormat->getThickness() != '') {
				$measureNode = $this->_createMeasurementNode($doc, $deployment, '03', $publicationFormat->getThickness(), $publicationFormat->getThicknessUnitCode());
				$descDetailNode->appendChild($measureNode);
				unset($measureNode);
			}

			if ($publicationFormat->getWeight() != '') {
				$measureNode = $this->_createMeasurementNode($doc, $deployment, '08', $publicationFormat->getWeight(), $publicationFormat->getWeightUnitCode());
				$descDetailNode->appendChild($measureNode);
				unset($measureNode);
			}
		}

		if($publicationFormat->getCountryManufactureCode() != '') {
			$descDetailNode->appendChild($this->_buildTextNode($doc, 'CountryOfManufacture', $publicationFormat->getCountryManufactureCode()));
		}

		if (!$publicationFormat->getPhysicalFormat() && $publicationFormat->getTechnicalProtectionCode() != '') {
			$descDetailNode->appendChild($this->_buildTextNode($doc, 'EpubTechnicalProtection', $publicationFormat->getTechnicalProtectionCode()));
		}

		/* --- Collection information, first for series and then for product --- */

		$seriesCollectionNode = $doc->createElementNS($deployment->getNamespace(), 'Collection');
		$seriesCollectionNode->appendChild($this->_buildTextNode($doc, 'CollectionType', '10')); // publisher series.
		$descDetailNode->appendChild($seriesCollectionNode);

		$seriesTitleDetailNode = $doc->createElementNS($deployment->getNamespace(), 'TitleDetail');
		$seriesTitleDetailNode->appendChild($this->_buildTextNode($doc, 'TitleType', '01'));
		$seriesCollectionNode->appendChild($seriesTitleDetailNode);

		$titleElementNode = $doc->createElementNS($deployment->getNamespace(), 'TitleElement');
		$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleElementLevel', '02')); // Collection level title
		$seriesTitleDetailNode->appendChild($titleElementNode);

		/* --- Series information, if this monograph is part of one. --- */

		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById($submission->getSeriesId());
		if ($series != null) {

			if ($submission->getSeriesPosition() != '') {
				$titleElementNode->appendChild($this->_buildTextNode($doc, 'PartNumber', $submission->getSeriesPosition()));
			}

			if ($series->getLocalizedPrefix() == '' || $series->getLocalizedTitle(false) == '') {
				$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleText', trim(join(' ', array($series->getLocalizedPrefix(), $series->getLocalizedTitle(false))))));
			} else {
				if ($series->getLocalizedPrefix() != '') {
					$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitlePrefix', $series->getLocalizedPrefix()));
				}

				$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleWithoutPrefix', $series->getLocalizedTitle(false)));
			}

			if ($series->getLocalizedSubtitle() != '') {
				$titleElementNode->appendChild($this->_buildTextNode($doc, 'Subtitle', $series->getLocalizedSubtitle()));
			}
		}

		/* --- and now product level info --- */

		$productTitleDetailNode = $doc->createElementNS($deployment->getNamespace(), 'TitleDetail');
		$productTitleDetailNode->appendChild($this->_buildTextNode($doc, 'TitleType', '01'));
		$descDetailNode->appendChild($productTitleDetailNode);

		$titleElementNode = $doc->createElementNS($deployment->getNamespace(), 'TitleElement');
		$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleElementLevel', '01'));

		$productTitleDetailNode->appendChild($titleElementNode);

		if ($submission->getLocalizedPrefix() == '' || $submission->getLocalizedTitle(false) == '') {
			$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleText', trim(join(' ', array($submission->getLocalizedPrefix(), $submission->getLocalizedTitle(false))))));
		} else {
			if ($submission->getLocalizedPrefix() != '') {
				$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitlePrefix', $submission->getLocalizedPrefix()));
			}
			$titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleWithoutPrefix', $submission->getLocalizedTitle(false)));
		}

		if ($submission->getLocalizedSubtitle() != '') {
			$titleElementNode->appendChild($this->_buildTextNode($doc, 'Subtitle', $submission->getLocalizedSubtitle()));
		}

		/* --- Contributor information --- */

		$authors = $submission->getAuthors(); // sorts by sequence.
		$sequence = 1;
		foreach ($authors as $author) {
			$contributorNode = $doc->createElementNS($deployment->getNamespace(), 'Contributor');
			$contributorNode->appendChild($this->_buildTextNode($doc, 'SequenceNumber', $sequence));

			$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
			$userGroup = $userGroupDao->getById($author->getUserGroupId(), $submission->getContextId());

			$userGroupOnixMap = array('AU' => 'A01', 'VE' => 'B01', 'CA' => 'A01', 'Trans' => 'B06', 'PE' => 'B21'); // From List17, ContributorRole types.

			$role = array_key_exists($userGroup->getLocalizedAbbrev(), $userGroupOnixMap) ? $userGroupOnixMap[$userGroup->getLocalizedAbbrev()] : 'Z99'; // Z99 - unknown contributor type.

			$contributorNode->appendChild($this->_buildTextNode($doc, 'ContributorRole', $role));
			$contributorNode->appendChild($this->_buildTextNode($doc, 'PersonName', $author->getFullName(false)));
			$contributorNode->appendChild($this->_buildTextNode($doc, 'PersonNameInverted', $author->getFullName(false, true)));
			$contributorNode->appendChild($this->_buildTextNode($doc, 'NamesBeforeKey', $author->getLocalizedGivenName()));
			if ($author->getLocalizedFamilyName() != '') {
				$contributorNode->appendChild($this->_buildTextNode($doc, 'KeyNames', $author->getLocalizedFamilyName()));
			} else {
				$contributorNode->appendChild($this->_buildTextNode($doc, 'KeyNames', $author->getFullName(false)));
			}

			if ($author->getLocalizedBiography() != '') {
				$contributorNode->appendChild($this->_buildTextNode($doc, 'BiographicalNote', $author->getLocalizedBiography()));
			}

			if ($author->getCountry() != '') {
				$contributorPlaceNode = $doc->createElementNS($deployment->getNamespace(), 'ContributorPlace');
				$contributorNode->appendChild($contributorPlaceNode);
				$contributorPlaceNode->appendChild($this->_buildTextNode($doc, 'ContributorPlaceRelator', '04'));
				$contributorPlaceNode->appendChild($this->_buildTextNode($doc, 'CountryCode', $author->getCountry()));
				unset($contributorPlaceNode);
			}

			$sequence++;
			$descDetailNode->appendChild($contributorNode);

			unset($contributorNode);
			unset($sequenceNode);
			unset($userGroup);
			unset($author);
		}

		if (sizeof($authors) == 0) { // this will probably never happen, but include the possibility.
			$descDetailNode->appendChild($this->_buildTextNode($doc, 'NoContributor', '')); // empty state of fact.
		}

		/* --- Add Language elements --- */

		$submissionLanguageDao = DAORegistry::getDAO('SubmissionLanguageDAO');
		$allLanguages = $submissionLanguageDao->getLanguages($submission->getId(), array_keys(AppLocale::getSupportedFormLocales()));
		$uniqueLanguages = array();
		foreach ($allLanguages as $locale => $languages) {
			$uniqueLanguages = array_merge($uniqueLanguages, $languages);
		}

		foreach ($uniqueLanguages as $language) {
			$languageNode = $doc->createElementNS($deployment->getNamespace(), 'Language');

			$languageNode->appendChild($this->_buildTextNode($doc, 'LanguageRole', '01'));
			$onixLanguageCode = $onixCodelistItemDao->getCodeFromValue($language, 'List74');
			if ($onixLanguageCode != '') {
				$languageNode->appendChild($this->_buildTextNode($doc, 'LanguageCode', $onixLanguageCode));
				$descDetailNode->appendChild($languageNode);
			}
			unset($languageNode);
		}

		/* --- add Extents for 00 (main content), 04 (back matter), 08 for digital works ---*/

		if ($publicationFormat->getFrontMatter() > 0) {
			// 03 - Pages
			$extentNode = $this->_createExtentNode($doc, $deployment, '00', $publicationFormat->getFrontMatter(), '03');
			$descDetailNode->appendChild($extentNode);
			unset($extentNode);
		}

		if ($publicationFormat->getBackMatter() > 0) {
			$extentNode = $this->_createExtentNode($doc, $deployment, '04', $publicationFormat->getBackMatter(), '03');
			$descDetailNode->appendChild($extentNode);
			unset($extentNode);
		}

		if (!$publicationFormat->getPhysicalFormat()) { // EBooks and digital content have extent information about file sizes
			$fileSize = $publicationFormat->getFileSize() ? $publicationFormat->getFileSize() : $publicationFormat->getCalculatedFileSize();
			$extentNode = $this->_createExtentNode($doc, $deployment, '08', $fileSize, '05');
			$descDetailNode->appendChild($extentNode);
			unset($extentNode);
		}


		/* --- Add Subject elements --- */

		$subjectNode = $doc->createElementNS($deployment->getNamespace(), 'Subject');
		$mainSubjectNode = $doc->createElementNS($deployment->getNamespace(), 'MainSubject'); // Always empty as per 3.0 spec.
		$subjectNode->appendChild($mainSubjectNode);
		$subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectSchemeIdentifier', '12')); // 12 is BIC subject category code list.
		$subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectSchemeVersion', '2')); // Version 2 of ^^

		$submissionSubjectDao =& DAORegistry::getDAO('SubmissionSubjectDAO');
		$allSubjects =& $submissionSubjectDao->getSubjects($submission->getId(),  array_keys(AppLocale::getSupportedFormLocales()));
		$uniqueSubjects = array();
		foreach ($allSubjects as $locale => $subjects) {
			$uniqueSubjects = array_merge($uniqueSubjects, $subjects);
		}

		if (sizeof($uniqueSubjects) > 0) {
			$subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectCode', trim(join(', ', $uniqueSubjects))));
		}

		$descDetailNode->appendChild($subjectNode);

		/* --- Add Audience elements --- */

		if (is_a($submission, 'PublishedMonograph')) { // PublishedMonograph-specific fields.
			if ($submission->getAudience()) {
				$audienceNode = $doc->createElementNS($deployment->getNamespace(), 'Audience');
				$descDetailNode->appendChild($audienceNode);
				$audienceNode->appendChild($this->_buildTextNode($doc, 'AudienceCodeType', $submission->getAudience()));
				$audienceNode->appendChild($this->_buildTextNode($doc, 'AudienceCodeValue', '01'));
			}

			if ($submission->getAudienceRangeQualifier() != '') {
				$audienceRangeNode = $doc->createElementNS($deployment->getNamespace(), 'AudienceRange');
				$descDetailNode->appendChild($audienceRangeNode);
				$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeQualifier', $submission->getAudienceRangeQualifier()));

				if ($submission->getAudienceRangeExact() != '') {
					$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangePrecision', '01')); // Exact, list31
					$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeValue', $submission->getAudienceRangeExact()));
				} else { // if not exact, then include the From -> To possibilities
					if ($submission->getAudienceRangeFrom() != '') {
						$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangePrecision', '03')); // from
						$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeValue', $submission->getAudienceRangeFrom()));
					}
					if ($submission->getAudienceRangeTo() != '') {
						$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangePrecision', '04')); // to
						$audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeValue', $submission->getAudienceRangeTo()));
					}
				}
			}
		}

		$productNode->appendChild($descDetailNode);
		unset($descDetailNode);

		// Back to assembling Product node.
		/* --- Collateral Detail --- */

		$collateralDetailNode = $doc->createElementNS($deployment->getNamespace(), 'CollateralDetail');
		$productNode->appendChild($collateralDetailNode);

		$abstract = strip_tags($submission->getLocalizedAbstract());

		$textContentNode = $doc->createElementNS($deployment->getNamespace(), 'TextContent');
		$collateralDetailNode->appendChild($textContentNode);
		$textContentNode->appendChild($this->_buildTextNode($doc, 'TextType', '02')); // short description
		$textContentNode->appendChild($this->_buildTextNode($doc, 'ContentAudience', '00')); // Any audience
		$textContentNode->appendChild($this->_buildTextNode($doc, 'Text', substr($abstract, 0, 250))); // Any audience

		$textContentNode = $doc->createElementNS($deployment->getNamespace(), 'TextContent');
		$collateralDetailNode->appendChild($textContentNode);

		$textContentNode->appendChild($this->_buildTextNode($doc, 'TextType', '03')); // description
		$textContentNode->appendChild($this->_buildTextNode($doc, 'ContentAudience', '00')); // Any audience
		$textContentNode->appendChild($this->_buildTextNode($doc, 'Text', $abstract)); // Any audience

		/* --- Publishing Detail --- */

		$publishingDetailNode = $doc->createElementNS($deployment->getNamespace(), 'PublishingDetail');
		$productNode->appendChild($publishingDetailNode);

		if ($publicationFormat->getImprint()) {
			$imprintNode = $doc->createElementNS($deployment->getNamespace(), 'Imprint');
			$publishingDetailNode->appendChild($imprintNode);
			$imprintNode->appendChild($this->_buildTextNode($doc, 'ImprintName', $publicationFormat->getImprint()));
			unset($imprintNode);
		}

		$publisherNode = $doc->createElementNS($deployment->getNamespace(), 'Publisher');
		$publishingDetailNode->appendChild($publisherNode);

		$publisherNode->appendChild($this->_buildTextNode($doc, 'PublishingRole', '01')); // Publisher
		$publisherNode->appendChild($this->_buildTextNode($doc, 'PublisherName', $context->getSetting('publisher')));
		if ($context->getSetting('location') != '') {
			$publishingDetailNode->appendChild($this->_buildTextNode($doc, 'CityOfPublication', $context->getSetting('location')));
		}

		$websiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
		$publisherNode->appendChild($websiteNode);

		$websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Publisher's B2C website
		$websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', Request::url($context->getPath())));

		/* --- Publishing Dates --- */

		$publicationDates = $publicationFormat->getPublicationDates();
		while ($date = $publicationDates->next()) {
			$pubDateNode = $doc->createElementNS($deployment->getNamespace(), 'PublishingDate');
			$publishingDetailNode->appendChild($pubDateNode);

			$pubDateNode->appendChild($this->_buildTextNode($doc, 'PublishingDateRole', $date->getRole()));

			$dateNode = $doc->createElementNS($deployment->getNamespace(), 'Date');
			$dateNode->setAttribute('dateformat', $date->getDateFormat());
			$pubDateNode->appendChild($dateNode);
			$dateNode->appendChild($doc->createTextNode($date->getDate()));

			unset($pubDateNode);
			unset($dateNode);
			unset($date);
		}

		/* -- Sales Rights -- */

		$allSalesRights = $publicationFormat->getSalesRights();
		$salesRightsROW = null;
		while ($salesRights = $allSalesRights->next()) {
			if (!$salesRights->getROWSetting()) {

				$salesRightsNode = $doc->createElementNS($deployment->getNamespace(), 'SalesRights');
				$publishingDetailNode->appendChild($salesRightsNode);
				$salesRightsNode->appendChild($this->_buildTextNode($doc, 'SalesRightsType', $salesRights->getType()));

				// now do territories and countries.
				$territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');
				$salesRightsNode->appendChild($territoryNode);

				if (sizeof($salesRights->getRegionsIncluded()) > 0 && sizeof($salesRights->getCountriesExcluded()) > 0) {
					$territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $salesRights->getRegionsIncluded()))));
					$territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $salesRights->getCountriesExcluded()))));
				} else if (sizeof($salesRights->getCountriesIncluded()) > 0) {
					$territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $salesRights->getCountriesIncluded()))));
				}

				if (sizeof($salesRights->getRegionsExcluded()) > 0) {
					$territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
				}

				unset($territoryNode);
				unset($salesRightsNode);

			} else { // found the SalesRights object that is assigned 'rest of world'.
				$salesRightsROW = $salesRights; // stash this for later since it always goes last.
			}
			unset($salesRights);
		}
		if ($salesRightsROW != null) {
			$publishingDetailNode->appendChild($this->_buildTextNode($doc, 'ROWSalesRightsType', $salesRightsROW->getType()));
		}

		/* --- Product Supply.  We create one of these per defined Market. --- */

		$representativeDao = DAORegistry::getDAO('RepresentativeDAO');
		$markets = $publicationFormat->getMarkets();

		while ($market = $markets->next()) {
			$productSupplyNode = $doc->createElementNS($deployment->getNamespace(), 'ProductSupply');
			$productNode->appendChild($productSupplyNode);

			$marketNode = $doc->createElementNS($deployment->getNamespace(), 'Market');
			$productSupplyNode->appendChild($marketNode);

			$territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');
			$marketNode->appendChild($territoryNode);

			if (sizeof($market->getCountriesIncluded()) > 0) {
				$territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $market->getCountriesIncluded()))));
			}

			if (sizeof($market->getRegionsIncluded()) > 0) {
				$territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $market->getRegionsIncluded()))));
			}

			if (sizeof($market->getCountriesExcluded()) > 0) {
				$territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $market->getCountriesExcluded()))));
			}

			if (sizeof($market->getRegionsExcluded()) > 0) {
				$territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
			}

			unset($marketNode);
			unset($territoryNode);

			/* --- Include a MarketPublishingDetail node --- */

			$marketPubDetailNode = $doc->createElementNS($deployment->getNamespace(), 'MarketPublishingDetail');
			$productSupplyNode->appendChild($marketPubDetailNode);

			$agent = $representativeDao->getById($market->getAgentId());

			if (isset($agent)) {
				$representativeNode = $doc->createElementNS($deployment->getNamespace(), 'PublisherRepresentative');
				$marketPubDetailNode->appendChild($representativeNode);

				$representativeNode->appendChild($this->_buildTextNode($doc, 'AgentRole', $agent->getRole()));
				$representativeNode->appendChild($this->_buildTextNode($doc, 'AgentName', $agent->getName()));

				if ($agent->getUrl() != '') {
					$agentWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
					$representativeNode->appendChild($agentWebsiteNode);

					$agentWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
					$agentWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $agent->getUrl()));
				}
				unset($representativeNode);
			}

			$marketPubDetailNode->appendChild($this->_buildTextNode($doc, 'MarketPublishingStatus', '04')); // Active

			// MarketDate is a required field on the form. If that changes, this should be wrapped in a conditional.
			$marketDateNode = $doc->createElementNS($deployment->getNamespace(), 'MarketDate');
			$marketPubDetailNode->appendChild($marketDateNode);

			$marketDateNode->appendChild($this->_buildTextNode($doc, 'MarketDateRole', $market->getDateRole()));
			$marketDateNode->appendChild($this->_buildTextNode($doc, 'DateFormat', $market->getDateFormat()));
			$marketDateNode->appendChild($this->_buildTextNode($doc, 'Date', $market->getDate()));

			unset($marketDateNode);
			unset($marketPubDetailNode);

			/* --- Supplier Detail Information --- */

			$supplier = $representativeDao->getById($market->getSupplierId());

			$supplyDetailNode = $doc->createElementNS($deployment->getNamespace(), 'SupplyDetail');
			$productSupplyNode->appendChild($supplyDetailNode);

			if (isset($supplier)) {
				$supplierNode = $doc->createElementNS($deployment->getNamespace(), 'Supplier');
				$supplyDetailNode->appendChild($supplierNode);

				$supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierRole', $supplier->getRole()));
				$supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierName', $supplier->getName()));
				if ($supplier->getPhone()) {
					$supplierNode->appendChild($this->_buildTextNode($doc, 'TelephoneNumber', $supplier->getPhone()));
				}
				if ($supplier->getEmail()) {
					$supplierNode->appendChild($this->_buildTextNode($doc, 'EmailAddress', $supplier->getEmail()));
				}

				if ($supplier->getUrl() != '') {
					$supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
					$supplierNode->appendChild($supplierWebsiteNode);

					$supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
					$supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $supplier->getUrl()));

					unset($supplierWebsiteNode);
				}
				unset($supplierNode);
				unset($supplierWebsiteNode);

			} else { // No suppliers specified, use the Press settings instead.
				$supplierNode = $doc->createElementNS($deployment->getNamespace(), 'Supplier');
				$supplyDetailNode->appendChild($supplierNode);

				$supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierRole', '09')); // Publisher supplying to end customers
				$supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierName', $context->getSetting('publisher')));

				if ($context->getSetting('contactEmail') != '') {
					$supplierNode->appendChild($this->_buildTextNode($doc, 'EmailAddress', $context->getSetting('contactEmail')));
				}

				$supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
				$supplierNode->appendChild($supplierWebsiteNode);

				$supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
				$supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', Request::url($context->getPath())));

				unset($supplierNode);
				unset($supplierWebsiteNode);
			}

			if ($publicationFormat->getReturnableIndicatorCode() != '') {
				$returnsNode = $doc->createElementNS($deployment->getNamespace(), 'ReturnsConditions');
				$supplyDetailNode->appendChild($returnsNode);

				$returnsNode->appendChild($this->_buildTextNode($doc, 'ReturnsCodeType', '02'));  // we support the BISAC codes for these
				$returnsNode->appendChild($this->_buildTextNode($doc, 'ReturnsCode', $publicationFormat->getReturnableIndicatorCode()));

				unset($returnsNode);
			}

			$supplyDetailNode->appendChild($this->_buildTextNode($doc, 'ProductAvailability',
					$publicationFormat->getProductAvailabilityCode() ? $publicationFormat->getProductAvailabilityCode() : '20')); // assume 'available' if not specified.

			$priceNode = $doc->createElementNS($deployment->getNamespace(), 'Price');
			$supplyDetailNode->appendChild($priceNode);

			if ($market->getPriceTypeCode() != '') {
 				$priceNode->appendChild($this->_buildTextNode($doc, 'PriceType', $market->getPriceTypeCode()));
			}

			if ($market->getDiscount() != '') {
				$discountNode = $doc->createElementNS($deployment->getNamespace(), 'Discount');
				$priceNode->appendChild($discountNode);
				$discountNode->appendChild($this->_buildTextNode($doc, 'DiscountPercent', $market->getDiscount()));
				unset($discountNode);
			}

			$priceNode->appendChild($this->_buildTextNode($doc, 'PriceAmount', $market->getPrice()));

			if ($market->getTaxTypeCode() != '' || $market->getTaxRateCode() != '') {
				$taxNode = $doc->createElementNS($deployment->getNamespace(), 'Tax');
				$priceNode->appendChild($taxNode);

				if ($market->getTaxTypeCode()) {
					$taxNode->appendChild($this->_buildTextNode($doc, 'TaxType', $market->getTaxTypeCode()));
				}
				if ($market->getTaxRateCode()) {
					$taxNode->appendChild($this->_buildTextNode($doc, 'TaxRateCode', $market->getTaxRateCode()));
				}
				unset($taxNode);
			}

			if ($market->getCurrencyCode() != '') {
				$priceNode->appendChild($this->_buildTextNode($doc, 'CurrencyCode', $market->getCurrencyCode())); // CAD, GBP, USD, etc
			}

			unset($priceNode);
			unset($supplyDetailNode);
			unset($market);
		} // end of Market, closes ProductSupply.

		return $productNode;
	}

	/**
	 * Convenience method for building a Measure node.
	 * @param DOMDocument $doc
	 * @param ONIX30ExportDeployment $deployment
	 * @param string $type
	 * @param string $measurement
	 * @param string $unitCode
	 * @return DOMElement
	 */
	function _createMeasurementNode($doc, $deployment, $type, $measurement, $unitCode) {
		$measureNode =& $doc->createElementNS($deployment->getNamespace(), 'Measure');

		$measureTypeNode = $doc->createElementNS($deployment->getNamespace(), 'MeasureType');
		$measureTypeNode->appendChild($doc->createTextNode($type));

		$measurementNode = $doc->createElementNS($deployment->getNamespace(), 'Measurement');
		$measurementNode->appendChild($doc->createTextNode($measurement));

		$measureUnitNode = $doc->createElementNS($deployment->getNamespace(), 'MeasureUnitCode');
		$measureUnitNode->appendChild($doc->createTextNode($unitCode));

		$measureNode->appendChild($measureTypeNode);
		$measureNode->appendChild($measurementNode);
		$measureNode->appendChild($measureUnitNode);

		return $measureNode;
	}

	/**
	 * Convenience method for building an Extent node.
	 * @param DOMDocument $doc
	 * @param ONIX30ExportDeployment $deployment
	 * @param string $type
	 * @param string $extentValue
	 * @param string $extentUnit
	 * @return DOMElement
	 */
	function _createExtentNode($doc, $deployment, $type, $extentValue, $extentUnit) {
		$extentNode =& $doc->createElementNS($deployment->getNamespace(), 'Extent');

		$typeNode = $doc->createElementNS($deployment->getNamespace(), 'ExtentType');
		$typeNode->appendChild($doc->createTextNode($type));

		$valueNode = $doc->createElementNS($deployment->getNamespace(), 'ExtentValue');
		$valueNode->appendChild($doc->createTextNode($extentValue));

		$unitNode = $doc->createElementNS($deployment->getNamespace(), 'ExtentUnit');
		$unitNode->appendChild($doc->createTextNode($extentUnit));

		$extentNode->appendChild($typeNode);
		$extentNode->appendChild($valueNode);
		$extentNode->appendChild($unitNode);

		return $extentNode;
	}

	/**
	 * Convenience method for building a node with text content.
	 * @param DOMDocument $doc
	 * @param string $nodeName
	 * @param string $textContent
	 * @return DOMElement
	 */
	function _buildTextNode($doc, $nodeName, $textContent) {
		$deployment = $this->getDeployment();
		$node = $doc->createElementNS($deployment->getNamespace(), $nodeName);
		$node->appendChild($doc->createTextNode($textContent));
		return $node;
	}
}


