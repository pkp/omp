<?php

/**
 * @file Onix30ExportDom.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportDom
 * @ingroup plugins_importexport_onix30
 *
 * @brief Onix 3.0 plugin DOM functions for export
 */

// $Id$


import('lib.pkp.classes.xml.XMLCustomWriter');

class Onix30ExportDom {
	function &generateMonographDom(&$doc, &$press, &$monograph) {
		$unavailableString = __('plugins.importexport.onix30.unavailable');

		$root =& XMLCustomWriter::createElement($doc, 'ONIXMessage');
		XMLCustomWriter::setAttribute($root, 'release', '3.0');

		$lang = $monograph->getLanguage();

		/* --- Header --- */

		$headerNode =& XMLCustomWriter::createElement($doc, 'Header');
		XMLCustomWriter::appendChild($root, $headerNode);

		/* --- Sender --- */

		$senderNode =& XMLCustomWriter::createElement($doc, 'Sender');
		XMLCustomWriter::appendChild($headerNode, $senderNode);

		// The Sender node contains SenderName, ContactName, and EmailAddress
		$senderNameNode =& XMLCustomWriter::createChildWithText($doc, $senderNode, 'SenderName', 'Sender Name Here');
		$contactNameNode =& XMLCustomWriter::createChildWithText($doc, $senderNode, 'ContactName', 'Contact Name Here');
		$emailAddressNode =& XMLCustomWriter::createChildWithText($doc, $senderNode, 'EmailAddress', 'Email Address Here');

		/* --- Addressee ---*/

		$addresseeNode =& XMLCustomWriter::createElement($doc, 'Addressee');
		XMLCustomWriter::appendChild($headerNode, $addresseeNode);
		$addresseeNameNode =& XMLCustomWriter::createChildWithText($doc, $addresseeNode, 'AddresseeName', 'Addressee Name Here');

		/* --- SentDateTime --- */
		$sentDateTimeNode =& XMLCustomWriter::createChildWithText($doc, $headerNode, 'SentDateTime', date('Ymd'));

		/* --- Product --- */

		$productNode =& XMLCustomWriter::createElement($doc, 'Product');
		XMLCustomWriter::appendChild($root, $productNode);
		XMLCustomWriter::createChildWithText($doc, $productNode, 'RecordReference', Request::url($press->getPath(), 'monograph', 'view', array($monograph->getId())));
		XMLCustomWriter::createChildWithText($doc, $productNode, 'NotificationType', '03'); // Confirmed record post-publication
		XMLCustomWriter::createChildWithText($doc, $productNode, 'RecordSourceType', '04'); // Bibliographic agency

		$productIdentifierNode =& XMLCustomWriter::createElement($doc, 'ProductIdentifier');
		XMLCustomWriter::appendChild($productNode, $productIdentifierNode);
		XMLCustomWriter::createChildWithText($doc, $productIdentifierNode, 'ProductIDType', '03'); // GTIN-13 (ISBN-13 as GTIN)
		XMLCustomWriter::createChildWithText($doc, $productIdentifierNode, 'IDValue', '9780007232833'); // value

		/* --- Descriptive Detail --- */

		$descDetailNode =& XMLCustomWriter::createElement($doc, 'DescriptiveDetail');
		XMLCustomWriter::appendChild($productNode, $descDetailNode);
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductComposition', '00'); // 00 is single item retail product
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'ProductForm', 'BC'); // BC is paperback/softback book

		/* --- Physical Book Measures --- */

		$dimensionComponents = array('01', '02', '03'); // 01 -> height, 02 -> width, 03 -> thickness

		foreach ($dimensionComponents as $component) {
			$measureNode =& XMLCustomWriter::createElement($doc, 'Measure');
			XMLCustomWriter::appendChild($descDetailNode, $measureNode);
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', $component);
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'Measurement', '100'); // getMeasurement() ?
			XMLCustomWriter::createChildWithText($doc, $measureNode, 'MeasureType', 'mm'); // getMeasurementUnit() ?
			unset($measureNode);
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
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitlePrefix', 'The'); // ?
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleWithoutPrefix', $monograph->getSeriesTitle()); // should strip out title prefix

		/* --- and now product level info --- */

		$productTitleDetailNode =& XMLCustomWriter::createElement($doc, 'TitleDetail');
		XMLCustomWriter::appendChild($descDetailNode, $productTitleDetailNode);
		XMLCustomWriter::createChildWithText($doc, $productTitleDetailNode, 'TitleType', '01');

		$titleElementNode =& XMLCustomWriter::createElement($doc, 'TitleElement');
		XMLCustomWriter::appendChild($productTitleDetailNode, $titleElementNode);
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleElementLevel', '01'); // Product Level Title
		XMLCustomWriter::createChildWithText($doc, $titleElementNode, 'TitleText', $monograph->getLocalizedTitle()); // should strip out title prefix

		/* --- Contributor information --- */

		$authors =& $monograph->getAuthors(); // sorts by sequence.
		$sequence = 1;
		foreach ($authors as $author) {
			$contributorNode =& XMLCustomWriter::createElement($doc, 'Contributor');
			XMLCustomWriter::appendChild($descDetailNode, $contributorNode);
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'SequenceNumber', $sequence);
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'ContributorRole', 'A01'); // Author.  getUserGroupId() ?
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'NamesBeforeKey', join(' ', array($author->getFirstName(), $author->getMiddleName())));
			XMLCustomWriter::createChildWithText($doc, $contributorNode, 'KeyNames', $author->getLastName());

			$sequence++;
			unset($contributorNode);
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

		XMLCustomWriter::createChildWithText($doc, $subjectNode, 'SubjectCode', join(' ', $uniqueSubjects)); // implement code lookup?
		XMLCustomWriter::createChildWithText($doc, $descDetailNode, 'AudienceCode', '01'); // 01 -> General/trade

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
		XMLCustomWriter::createChildWithText($doc, $publisherNode, 'PublisherName', 'Publisher Name');

		/* --- Product Supply --- */

		$productSupplyNode =& XMLCustomWriter::createElement($doc, 'ProductSupply');
		XMLCustomWriter::appendChild($productNode, $productSupplyNode);

		 // Market Information
		$marketNode =& XMLCustomWriter::createElement($doc, 'Market');
		XMLCustomWriter::appendChild($productSupplyNode, $marketNode);
		$marketTerritoryNode =& XMLCustomWriter::createElement($doc, 'Territory');
		XMLCustomWriter::appendChild($marketNode, $marketTerritoryNode);
		XMLCustomWriter::createChildWithText($doc, $marketTerritoryNode, 'CountriesIncluded', 'CA US IE GB'); // from our Countries list?

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
		XMLCustomWriter::createChildWithText($doc, $priceNode, 'PriceType', '02'); // RRP including tax, if any
		XMLCustomWriter::createChildWithText($doc, $priceNode, 'PriceAmount', '7.99'); // Localized for currencies, based on CountriesIncluded value (decimal points)

		$taxNode =& XMLCustomWriter::createElement($doc, 'Tax');
		XMLCustomWriter::appendChild($supplyDetailNode, $taxNode);
		XMLCustomWriter::appendChild($priceNode, $taxNode);
		XMLCustomWriter::createChildWithText($doc, $taxNode, 'TaxType', '01'); // VAT
		XMLCustomWriter::createChildWithText($doc, $taxNode, 'TaxRateCode', 'Z'); // Zero-rated
		XMLCustomWriter::createChildWithText($doc, $priceNode, 'CurrencyCode', 'GBP'); // Pounds Sterling

		return $root;
	}
}

?>
