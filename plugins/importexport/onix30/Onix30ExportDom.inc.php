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
		return true;
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

		$identificationCodes =& $assignedPublicationFormat->getIdentificationCodes()->toArray();

		foreach ($identificationCodes as $code) {
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
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductComposition', $assignedPublicationFormat->getProductCompositionCode()); // single item, trade only, etc
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductForm', $assignedPublicationFormat->getProductFormCode()); // paperback, hardcover, etc

		/* --- Physical Book Measurements --- */
		if ($assignedPublicationFormat->getEntryKey() != 'EBOOK') {
			// '01' => 'Height', '02' => 'Width', '03' => 'Thickness', '08' => 'Weight'
			$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
			XMLCustomWriter::appendChild($descDetailNode, $measureNode);
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '01');
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getHeight());
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getHeightUnit());
			unset($measureNode);

			$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
			XMLCustomWriter::appendChild($descDetailNode, $measureNode);
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '02');
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getWidth());
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getWidthUnit());
			unset($measureNode);

			$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
			XMLCustomWriter::appendChild($descDetailNode, $measureNode);
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '03');
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getThickness());
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getThicknessUnit());
			unset($measureNode);

			$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
			XMLCustomWriter::appendChild($descDetailNode, $measureNode);
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', '08');
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', $assignedPublicationFormat->getWeight());
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureUnitCode', $assignedPublicationFormat->getWeightUnit());
			unset($measureNode);
		} else { // include file size Extent
			$extentNode =& XMLCustomWriter::createElement($doc, 'Extent');
			XMLCustomWriter::appendChild($descDetailNode, $extentNode);
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentType', '08');
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentValue', $assignedPublicationFormat->getFileSize());
			XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentUnit', '05');
			unset($extentNode);
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

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getById($monograph->getSeriesId());
		if ($series != null) {
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitlePrefix', $series->getLocalizedPrefix());
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleWithoutPrefix', $series->getLocalizedTitle());
			XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleText', join(' ', array($series->getLocalizedPrefix(), $series->getLocalizedTitle())));
		}
		/* --- and now product level info --- */

		$productTitleDetailNode =& XMLCustomWriter::createElement($doc, 'TitleDetail');
		XMLCustomWriter::appendChild($descDetailNode, $productTitleDetailNode);
		XMLCustomWriter::createChildWithText($doc, $productTitleDetailNode, 'TitleType', '01');

		$titleElementNode =& XMLCustomWriter::createElement($doc, 'TitleElement');
		XMLCustomWriter::appendChild($productTitleDetailNode, $titleElementNode);
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleElementLevel', '01'); // Product Level Title
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitlePrefix', $monograph->getLocalizedPrefix());
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleWithoutPrefix', $monograph->getLocalizedTitle());
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleText', join(' ', array($monograph->getLocalizedPrefix(), $monograph->getLocalizedTitle())));
		/* --- Contributor information --- */

		$authors =& $monograph->getAuthors(); // sorts by sequence.
		$sequence = 1;
		foreach ($authors as $author) {
			$contributorNode =& XMLCustomWriter::createElement($doc, 'Contributor');
			XMLCustomWriter::appendChild($descDetailNode, $contributorNode);
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'SequenceNumber', $sequence);
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup =& $userGroupDao->getById($author->getUserGroupId(), $monograph->getPressId());

			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'ContributorRole', $userGroup->getLocalizedName());
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'PersonName', $author->getFullName());
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'PersonNameInverted', $author->getFullName(true));
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'NamesBeforeKey', join(' ', array($author->getFirstName(), $author->getMiddleName())));
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'KeyNames', $author->getLastName());
			if ($author->getSuffix() != '') {
				XMLCustomWriter::createChildWithText($doc, $contributorNode, 'SuffixToKey', $author->getSuffix());
			}
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'BiographicalNote', strip_tags($author->getLocalizedBiography()));
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'CountryCode', $author->getCountry());

			$sequence++;
			unset($contributorNode);
			unset($userGroup);
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
		}
		/* --- add Extents for 00 (main content) and 04 (back matter) ---*/

		$extentNode =& XMLCustomWriter::createElement($doc, 'Extent');
		XMLCustomWriter::appendChild($descDetailNode, $extentNode);
		XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentType', '00');
		XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentValue', '200'); // $monograph->getMainContentPageCount()
		XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentUnit', '03'); // 03 -> Pages

		$extentNode =& XMLCustomWriter::createElement($doc, 'Extent');
		XMLCustomWriter::appendChild($descDetailNode, $extentNode);
		XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentType', '04');
		XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentValue', '20'); // $monograph->getBackMatterPageCount()
		XMLCustomWriter::createChildWithText($doc, $extentNode, 'ExtentUnit', '03'); // 03 -> Pages

		/* --- Subjects --- */
		$subjectNode =& XMLCustomWriter::createElement($doc, 'Subject');
		XMLCustomWriter::appendChild($descDetailNode, $subjectNode);
		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'MainSubject', ''); // this is empty in the specification
		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectSchemeIdentifier', '12'); // 12 is BIC subject category code list
		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectSchemeVersion', '2'); // Version 2 of ^^

		$monographSubjectDao =& DAORegistry::getDAO('MonographSubjectDAO');
		$allSubjects =& $monographSubjectDao->getSubjects($monograph->getId(),  array_keys(AppLocale::getSupportedFormLocales()));
		$uniqueSubjects = array();
		foreach ($allSubjects as $locale => $subjects) {
			$uniqueSubjects = array_merge($uniqueSubjects, $subjects);
		}

		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectCode', join(' ', $uniqueSubjects));
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'AudienceCode', $monograph->getAudience());

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
		XMLCustomWriter::createChildWithText($doc, $imprintNode, 'ImprintName', 'Imprint Name');

		$publisherNode =& XMLCustomWriter::createElement($doc, 'Publisher');
		XMLCustomWriter::appendChild($publishingDetailNode, $publisherNode);
		XMLCustomWriter::createChildWithText($doc, $publisherNode, 'PublisherRole', '01'); // 01 -> Publisher
		XMLCustomWriter::createChildWithText($doc, $publisherNode, 'PublisherName', $press->getSetting('publisher'));

		/* --- Product Supply --- */

		$productSupplyNode =& XMLCustomWriter::createElement($doc, 'ProductSupply');
		XMLCustomWriter::appendChild($productNode, $productSupplyNode);

		 // Market Information
		$marketNode =& XMLCustomWriter::createElement($doc, 'Market');
		XMLCustomWriter::appendChild($productSupplyNode, $marketNode);
		$marketTerritoryNode =& XMLCustomWriter::createElement($doc, 'Territory');
		XMLCustomWriter::appendChild($marketNode, $marketTerritoryNode);
		XMLCustomWriter::createChildWithText($doc, $marketTerritoryNode, 'CountriesIncluded', $assignedPublicationFormat->getDistributionCountriesAsString());

		$marketPubDetailNode =& XMLCustomWriter::createElement($doc, 'MarketPublishingDetail');
		XMLCustomWriter::appendChild($productSupplyNode, $marketPubDetailNode);
		XMLCustomWriter::createChildWithText($doc, $marketPubDetailNode, 'MarketPublishingStatus', '04'); // 04 -> Active
		$marketDateNode =& XMLCustomWriter::createElement($doc, 'MarketDate');
		XMLCustomWriter::appendChild($marketPubDetailNode, $marketDateNode);
		XMLCustomWriter::createChildWithText($doc, $marketDateNode, 'MarketDateRole', '01'); // 01 -> Publication Date in this Market
		XMLCustomWriter::createChildWithText($doc, $marketDateNode, 'DateFormat', '00'); // 00 -> YYYYMMDD
		XMLCustomWriter::createChildWithText($doc, $marketDateNode, 'Date', '20111212'); // $monograph->getPublicationDate() ?

		// Supply Information
		$supplyDetailNode =& XMLCustomWriter::createElement($doc, 'SupplyDetail');
		XMLCustomWriter::appendChild($productSupplyNode, $supplyDetailNode);
		$supplierNode =& XMLCustomWriter::createElement($doc, 'Supplier');
		XMLCustomWriter::appendChild($supplyDetailNode, $supplierNode);
		XMLCustomWriter::createChildWithText($doc, $supplierNode, 'SupplierRole', '01'); // Publisher supplying to retailers
		XMLCustomWriter::createChildWithText($doc, $supplierNode, 'SupplierName', 'Supplier Name'); // same as PublisherName node in some cases?
		XMLCustomWriter::createChildWithText($doc, $supplyDetailNode, 'ProductAvailability', '21'); // In Stock

		$priceNode =& XMLCustomWriter::createElement($doc, 'Price');
		XMLCustomWriter::appendChild($supplyDetailNode, $priceNode);
		XMLCustomWriter::createChildWithText($doc, $priceNode, 'PriceType', $assignedPublicationFormat->getPriceTypeCode());
		XMLCustomWriter::createChildWithText($doc, $priceNode, 'PriceAmount', $assignedPublicationFormat->getPrice());

		$taxNode =& XMLCustomWriter::createElement($doc, 'Tax');
		XMLCustomWriter::appendChild($supplyDetailNode, $taxNode);
		XMLCustomWriter::appendChild($priceNode, $taxNode);
		XMLCustomWriter::createChildWithText($doc, $taxNode, 'TaxType', $assignedPublicationFormat->getTaxTypeCode()); // VAT, GST, etc
		XMLCustomWriter::createChildWithText($doc, $taxNode, 'TaxRateCode', $assignedPublicationFormat->getTaxRateCode()); // Zero-rated, tax included, tax excluded, etc
		XMLCustomWriter::createChildWithText($doc, $priceNode, 'CurrencyCode', $assignedPublicationFormat->getCurrencyCode()); // CAD, GBP, USD, etc

		return $root;
	}
}

?>
