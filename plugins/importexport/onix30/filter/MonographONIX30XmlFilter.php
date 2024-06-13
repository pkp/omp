<?php

/**
 * @file plugins/importexport/onix30/filter/MonographONIX30XmlFilter.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographONIX30XmlFilter
 *
 * @ingroup plugins_importexport_onix30
 *
 * @brief Base class that converts a monograph to an ONIX 3.0 document
 */

namespace APP\plugins\importexport\onix30\filter;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use APP\facades\Repo;
use APP\monograph\RepresentativeDAO;
use APP\plugins\importexport\onix30\Onix30ExportDeployment;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use DOMDocument;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\filter\FilterGroup;
use PKP\submission\SubmissionSubjectDAO;

class MonographONIX30XmlFilter extends \PKP\plugins\importexport\native\filter\NativeExportFilter
{
    /** @var \DOMDocument */
    public $_doc;

    /**
     * Constructor
     *
     * @param FilterGroup $filterGroup
     */
    public function __construct($filterGroup)
    {
        $this->setDisplayName('ONIX 3.0 XML monograph export');
        parent::__construct($filterGroup);
    }

    //
    // Implement template methods from Filter
    //
    /**
     * @see Filter::process()
     *
     * @param Submission $submissions | array Monographs to export
     *
     * @return \DOMDocument
     */
    public function &process(&$submissions)
    {
        // Create the XML document
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $this->_doc = $doc;
        $deployment = $this->getDeployment();

        // create top level ONIXMessage element
        $rootNode = $doc->createElementNS($deployment->getNamespace(), 'ONIXMessage');
        $rootNode->appendChild($this->createHeaderNode($doc));

        if (!is_array($submissions)) {
            $this->createSubmissionNode($doc, $rootNode, $submissions);
        } else {
            foreach ($submissions as $submission) {
                $this->createSubmissionNode($doc, $rootNode, $submission);
            }
        }

        $doc->appendChild($rootNode);
        $rootNode->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $rootNode->setAttribute('xsi:schemaLocation', $deployment->getNamespace() . ' ' . $deployment->getSchemaFilename());
        $rootNode->setAttribute('release', '3.0');

        return $doc;
    }

    /**
     * Creates a submission node for each input submission.
     *
     * @param \DOMDocument $doc The main XML Document object
     * @param \DOMElement $rootNode The root node of the document, on which the submission node will get attached
     * @param Submission $submission The submission we want to export and attach.
     */
    public function createSubmissionNode($doc, $rootNode, $submission)
    {
        $publicationFormats = $submission->getCurrentPublication()->getData('publicationFormats');

        // Append all publication formats as Product nodes.
        foreach ($publicationFormats as $publicationFormat) {
            $rootNode->appendChild($this->createProductNode($doc, $submission, $publicationFormat));
        }
    }

    //
    // ONIX conversion functions
    //
    /**
     * Create and return a node representing the ONIX Header metadata for this submission.
     *
     * @param \DOMDocument $doc
     *
     * @return \DOMElement
     */
    public function createHeaderNode($doc)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        $headNode = $doc->createElementNS($deployment->getNamespace(), 'Header');
        $senderNode = $doc->createElementNS($deployment->getNamespace(), 'Sender');

