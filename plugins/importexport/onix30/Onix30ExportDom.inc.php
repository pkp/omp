<?php

/**
 * @file plugins/importexport/onix30/Onix30ExportDom.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportDom
 * @ingroup plugins_importexport_onix30
 *
 * @brief Onix 3.0 plugin DOM functions for export
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

class Onix30ExportDom {

	function Onix30ExportDom() {

	}

	function &generateMonographDom(&$doc, &$press, &$monograph, &$assignedPublicationFormat) {
		$unavailableString = __('plugins.importexport.onix30.unavailable');

		$root =& XMLCustomWriter::createElement($doc, 'ONIXMessage');
		XMLCustomWriter::setAttribute($root, 'release', '3.0');
		XMLCustomWriter::setAttribute($root, 'xmlns', 'http://ns.editeur.org/onix/3.0/reference');

		/* --- Header --- */

		$headerNode =& XMLCustomWriter::createElement($doc, 'Header');
		XMLCustomWriter::appendChild($root, $headerNode);

		/* --- Sender --- */

		$senderNode =& XMLCustomWriter::createElement($doc, 'Sender');
		XMLCustomWriter::appendChild($headerNode, $senderNode);

		// The Sender node contains a complex type of SenderIentifier, and then SenderName, ContactName, and EmailAddress
		// Use the Press object for these settings

		$senderIdentifierNode =& XMLCustomWriter::createElement($doc, 'SenderIdentifier');
		XMLCustomWriter::appendChild($senderNode, $senderIdentifierNode);
		$senderIdTypeNode =& XMLCustomWriter::createChildWithText($doc, $senderIdentifierNode, 'SenderIDType', $press->getSetting('codeType'));
		$senderNameNode =& XMLCustomWriter::createChildWithText($doc, $senderIdentifierNode, 'IDValue', $press->getSetting('codeValue'));

		$senderNameNode =& XMLCustomWriter::createChildWithText($doc, $senderNode, 'SenderName', $press->getLocalizedName());
		$contactNameNode =& XMLCustomWriter::createChildWithText($doc, $senderNode, 'ContactName', $press->getContactName());
		$emailAddressNode =& XMLCustomWriter::createChildWithText($doc, $senderNode, 'EmailAddress', $press->getContactEmail());

		/* --- Addressee ---*/

		// this composite is optional, and depends on their being an addressee value sent along with the request
		$addressee = strip_tags(Request::getUserVar('addressee'));
		if ($addressee != '') {
			$addresseeNode =& XMLCustomWriter::createElement($doc, 'Addressee');
			XMLCustomWriter::appendChild($headerNode, $addresseeNode);
			$addresseeNameNode =& XMLCustomWriter::createChildWithText($doc, $addresseeNode, 'AddresseeName', $addressee);
		}

		/* --- SentDateTime --- */
		$sentDateTimeNode =& XMLCustomWriter::createChildWithText($doc, $headerNode, 'SentDateTime', date('Ymd'));

		/* --- Product --- */

		$productNode =& XMLCustomWriter::createElement($doc, 'Product');
		XMLCustomWriter::appendChild($root, $productNode);
		XMLCustomWriter::createChildWithText($doc, $productNode, 'RecordReference', Request::url($press->getPath(), 'monograph', 'view', array($monograph->getId())));
		XMLCustomWriter::createChildWithText($doc, $productNode, 'NotificationType', '03'); // Confirmed record post-publication
		XMLCustomWriter::createChildWithText($doc, $productNode, 'RecordSourceType', '04'); // Bibliographic agency

		$identificationCodes =& $assignedPublicationFormat->getIdentificationCodes();

		while ($code =& $identificationCodes->next()) {
			$productIdentifierNode =& XMLCustomWriter::createElement($doc, 'ProductIdentifier');
			XMLCustomWriter::appendChild($productNode, $productIdentifierNode);
			XMLCustomWriter::createChildWithText($doc, $productIdentifierNode, 'ProductIDType', $code->getCode()); // GTIN-13 (ISBN-13 as GTIN)
			XMLCustomWriter::createChildWithText($doc, $productIdentifierNode, 'IDValue', $code->getValue());
			unset($productIdentifierNode);
			unset($code);
		}
		/* --- Descriptive Detail --- */

		$descDetailNode =& XMLCustomWriter::createElement($doc, 'DescriptiveDetail');
		XMLCustomWriter::appendChild($productNode, $descDetailNode);
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductComposition',
			$assignedPublicationFormat->getProductCompositionCode() ? $assignedPublicationFormat->getProductCompositionCode() : '00'); // single item, trade only, etc.  Default to single item if not specified.
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductForm', $assignedPublicationFormat->getEntryKey()); // paperback, hardcover, etc
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductFormDetail', $assignedPublicationFormat->getProductFormDetailCode(), false); // refinement of ProductForm

		/* --- Physical Book Measurements --- */
		if ($assignedPublicationFormat->getPhysicalFormat()) {
			// '01' => 'Height', '02' => 'Width', '03' => 'Thickness', '08' => 'Weight'
			if ($assignedPublicationFormat->getHeight() != '') {
				$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
				XMLCustomWriter::appendChild($descDetailNode, $measureNode);
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '01');
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getHeight());
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getHeightUnitCode());
				unset($measureNode);
			}

			if ($assignedPublicationFormat->getWidth() != '') {
				$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
				XMLCustomWriter::appendChild($descDetailNode, $measureNode);
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '02');
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getWidth());
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getWidthUnitCode());
				unset($measureNode);
			}

			if ($assignedPublicationFormat->getThickness() != '') {
				$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
				XMLCustomWriter::appendChild($descDetailNode, $measureNode);
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '03');
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getThickness());
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getThicknessUnitCode());
				unset($measureNode);
			}

			if ($assignedPublicationFormat->getWeight() != '') {
				$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
				XMLCustomWriter::appendChild($descDetailNode, $measureNode);
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '08');
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getWeight());
				XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getWeightUnitCode());
				unset($measureNode);
			}

			XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'CountryOfManufacture', $assignedPublicationFormat->getCountryManufactureCode(), false);
		}

		if (!$assignedPublicationFormat->getPhysicalFormat()) {
			XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'EpubTechnicalProtection', $assignedPublicationFormat->getTechnicalProtectionCode(), false);
		}

		/* --- Collection information, first for series and then for product --- */

		$seriesCollectionNode =& XMLCustomWriter::createElement($doc, 'Collection');
		XMLCustomWriter::appendChild($descDetailNode, $seriesCollectionNode);
		XMLCustomWriter::createChildWithText($doc, $seriesCollectionNode, 'CollectionType', '10'); // 10 is publisher series

		$seriesTitleDetailNode =& XMLCustomWriter::createElement($doc, 'TitleDetail');
		XMLCustomWriter::appendChild($seriesCollectionNode, $seriesTitleDetailNode);
		XMLCustomWriter::createChildWithText($doc, $seriesTitleDetailNode, 'TitleType', '01');

		$titleElementNode =& XMLCustomWriter::createElement($doc, 'TitleElement');
		XMLCustomWriter::appendChild($seriesTitleDetailNode, $titleElementNode);
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleElementLevel', '02'); // Collection Level Title

		/* --- Series information, if this monograph is part of one. --- */

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getById($monograph->getSeriesId());
		if ($series != null) {
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'PartNumber', $monograph->getSeriesPosition(), false);
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleText', join(' ', array($series->getLocalizedPrefix(), $series->getLocalizedTitle())));
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitlePrefix', $series->getLocalizedPrefix(), false);
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleWithoutPrefix', $series->getLocalizedTitle());
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'Subtitle', $series->getLocalizedSubtitle(), false);
		}

		/* --- and now product level info --- */

		$productTitleDetailNode =& XMLCustomWriter::createElement($doc, 'TitleDetail');
		XMLCustomWriter::appendChild($descDetailNode, $productTitleDetailNode);
		XMLCustomWriter::createChildWithText($doc, $productTitleDetailNode, 'TitleType', '01');

		$titleElementNode =& XMLCustomWriter::createElement($doc, 'TitleElement');
		XMLCustomWriter::appendChild($productTitleDetailNode, $titleElementNode);
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleElementLevel', '01'); // Product Level Title
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleText', join(' ', array($monograph->getLocalizedPrefix(), $monograph->getLocalizedTitle())));
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitlePrefix', $monograph->getLocalizedPrefix(), false);
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleWithoutPrefix', $monograph->getLocalizedTitle());
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'Subtitle', $monograph->getLocalizedSubtitle(), false);

		/* --- Contributor information --- */

		$authors =& $monograph->getAuthors(); // sorts by sequence.
		$sequence = 1;
		foreach ($authors as $author) {
			$contributorNode =& XMLCustomWriter::createElement($doc, 'Contributor');
			XMLCustomWriter::appendChild($descDetailNode, $contributorNode);
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'SequenceNumber', $sequence);
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup =& $userGroupDao->getById($author->getUserGroupId(), $monograph->getPressId());

			$userGroupOnixMap = array('AU' => 'A01', 'VE' => 'B01', 'CA' => 'A01', 'Trans' => 'B06'); // From List17, ContributorRole types.

			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'ContributorRole', $userGroupOnixMap[$userGroup->getLocalizedAbbrev()]);
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'PersonName', $author->getFullName());
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'PersonNameInverted', $author->getFullName(true));
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'NamesBeforeKey', join(' ', array($author->getFirstName(), $author->getMiddleName())));
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'KeyNames', $author->getLastName());
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'SuffixToKey', $author->getSuffix(), false);

			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'BiographicalNote', strip_tags($author->getLocalizedBiography()));

			$contributorPlaceNode =& XMLCustomWriter::createElement($doc, 'ContributorPlace');
			XMLCustomWriter::appendChild($contributorNode, $contributorPlaceNode);
			XMLCustomWriter::createChildWithText($doc, $contributorPlaceNode, 'ContributorPlaceRelator', '04'); // currently resides in
			XMLCustomWriter::createChildWithText($doc, $contributorPlaceNode, 'CountryCode', $author->getCountry());

			$sequence++;
			unset($contributorPlaceNode);
			unset($contributorNode);
			unset($userGroup);
			unset($author);
		}

		if (sizeof($authors) == 0) { // this will probably never happen, but include the possibility.
			XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'NoContributor', ''); // empty statement of fact.
		}

		/* --- Add Language elements --- */

		$monographLanguageDao =& DAORegistry::getDAO('MonographLanguageDAO');
		$allLanguages =& $monographLanguageDao->getLanguages($monograph->getId(), array_keys(AppLocale::getSupportedFormLocales()));
		$uniqueLanguages = array();
		foreach ($allLanguages as $locale => $languages) {
			$uniqueLanguages = array_merge($uniqueLanguages, $languages);
		}

		foreach ($uniqueLanguages as $language) {
 			$languageNode =& XMLCustomWriter::createElement($doc, 'Language');
 			XMLCustomWriter::appendChild($descDetailNode, $languageNode);
 			XMLCustomWriter::createChildWithText($doc, $languageNode, 'LanguageRole', '01');
 			XMLCustomWriter::createChildWithText($doc, $languageNode, 'LanguageCode', $language);
 			unset($languageNode);
		}

		/* --- add Extents for 00 (main content) and 04 (back matter) ---*/

		if ($assignedPublicationFormat->getFrontMatter() > 0) {
			$extentNode =& XMLCustomWriter::createElement($doc, 'Extent');
			XMLCustomWriter::appendChild($descDetailNode, $extentNode);
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentType', '00');
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentValue', $assignedPublicationFormat->getFrontMatter());
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentUnit', '03'); // 03 -> Pages
			unset($extentNode);
		}

		if ($assignedPublicationFormat->getBackMatter() > 0) {
			$extentNode =& XMLCustomWriter::createElement($doc, 'Extent');
			XMLCustomWriter::appendChild($descDetailNode, $extentNode);
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentType', '04');
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentValue', $assignedPublicationFormat->getBackMatter());
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentUnit', '03'); // 03 -> Pages
			unset($extentNode);
		}

		if (!$assignedPublicationFormat->getPhysicalFormat()) { // EBooks and digital content have extent information about file sizes
			$extentNode =& XMLCustomWriter::createElement($doc, 'Extent');
			XMLCustomWriter::appendChild($descDetailNode, $extentNode);
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentType', '08');
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentValue', $assignedPublicationFormat->getFileSize());
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentUnit', '05');
			unset($extentNode);
		}

		/* --- Add Subject elements --- */

		$subjectNode =& XMLCustomWriter::createElement($doc, 'Subject');
		XMLCustomWriter::appendChild($descDetailNode, $subjectNode);
		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'MainSubject', ''); // this is empty, as per the ONIX 3.0 schema.
		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectSchemeIdentifier', '12'); // 12 is BIC subject category code list
		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectSchemeVersion', '2'); // Version 2 of ^^

		$monographSubjectDao =& DAORegistry::getDAO('MonographSubjectDAO');
		$allSubjects =& $monographSubjectDao->getSubjects($monograph->getId(),  array_keys(AppLocale::getSupportedFormLocales()));
		$uniqueSubjects = array();
		foreach ($allSubjects as $locale => $subjects) {
			$uniqueSubjects = array_merge($uniqueSubjects, $subjects);
		}

		if (sizeof($uniqueSubjects) > 0) {
			XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectCode', join(' ', $uniqueSubjects));
		}

		/* --- Add Audience elements --- */

		$audienceNode =& XMLCustomWriter::createElement($doc, 'Audience');
		XMLCustomWriter::appendChild($descDetailNode, $audienceNode);
		XMLCustomWriter::createChildWithText($doc, $audienceNode, 'AudienceCodeType', $monograph->getAudience());
		XMLCustomWriter::createChildWithText($doc, $audienceNode, 'AudienceCodeValue', '01'); // 01 -> ONIX List 29 - ONIX Audience Codes using List 28 in previous field

		/* --- Check to see if there are qualifiers for Audience, include them if so --- */

		if ($monograph->getAudienceRangeQualifier() != '') {
			$audienceRangeNode =& XMLCustomWriter::createElement($doc, 'AudienceRange');
			XMLCustomWriter::appendChild($descDetailNode, $audienceRangeNode);
			XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangeQualifier', $monograph->getAudienceRangeQualifier());
			if ($monograph->getAudienceRangeExact() != '') {
				XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangePrecision', '01'); // exact (List31)
				XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangeValue', $monograph->getAudienceRangeExact());
			} else { // if not exact, then include the From -> To possibilities
				if ($monograph->getAudienceRangeFrom() != '') {
					XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangePrecision', '03'); // there is no 02.  from (List31)
					XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangeValue', $monograph->getAudienceRangeFrom());
				}
				if ($monograph->getAudienceRangeTo() != '') {
					XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangePrecision', '04'); // to (List31)
					XMLCustomWriter::createChildWithText($doc, $audienceRangeNode, 'AudienceRangeValue', $monograph->getAudienceRangeTo());
				}
			}
		}

		/* --- Collateral Detail --- */

		$collateralDetailNode =& XMLCustomWriter::createElement($doc, 'CollateralDetail');
		XMLCustomWriter::appendChild($productNode, $collateralDetailNode);

		$abstract =& strip_tags($monograph->getLocalizedAbstract());

		$textContentNode =& XMLCustomWriter::createElement($doc, 'TextContent');
		XMLCustomWriter::appendChild($collateralDetailNode, $textContentNode);
		XMLCustomWriter::createChildWithText($doc, $textContentNode, 'TextType', '02'); // short description
		XMLCustomWriter::createChildWithText($doc, $textContentNode, 'ContentAudience', '00'); // any audience
		XMLCustomWriter::createChildWithText($doc, $textContentNode, 'Text', substr($abstract, 0, 250)); // any audience

		$textContentNode =& XMLCustomWriter::createElement($doc, 'TextContent');
		XMLCustomWriter::appendChild($collateralDetailNode, $textContentNode);
		XMLCustomWriter::createChildWithText($doc, $textContentNode, 'TextType', '03'); // description
		XMLCustomWriter::createChildWithText($doc, $textContentNode, 'ContentAudience', '00'); // any audience
		XMLCustomWriter::createChildWithText($doc, $textContentNode, 'Text', $abstract); // any audience

		/* --- Publishing Detail --- */

		$publishingDetailNode =& XMLCustomWriter::createElement($doc, 'PublishingDetail');
		XMLCustomWriter::appendChild($productNode, $publishingDetailNode);

		$imprintNode =& XMLCustomWriter::createElement($doc, 'Imprint');
		XMLCustomWriter::appendChild($publishingDetailNode, $imprintNode);
		XMLCustomWriter::createChildWithText($doc, $imprintNode, 'ImprintName', $assignedPublicationFormat->getImprint(), false);
		unset($imprintNode);

		$publisherNode =& XMLCustomWriter::createElement($doc, 'Publisher');
		XMLCustomWriter::appendChild($publishingDetailNode, $publisherNode);
		XMLCustomWriter::createChildWithText($doc, $publisherNode, 'PublishingRole', '01'); // 01 -> Publisher
		XMLCustomWriter::createChildWithText($doc, $publisherNode, 'PublisherName', $press->getSetting('publisher'));
		XMLCustomWriter::createChildWithText($doc, $publishingDetailNode, 'CityOfPublication', $press->getSetting('location'), false);

		$websiteNode =& XMLCustomWriter::createElement($doc, 'Website');
		XMLCustomWriter::appendChild($publisherNode, $websiteNode);
		XMLCustomWriter::createChildWithText($doc, $websiteNode, 'WebsiteRole', '18'); // 18 -> Publisher's B2C website
		XMLCustomWriter::createChildWithText($doc, $websiteNode, 'WebsiteLink', Request::url($press->getPath()));

		/* --- Publishing Dates --- */

		$publicationDates =& $assignedPublicationFormat->getPublicationDates();
		while ($date =& $publicationDates->next()) {
			$pubDateNode =& XMLCustomWriter::createElement($doc, 'PublishingDate');
			XMLCustomWriter::appendChild($publishingDetailNode, $pubDateNode);
			XMLCustomWriter::createChildWithText($doc, $pubDateNode, 'PublishingDateRole', $date->getRole());
			$dateNode =& XMLCustomWriter::createElement($doc, 'Date');
			XMLCustomWriter::setAttribute($dateNode, 'dateformat', $date->getDateFormat());
			XMLCustomWriter::appendChild($pubDateNode, $dateNode);
			$dateTextNode =& XMLCustomWriter::createTextNode($doc, $date->getDate());
			XMLCustomWriter::appendChild($dateNode, $dateTextNode);

			unset($pubDateNode);
			unset($dateNode);
			unset($date);
		}

		/* -- Sales Rights -- */

		$allSalesRights =& $assignedPublicationFormat->getSalesRights();
		$salesRightsROW = null;
		while ($salesRights =& $allSalesRights->next()) {
			if (!$salesRights->getROWSetting()) {

				$salesRightsNode =& XMLCustomWriter::createElement($doc, 'SalesRights');
				XMLCustomWriter::appendChild($publishingDetailNode, $salesRightsNode);

				XMLCustomWriter::createChildWithText($doc, $salesRightsNode, 'SalesRightsType', $salesRights->getType());
				// now do territories and countries.
				$territoryNode =& XMLCustomWriter::createElement($doc, 'Territory');
				XMLCustomWriter::appendChild($salesRightsNode, $territoryNode);
				if (sizeof($salesRights->getCountriesIncluded()) > 0) {
					XMLCustomWriter::createChildWithText($doc, $territoryNode, 'CountriesIncluded', join(' ', $salesRights->getCountriesIncluded()));
				}
				if (sizeof($salesRights->getRegionsIncluded()) > 0) {
					XMLCustomWriter::createChildWithText($doc, $territoryNode, 'RegionsIncluded', join(' ', $salesRights->getRegionsIncluded()));
				}
				if (sizeof($salesRights->getCountriesExcluded()) > 0) {
					XMLCustomWriter::createChildWithText($doc, $territoryNode, 'CountriesExcluded', join(' ', $salesRights->getCountriesExcluded()));
				}
				if (sizeof($salesRights->getRegionsExcluded()) > 0) {
					XMLCustomWriter::createChildWithText($doc, $territoryNode, 'RegionsExcluded', join(' ', $salesRights->getRegionsExcluded()));
				}

				unset($territoryNode);
				unset($salesRightsNode);

			} else { // found the SalesRights object that is assigned 'rest of world'.
				$salesRightsROW =& $salesRights; // stash this for later since it always goes last.
			}
			unset($salesRights);
		}
		if ($salesRightsROW != null) {
			XMLCustomWriter::createChildWithText($doc, $publishingDetailNode, 'ROWSalesRightsType', $salesRightsROW->getType());
		}

		/* --- Product Supply.  We create one of these per defined Market. --- */

		$representativeDao =& DAORegistry::getDAO('RepresentativeDAO');
		$markets =& $assignedPublicationFormat->getMarkets();

		while ($market =& $markets->next()) {
			$productSupplyNode =& XMLCustomWriter::createElement($doc, 'ProductSupply');
			XMLCustomWriter::appendChild($productNode, $productSupplyNode);

			$marketNode =& XMLCustomWriter::createElement($doc, 'Market');
			XMLCustomWriter::appendChild($productSupplyNode, $marketNode);
			$territoryNode =& XMLCustomWriter::createElement($doc, 'Territory');
			XMLCustomWriter::appendChild($marketNode, $territoryNode);

			if (sizeof($market->getCountriesIncluded()) > 0) {
				XMLCustomWriter::createChildWithText($doc, $territoryNode, 'CountriesIncluded', join(' ', $market->getCountriesIncluded()));
			}
			if (sizeof($market->getRegionsIncluded()) > 0) {
				XMLCustomWriter::createChildWithText($doc, $territoryNode, 'RegionsIncluded', join(' ', $market->getRegionsIncluded()));
			}
			if (sizeof($market->getCountriesExcluded()) > 0) {
				XMLCustomWriter::createChildWithText($doc, $territoryNode, 'CountriesExcluded', join(' ', $market->getCountriesExcluded()));
			}
			if (sizeof($market->getRegionsExcluded()) > 0) {
				XMLCustomWriter::createChildWithText($doc, $territoryNode, 'RegionsExcluded', join(' ', $market->getRegionsExcluded()));
			}
			unset($marketNode);
			unset($territoryNode);

			/* --- Include a MarketPublishingDetail node --- */

			$marketPubDetailNode =& XMLCustomWriter::createElement($doc, 'MarketPublishingDetail');
			XMLCustomWriter::appendChild($productSupplyNode, $marketPubDetailNode);
			$agent =& $representativeDao->getById($market->getAgentId());

			if (isset($agent)) {
				$representativeNode =& XMLCustomWriter::createElement($doc, 'PublisherRepresentative');
				XMLCustomWriter::appendChild($marketPubDetailNode, $representativeNode);
				XMLCustomWriter::createChildWithText($doc, $representativeNode, 'AgentRole', $agent->getRole());
				XMLCustomWriter::createChildWithText($doc, $representativeNode, 'AgentName', $agent->getName());
				if ($agent->getUrl() != '') {
					$agentWebsiteNode =& XMLCustomWriter::createElement($doc, 'Website');
					XMLCustomWriter::appendChild($representativeNode, $agentWebsiteNode);
					XMLCustomWriter::createChildWithText($doc, $agentWebsiteNode, 'WebsiteRole', '18'); // 18 -> Public website
					XMLCustomWriter::createChildWithText($doc, $agentWebsiteNode, 'WebsiteLink', $agent->getUrl());
					unset($supplierWebsiteNode);
				}
				unset($representativeNode);
			}

			XMLCustomWriter::createChildWithText($doc, $marketPubDetailNode, 'MarketPublishingStatus', '04'); // 04 -> Active

			// MarketDate is a required field on the form. If that changes, this should be wrapped in a conditional.
			$marketDateNode =& XMLCustomWriter::createElement($doc, 'MarketDate');
			XMLCustomWriter::appendChild($marketPubDetailNode, $marketDateNode);
			XMLCustomWriter::createChildWithText($doc, $marketDateNode, 'MarketDateRole', $market->getDateRole());
			XMLCustomWriter::createChildWithText($doc, $marketDateNode, 'DateFormat', $market->getDateFormat());
			XMLCustomWriter::createChildWithText($doc, $marketDateNode, 'Date', $market->getDate());

			unset($marketDateNode);
			unset($marketPubDetailNode);

			/* --- Supplier Detail Information --- */

			$supplier =& $representativeDao->getById($market->getSupplierId());
			$supplyDetailNode =& XMLCustomWriter::createElement($doc, 'SupplyDetail');
			XMLCustomWriter::appendChild($productSupplyNode, $supplyDetailNode);

			if (isset($supplier)) {
				$supplierNode =& XMLCustomWriter::createElement($doc, 'Supplier');
				XMLCustomWriter::appendChild($supplyDetailNode, $supplierNode);
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'SupplierRole', $supplier->getRole());
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'SupplierName', $supplier->getName());
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'TelephoneNumber', $supplier->getPhone(), false);
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'FaxNumber', $supplier->getFax(), false);
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'EmailAddress', $supplier->getEmail(), false);
				if ($supplier->getUrl() != '') {
					$supplierWebsiteNode =& XMLCustomWriter::createElement($doc, 'Website');
					XMLCustomWriter::appendChild($supplierNode, $supplierWebsiteNode);
					XMLCustomWriter::createChildWithText($doc, $supplierWebsiteNode, 'WebsiteRole', '18'); // 18 -> Public website
					XMLCustomWriter::createChildWithText($doc, $supplierWebsiteNode, 'WebsiteLink', $supplier->getUrl());
					unset($supplierWebsiteNode);
				}
				unset($supplierNode);
				unset($supplierWebsiteNode);

			} else { // No suppliers specified, use the Press settings instead.
				$supplierNode =& XMLCustomWriter::createElement($doc, 'Supplier');
				XMLCustomWriter::appendChild($supplyDetailNode, $supplierNode);
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'SupplierRole', '09'); // Publisher supplying to end customers
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'SupplierName', $press->getSetting('publisher'));
				XMLCustomWriter::createChildWithText($doc, $supplierNode, 'EmailAddress', $press->getSetting('contactEmail'), false);
				$supplierWebsiteNode =& XMLCustomWriter::createElement($doc, 'Website');
				XMLCustomWriter::appendChild($supplierNode, $supplierWebsiteNode);
				XMLCustomWriter::createChildWithText($doc, $supplierWebsiteNode, 'WebsiteRole', '18'); // 18 -> Publisher's B2C website
				XMLCustomWriter::createChildWithText($doc, $supplierWebsiteNode, 'WebsiteLink', Request::url($press->getPath()));
				unset($supplierNode);
				unset($supplierWebsiteNode);
			}
			if ($assignedPublicationFormat->getReturnableIndicatorCode() != '') {
				$returnsNode =& XMLCustomWriter::createElement($doc, 'ReturnsConditions');
				XMLCustomWriter::appendChild($supplyDetailNode, $returnsNode);
				XMLCustomWriter::createChildWithText($doc, $returnsNode, 'ReturnsCodeType', '02'); // we support the BISAC codes for these
				XMLCustomWriter::createChildWithText($doc, $returnsNode, 'ReturnsCode', $assignedPublicationFormat->getReturnableIndicatorCode());
				unset($returnsNode);
			}

			XMLCustomWriter::createChildWithText($doc, $supplyDetailNode, 'ProductAvailability',
				$assignedPublicationFormat->getProductAvailabilityCode() ? $assignedPublicationFormat->getProductAvailabilityCode() : '20'); // assume 'available' if not specified.

			$priceNode =& XMLCustomWriter::createElement($doc, 'Price');
			XMLCustomWriter::appendChild($supplyDetailNode, $priceNode);
			XMLCustomWriter::createChildWithText($doc, $priceNode, 'PriceType', $market->getPriceTypeCode(), false);

			if ($market->getDiscount() != '') {
				$discountNode =& XMLCustomWriter::createElement($doc, 'Discount');
				XMLCustomWriter::appendChild($priceNode, $discountNode);
				XMLCustomWriter::createChildWithText($doc, $discountNode, 'DiscountPercent', $market->getDiscount());
				unset($discountNode);
			}

			XMLCustomWriter::createChildWithText($doc, $priceNode, 'PriceAmount', $market->getPrice());

			if ($market->getTaxTypeCode() != '' || $market->getTaxRateCode() != '') {
				$taxNode =& XMLCustomWriter::createElement($doc, 'Tax');
				XMLCustomWriter::appendChild($priceNode, $taxNode);
				XMLCustomWriter::createChildWithText($doc, $taxNode, 'TaxType', $market->getTaxTypeCode(), false); // VAT, GST, etc
				XMLCustomWriter::createChildWithText($doc, $taxNode, 'TaxRateCode', $market->getTaxRateCode(), false); // Zero-rated, tax included, tax excluded, etc
				unset($taxNode);
			}
			XMLCustomWriter::createChildWithText($doc, $priceNode, 'CurrencyCode', $market->getCurrencyCode(), false); // CAD, GBP, USD, etc

			unset($priceNode);
			unset($supplyDetailNode);
			unset($market);
		} // end of Market, closes ProductSupply.

		return $root;
	}
}
?>