        // Assemble SenderIdentifier element.
        $senderIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'SenderIdentifier');
        $senderIdentifierNode->appendChild($this->_buildTextNode($doc, 'SenderIDType', $context->getData('codeType')));
        $senderIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $context->getData('codeValue')));

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
     *
     * @param \DOMDocument $doc
     * @param Submission $submission
     * @param PublicationFormat $publicationFormat
     *
     * @return \DOMElement
     */
    public function createProductNode($doc, $submission, $publicationFormat)
    {
        /** @var Onix30ExportDeployment */
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */

        $productNode = $doc->createElementNS($deployment->getNamespace(), 'Product');

        $request = Application::get()->getRequest();
        $productNode->appendChild($this->_buildTextNode($doc, 'RecordReference', $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $context->getPath(), 'monograph', 'view', [$submission->getId()], urlLocaleForPage: '')));
        $productNode->appendChild($this->_buildTextNode($doc, 'NotificationType', '03'));
        $productNode->appendChild($this->_buildTextNode($doc, 'RecordSourceType', '04')); // Bibliographic agency

        $identifierGiven = false;

        $identificationCodes = $publicationFormat->getIdentificationCodes();

        while ($code = $identificationCodes->next()) {
            $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'ProductIDType', $code->getCode())); // GTIN-13 (ISBN-13 as GTIN)
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $code->getValue()));
            $productNode->appendChild($productIdentifierNode);

            unset($productIdentifierNode);
            unset($code);

            $identifierGiven = true;
        }

        // Deal with the possibility of a DOI pubId.
        if ($context->areDoisEnabled() && $publicationFormat->getDoi()) {
            $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'ProductIDType', '06')); // DOI
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $publicationFormat->getDoi())); // GTIN-13 (ISBN-13 as GTIN)
            $productNode->appendChild($productIdentifierNode);

            unset($productIdentifierNode);

            $identifierGiven = true;
        }

        if (!$identifierGiven) {
            $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'ProductIDType', '01')); // Id
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDTypeName', 'PKID'));
            $productIdentifierNode->appendChild($this->_buildTextNode($doc, 'IDValue', $publicationFormat->getId()));

            $productNode->appendChild($productIdentifierNode);
        }

        /* --- Descriptive Detail --- */
        $descDetailNode = $doc->createElementNS($deployment->getNamespace(), 'DescriptiveDetail');

        $descDetailNode->appendChild($this->_buildTextNode(
            $doc,
            'ProductComposition',
            $publicationFormat->getProductCompositionCode() ? $publicationFormat->getProductCompositionCode() : '00'
        )); // single item, trade only, etc.  Default to single item if not specified.

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

        if ($publicationFormat->getCountryManufactureCode() != '') {
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
        $seriesId = $submission->getCurrentPublication()->getData('seriesId');
        $series = $seriesId ? Repo::section()->get($seriesId) : null;
        if ($series != null) {
            if ($submission->getCurrentPublication()->getData('seriesPosition')) {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'PartNumber', $submission->getCurrentPublication()->getData('seriesPosition')));
            }

            if ($series->getLocalizedPrefix() == '' || $series->getLocalizedTitle(false) == '') {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleText', trim(join(' ', [$series->getLocalizedPrefix(), $series->getLocalizedTitle(false)]))));
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

        $publication = $submission->getCurrentPublication();
        if (!$publication->getLocalizedData('prefix') || !$publication->getLocalizedData('title')) {
            $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleText', trim($publication->getLocalizedData('prefix') ?? $publication->getLocalizedTitle())));
        } else {
            if ($publication->getLocalizedData('prefix')) {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitlePrefix', $publication->getLocalizedData('prefix')));
            }
            $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleWithoutPrefix', strip_tags($publication->getLocalizedData('title'))));
        }

        if ($subTitle = $publication->getLocalizedSubTitle($publication->getData('locale'))) {
            $titleElementNode->appendChild($this->_buildTextNode($doc, 'Subtitle', $subTitle));
        }

        /* --- Contributor information --- */

        $authors = $publication->getData('authors'); // sorts by sequence.
        $sequence = 1;
        foreach ($authors as $author) {
            $contributorNode = $doc->createElementNS($deployment->getNamespace(), 'Contributor');
            $contributorNode->appendChild($this->_buildTextNode($doc, 'SequenceNumber', $sequence));

            $userGroup = Repo::userGroup()->get($author->getUserGroupId());

            $userGroupOnixMap = ['AU' => 'A01', 'VE' => 'B01', 'CA' => 'A01', 'Trans' => 'B06', 'PE' => 'B21']; // From List17, ContributorRole types.

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
            unset($userGroup);
            unset($author);
        }

        if (sizeof($authors) == 0) { // this will probably never happen, but include the possibility.
            $descDetailNode->appendChild($this->_buildTextNode($doc, 'NoContributor', '')); // empty state of fact.
        }

        /* --- Add Language element --- */

        $languageNode = $doc->createElementNS($deployment->getNamespace(), 'Language');
        $languageNode->appendChild($this->_buildTextNode($doc, 'LanguageRole', '01'));
        $onixLanguageCode = $onixCodelistItemDao->getCodeFromValue($submission->getData('locale'), 'List74');
        if ($onixLanguageCode != '') {
            $languageNode->appendChild($this->_buildTextNode($doc, 'LanguageCode', $onixLanguageCode));
            $descDetailNode->appendChild($languageNode);
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

        $submissionSubjectDao = DAORegistry::getDAO('SubmissionSubjectDAO'); /** @var SubmissionSubjectDAO $submissionSubjectDao */
        $allSubjects = $submissionSubjectDao->getSubjects($publication->getId(), array_keys(Locale::getSupportedFormLocales()));
        $uniqueSubjects = [];
        foreach ($allSubjects as $locale => $subjects) {
            $uniqueSubjects = array_merge($uniqueSubjects, $subjects);
        }

        if (sizeof($uniqueSubjects) > 0) {
            $subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectCode', trim(join(', ', $uniqueSubjects))));
        }

        $descDetailNode->appendChild($subjectNode);

        /* --- Add Audience elements --- */

        if ($submission->getData('audience')) {
            $audienceNode = $doc->createElementNS($deployment->getNamespace(), 'Audience');
            $descDetailNode->appendChild($audienceNode);
            $audienceNode->appendChild($this->_buildTextNode($doc, 'AudienceCodeType', $submission->getData('audience')));
            $audienceNode->appendChild($this->_buildTextNode($doc, 'AudienceCodeValue', '01'));
        }

        if ($submission->getData('audienceRangeQualifier') != '') {
            $audienceRangeNode = $doc->createElementNS($deployment->getNamespace(), 'AudienceRange');
            $descDetailNode->appendChild($audienceRangeNode);
            $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeQualifier', $submission->getData('audienceRangeQualifier')));

            if ($submission->getData('audienceRangeExact') != '') {
                $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangePrecision', '01')); // Exact, list31
                $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeValue', $submission->getData('audienceRangeExact')));
            } else { // if not exact, then include the From -> To possibilities
                if ($submission->getData('audienceRangeFrom') != '') {
                    $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangePrecision', '03')); // from
                    $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeValue', $submission->getData('audienceRangeFrom')));
                }
                if ($submission->getData('audienceRangeTo') != '') {
                    $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangePrecision', '04')); // to
                    $audienceRangeNode->appendChild($this->_buildTextNode($doc, 'AudienceRangeValue', $submission->getData('audienceRangeTo')));
                }
            }
        }

        $productNode->appendChild($descDetailNode);
        unset($descDetailNode);

        // Back to assembling Product node.
        /* --- Collateral Detail --- */

        $collateralDetailNode = $doc->createElementNS($deployment->getNamespace(), 'CollateralDetail');
        $productNode->appendChild($collateralDetailNode);

        $abstract = strip_tags($publication->getLocalizedData('abstract'));

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
        
        $supportingResourceNode = $doc->createElementNS($deployment->getNamespace(), 'SupportingResource');
        $collateralDetailNode->appendChild($supportingResourceNode);
        $supportingResourceNode->appendChild($this->_buildTextNode($doc, 'ResourceContentType', '01')); // Front cover
        $supportingResourceNode->appendChild($this->_buildTextNode($doc, 'ContentAudience', '00')); // Any audience
        $supportingResourceNode->appendChild($this->_buildTextNode($doc, 'ResourceMode', '03')); // A still image

        $resourceVersionNode = $doc->createElementNS($deployment->getNamespace(), 'ResourceVersion');
        $supportingResourceNode->appendChild($resourceVersionNode);
        $resourceVersionNode->appendChild($this->_buildTextNode($doc, 'ResourceForm', '01')); // Linkable resource
        $resourceVersionNode->appendChild($this->_buildTextNode($doc, 'ResourceLink', $publication->getLocalizedCoverImageUrl($context->getId(), $publication->getData('locale'))));

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
        $publisherNode->appendChild($this->_buildTextNode($doc, 'PublisherName', $context->getData('publisher')));
        if ($context->getData('location') != '') {
            $publishingDetailNode->appendChild($this->_buildTextNode($doc, 'CityOfPublication', $context->getData('location')));
        }

        $websiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
        $publisherNode->appendChild($websiteNode);

        $websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Publisher's B2C website
        $websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $context->getPath(), urlLocaleForPage: '')));

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
                $salesRightsNode->appendChild($this->_buildTextNode($doc, 'SalesRightsType', $salesRights->getType()));

                // now do territories and countries.
                $territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');

                if (sizeof($salesRights->getRegionsIncluded()) > 0 && sizeof($salesRights->getCountriesExcluded()) > 0) {
                    $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $salesRights->getRegionsIncluded()))));
                    $territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $salesRights->getCountriesExcluded()))));
                } elseif (sizeof($salesRights->getCountriesIncluded()) > 0) {
                    $territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $salesRights->getCountriesIncluded()))));
                }

                if (sizeof($salesRights->getRegionsExcluded()) > 0) {
                    $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                }

                // Include territory and sales rights if the territory isn't empty
                if ($territoryNode->firstElementChild) {
                    $salesRightsNode->appendChild($territoryNode);
                    $publishingDetailNode->appendChild($salesRightsNode);
                } else {
                    $deployment->addWarning(Application::ASSOC_TYPE_MONOGRAPH, $deployment->getSubmission()->getId(), __('plugins.importexport.common.error.salesRightRequiresTerritory'));
                }
            } else { // found the SalesRights object that is assigned 'rest of world'.
                $salesRightsROW = $salesRights; // stash this for later since it always goes last.
            }
            unset($salesRights);
        }
        if ($salesRightsROW != null) {
            $publishingDetailNode->appendChild($this->_buildTextNode($doc, 'ROWSalesRightsType', $salesRightsROW->getType()));
        }

        /* --- Product Supply.  We create one of these per defined Market. --- */

        $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
        $markets = $publicationFormat->getMarkets();

        while ($market = $markets->next()) {
            $productSupplyNode = $doc->createElementNS($deployment->getNamespace(), 'ProductSupply');
            $productNode->appendChild($productSupplyNode);

            $marketNode = $doc->createElementNS($deployment->getNamespace(), 'Market');
            $productSupplyNode->appendChild($marketNode);

            $territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');

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

            // Include territory if it's not empty
            if ($territoryNode->firstElementChild) {
                $marketNode->appendChild($territoryNode);
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
            } else { // No suppliers specified, use the Press settings instead.
                $supplierNode = $doc->createElementNS($deployment->getNamespace(), 'Supplier');
                $supplyDetailNode->appendChild($supplierNode);

                $supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierRole', '09')); // Publisher supplying to end customers
                $supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierName', $context->getData('publisher')));

                if ($context->getData('contactEmail') != '') {
                    $supplierNode->appendChild($this->_buildTextNode($doc, 'EmailAddress', $context->getData('contactEmail')));
                }

                $supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                $supplierNode->appendChild($supplierWebsiteNode);

                $supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
                $supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $context->getPath(), urlLocaleForPage: '')));

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

            $supplyDetailNode->appendChild($this->_buildTextNode(
                $doc,
                'ProductAvailability',
                $publicationFormat->getProductAvailabilityCode() ? $publicationFormat->getProductAvailabilityCode() : '20'
            )); // assume 'available' if not specified.

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
     *
     * @param \DOMDocument $doc
     * @param Onix30ExportDeployment $deployment
     * @param string $type
     * @param string $measurement
     * @param string $unitCode
     *
     * @return \DOMElement
     */
    public function _createMeasurementNode($doc, $deployment, $type, $measurement, $unitCode)
    {
        $measureNode = $doc->createElementNS($deployment->getNamespace(), 'Measure');

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
     *
     * @param \DOMDocument $doc
     * @param ONIX30ExportDeployment $deployment
     * @param string $type
     * @param string $extentValue
     * @param string $extentUnit
     *
     * @return \DOMElement
     */
    public function _createExtentNode($doc, $deployment, $type, $extentValue, $extentUnit)
    {
        $extentNode = $doc->createElementNS($deployment->getNamespace(), 'Extent');

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
     *
     * @param \DOMDocument $doc
     * @param string $nodeName
     * @param string $textContent
     *
     * @return \DOMElement
     */
    public function _buildTextNode($doc, $nodeName, $textContent)
    {
        $deployment = $this->getDeployment();
        $node = $doc->createElementNS($deployment->getNamespace(), $nodeName);
        $node->appendChild($doc->createTextNode($textContent));
        return $node;
    }
}
