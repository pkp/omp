<?php

/**
 * @file plugins/importexport/onix30/filter/MonographONIX30XmlFilter.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
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
use DOMElement;
use DOMException;
use Exception;
use PKP\db\DAORegistry;
use PKP\filter\FilterGroup;
use PKP\i18n\LocaleConversion;
use PKP\plugins\importexport\native\filter\NativeExportFilter;
use PKP\userGroup\UserGroup;

class MonographONIX30XmlFilter extends NativeExportFilter
{
    /**  */
    public DOMDocument $doc;

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
     * @param Submission $submissions | array Monographs to export
     *
     * @throws DOMException
     *
     * @return DOMDocument
     *
     * @see Filter::process()
     *
     */
    public function &process(&$submissions)
    {
        // Create the XML document
        $doc = new DOMDocument('1.0', 'utf-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $this->doc = $doc;
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
     * @param DOMDocument $doc The main XML Document object
     * @param DOMElement $rootNode The root node of the document, on which the submission node will get attached
     * @param Submission $submission The submission we want to export and attach.
     *
     * @throws DOMException
     */
    public function createSubmissionNode(DOMDocument $doc, DOMElement $rootNode, Submission $submission)
    {
        $publicationFormats = $submission->getCurrentPublication()->getData('publicationFormats');

        // Collect identifiers for all publication formats to connect related products
        $identificationCodes = [];
        foreach ($publicationFormats as $publicationFormat) {
            $pubIdentificationCodes = $publicationFormat->getIdentificationCodes();
            $pubId = $publicationFormat->getId();
            while ($code = $pubIdentificationCodes->next()) {
                $identificationCodes[$pubId][$code->getCode()] = $code->getValue();
            }
        }

        // Append all publication formats as Product nodes.
        foreach ($publicationFormats as $publicationFormat) {
            $rootNode->appendChild($this->createProductNode($doc, $submission, $publicationFormat, $identificationCodes));
        }
    }

    //
    // ONIX conversion functions
    //
    /**
     * Create and return a node representing the ONIX Header metadata for this submission.
     *
     * @param DOMDocument $doc
     *
     * @throws DOMException
     *
     * @return DOMElement
     */
    public function createHeaderNode($doc)
    {
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        $headNode = $doc->createElementNS($deployment->getNamespace(), 'Header');
        $senderNode = $doc->createElementNS($deployment->getNamespace(), 'Sender');

        // Assemble SenderIdentifier element.
        $senderIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'SenderIdentifier');
        $senderIdentifierNode->appendChild($this->buildTextNode($doc, 'SenderIDType', $context->getData('codeType')));
        $senderIdentifierNode->appendChild($this->buildTextNode($doc, 'IDValue', $context->getData('codeValue')));

        $senderNode->appendChild($senderIdentifierNode);

        // Assemble SenderName element.
        $senderNode->appendChild($this->buildTextNode($doc, 'SenderName', $context->getName($context->getPrimaryLocale())));
        $senderNode->appendChild($this->buildTextNode($doc, 'ContactName', $context->getContactName()));
        $senderNode->appendChild($this->buildTextNode($doc, 'EmailAddress', $context->getContactEmail()));

        $headNode->appendChild($senderNode);

        // add SentDateTime element.
        $headNode->appendChild($this->buildTextNode($doc, 'SentDateTime', date('Ymd')));

        return $headNode;
    }

    /**
     * Create and return a node representing the ONIX Product metadata for this submission.
     *
     *
     * @throws DOMException
     * @throws Exception
     */
    public function createProductNode(DOMDocument $doc, Submission $submission, PublicationFormat $publicationFormat, array $identificationCodes): DOMElement
    {
        /** @var Onix30ExportDeployment $deployment */
        $deployment = $this->getDeployment();
        $context = $deployment->getContext();

        /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');

        $productNode = $doc->createElementNS($deployment->getNamespace(), 'Product');
        $request = Application::get()->getRequest();

        // Create the RecordReference
        $host = $request->getServerHost(null, false);
        $path = $context->getPath();
        $pubId = $publicationFormat->getId();
        $recordReference = $host . '.' . $path . '.' . $pubId;

        $productNode->appendChild($this->buildTextNode($doc, 'RecordReference', $recordReference));
        $productNode->appendChild($this->buildTextNode($doc, 'NotificationType', '03'));
        $productNode->appendChild($this->buildTextNode($doc, 'RecordSourceType', '04')); // Bibliographic agency

        $identifierGiven = false;

        if (array_key_exists($publicationFormat->getId(), $identificationCodes)) {
            foreach ($identificationCodes[$publicationFormat->getId()] as $code => $value) {
                $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
                $productIdentifierNode->appendChild($this->buildTextNode($doc, 'ProductIDType', $code));
                $productIdentifierNode->appendChild($this->buildTextNode($doc, 'IDValue', $value));
                $productNode->appendChild($productIdentifierNode);

                unset($productIdentifierNode);
                unset($code);

                $identifierGiven = true;
            }
        }

        // Deal with the possibility of a DOI pubId.
        if ($context->areDoisEnabled() && $publicationFormat->getDoi()) {
            $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
            $productIdentifierNode->appendChild($this->buildTextNode($doc, 'ProductIDType', '06')); // DOI
            $productIdentifierNode->appendChild($this->buildTextNode($doc, 'IDValue', $publicationFormat->getDoi()));
            $productNode->appendChild($productIdentifierNode);

            unset($productIdentifierNode);

            $identifierGiven = true;
        }

        if (!$identifierGiven) {
            $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
            $productIdentifierNode->appendChild($this->buildTextNode($doc, 'ProductIDType', '01')); // Id
            $productIdentifierNode->appendChild($this->buildTextNode($doc, 'IDTypeName', 'PKID'));
            $productIdentifierNode->appendChild($this->buildTextNode($doc, 'IDValue', $publicationFormat->getId()));

            $productNode->appendChild($productIdentifierNode);
        }

        /* --- Descriptive Detail --- */
        $descDetailNode = $doc->createElementNS($deployment->getNamespace(), 'DescriptiveDetail');

        $descDetailNode->appendChild($this->buildTextNode(
            $doc,
            'ProductComposition',
            $publicationFormat->getProductCompositionCode() ? $publicationFormat->getProductCompositionCode() : '00'
        )); // single item, trade only, etc. Default to single item if not specified.

        $descDetailNode->appendChild($this->buildTextNode($doc, 'ProductForm', $publicationFormat->getEntryKey())); // paperback, hardcover, etc

        if ($publicationFormat->getProductFormDetailCode() != '') {
            $descDetailNode->appendChild($this->buildTextNode($doc, 'ProductFormDetail', $publicationFormat->getProductFormDetailCode())); // refinement of ProductForm
        }

        /* --- Physical Book Measurements --- */
        if ($publicationFormat->getPhysicalFormat()) {
            // '01' => 'Height', '02' => 'Width', '03' => 'Thickness', '08' => 'Weight'
            if ($publicationFormat->getHeight() != '') {
                $measureNode = $this->createMeasurementNode($doc, $deployment, '01', $publicationFormat->getHeight(), $publicationFormat->getHeightUnitCode());
                $descDetailNode->appendChild($measureNode);
                unset($measureNode);
            }

            if ($publicationFormat->getWidth() != '') {
                $measureNode = $this->createMeasurementNode($doc, $deployment, '02', $publicationFormat->getWidth(), $publicationFormat->getWidthUnitCode());
                $descDetailNode->appendChild($measureNode);
                unset($measureNode);
            }

            if ($publicationFormat->getThickness() != '') {
                $measureNode = $this->createMeasurementNode($doc, $deployment, '03', $publicationFormat->getThickness(), $publicationFormat->getThicknessUnitCode());
                $descDetailNode->appendChild($measureNode);
                unset($measureNode);
            }

            if ($publicationFormat->getWeight() != '') {
                $measureNode = $this->createMeasurementNode($doc, $deployment, '08', $publicationFormat->getWeight(), $publicationFormat->getWeightUnitCode());
                $descDetailNode->appendChild($measureNode);
                unset($measureNode);
            }
        }

        if ($publicationFormat->getCountryManufactureCode() != '') {
            $descDetailNode->appendChild($this->buildTextNode($doc, 'CountryOfManufacture', $publicationFormat->getCountryManufactureCode()));
        }

        if (!$publicationFormat->getPhysicalFormat() && $publicationFormat->getTechnicalProtectionCode() != '') {
            $descDetailNode->appendChild($this->buildTextNode($doc, 'EpubTechnicalProtection', $publicationFormat->getTechnicalProtectionCode()));
        }

        /* --- License information --- */

        $publication = $submission->getCurrentPublication();
        $pubLocale = $publication->getData('locale');

        if ($publication->isCCLicense()) {
            $licenseOpts = Application::getCCLicenseOptions();
            $licenseUrl = $publication->getData('licenseUrl');
            if (array_key_exists($licenseUrl, $licenseOpts)) {
                $licenseName = (__($licenseOpts[$licenseUrl], [], $pubLocale));

                $epubLicenseNode = $doc->createElementNS($deployment->getNamespace(), 'EpubLicense');
                $descDetailNode->appendChild($epubLicenseNode);
                $epubLicenseNode->appendChild($this->buildTextNode($doc, 'EpubLicenseName', $licenseName));

                $epubLicenseExpressionNode = $doc->createElementNS($deployment->getNamespace(), 'EpubLicenseExpression');
                $epubLicenseNode->appendChild($epubLicenseExpressionNode);

                $epubLicenseExpressionNode->appendChild($this->buildTextNode($doc, 'EpubLicenseExpressionType', '02'));
                $epubLicenseExpressionNode->appendChild($this->buildTextNode($doc, 'EpubLicenseExpressionLink', $licenseUrl));
            }
        }

        /* --- Collection information, first for series and then for product --- */

        /* --- Series information, if this monograph is part of one. --- */
        $seriesId = $submission->getCurrentPublication()->getData('seriesId');
        $series = $seriesId ? Repo::section()->get($seriesId) : null;
        if ($series != null) {
            $seriesCollectionNode = $doc->createElementNS($deployment->getNamespace(), 'Collection');
            $seriesCollectionNode->appendChild($this->buildTextNode($doc, 'CollectionType', '10')); // publisher series.

            $seriesTitleDetailNode = $doc->createElementNS($deployment->getNamespace(), 'TitleDetail');
            $seriesTitleDetailNode->appendChild($this->buildTextNode($doc, 'TitleType', '01'));
            $seriesCollectionNode->appendChild($seriesTitleDetailNode);

            $titleElementNode = $doc->createElementNS($deployment->getNamespace(), 'TitleElement');
            $titleElementNode->appendChild($this->buildTextNode($doc, 'TitleElementLevel', '02')); // Collection level title
            $seriesTitleDetailNode->appendChild($titleElementNode);

            if ($submission->getCurrentPublication()->getData('seriesPosition')) {
                $titleElementNode->appendChild($this->buildTextNode($doc, 'PartNumber', $submission->getCurrentPublication()->getData('seriesPosition')));
            }

            $seriesLocale = $pubLocale;
            // If the series title doesn't exist in the submission locale, use the press locale
            if ($series->getTitle($seriesLocale, false) == '') {
                $seriesLocale = $context->getPrimaryLocale();
            }

            if ($series->getPrefix($seriesLocale) != '') {
                $titleElementNode->appendChild($this->buildTextNode($doc, 'TitlePrefix', $series->getPrefix($seriesLocale)));
            } else {
                $titleElementNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'NoPrefix'));
            }

            $titleElementNode->appendChild($this->buildTextNode($doc, 'TitleWithoutPrefix', $series->getTitle($seriesLocale, false)));

            if ($series->getSubtitle($seriesLocale) != '') {
                $titleElementNode->appendChild($this->buildTextNode($doc, 'Subtitle', $series->getSubtitle($seriesLocale)));
            }
        } else {
            $seriesCollectionNode = $doc->createElementNS($deployment->getNamespace(), 'NoCollection');
        }
        $descDetailNode->appendChild($seriesCollectionNode);

        /* --- and now product level info --- */

        $productTitleDetailNode = $doc->createElementNS($deployment->getNamespace(), 'TitleDetail');
        $productTitleDetailNode->appendChild($this->buildTextNode($doc, 'TitleType', '01'));
        $descDetailNode->appendChild($productTitleDetailNode);

        $titleElementNode = $doc->createElementNS($deployment->getNamespace(), 'TitleElement');
        $titleElementNode->appendChild($this->buildTextNode($doc, 'TitleElementLevel', '01'));

        $productTitleDetailNode->appendChild($titleElementNode);

        if ($publication->getData('prefix', $pubLocale)) {
            $titleElementNode->appendChild($this->buildTextNode($doc, 'TitlePrefix', $publication->getData('prefix', $pubLocale)));
        } else {
            $titleElementNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'NoPrefix'));
        }

        $titleElementNode->appendChild($this->buildTextNode($doc, 'TitleWithoutPrefix', $publication->getData('title', $pubLocale)));

        if ($subTitle = $publication->getData('subtitle', $pubLocale)) {
            $titleElementNode->appendChild($this->buildTextNode($doc, 'Subtitle', $subTitle));
        }

        /* --- Contributor information --- */

        $authors = $publication->getData('authors'); // sorts by sequence.
        $sequence = 1;
        foreach ($authors as $author) {
            $contributorNode = $doc->createElementNS($deployment->getNamespace(), 'Contributor');
            $contributorNode->appendChild($this->buildTextNode($doc, 'SequenceNumber', $sequence));

            $userGroup = UserGroup::find($author->getUserGroupId());

            $userGroupOnixMap = [
                'default.groups.name.author' => 'A01',
                'default.groups.name.volumeEditor' => 'B01',
                'default.groups.name.chapterAuthor' => 'A01',
                'default.groups.name.translator' => 'B06',
                'default.groups.name.editor' => 'B21'
            ]; // From List17, ContributorRole types.

            $nameKey = $userGroup->nameLocaleKey;
            $role = array_key_exists($nameKey, $userGroupOnixMap) ? $userGroupOnixMap[$nameKey] : 'Z99'; // Z99 - unknown contributor type.

            $contributorNode->appendChild($this->buildTextNode($doc, 'ContributorRole', $role));
            $contributorNode->appendChild($this->buildTextNode($doc, 'PersonName', $author->getFullName(false, false, $pubLocale)));
            $contributorNode->appendChild($this->buildTextNode($doc, 'PersonNameInverted', $author->getFullName(false, true, $pubLocale)));
            $contributorNode->appendChild($this->buildTextNode($doc, 'NamesBeforeKey', $author->getGivenName($pubLocale)));
            if ($author->getFamilyName($pubLocale) != '') {
                $contributorNode->appendChild($this->buildTextNode($doc, 'KeyNames', $author->getFamilyName($pubLocale)));
            } else {
                $contributorNode->appendChild($this->buildTextNode($doc, 'KeyNames', $author->getFullName(false, false, $pubLocale)));
            }

            if ($author->getBiography($pubLocale) != '') {
                $contributorNode->appendChild($this->buildTextNode($doc, 'BiographicalNote', $author->getBiography($pubLocale)));
            }

            if ($author->getCountry() != '') {
                $contributorPlaceNode = $doc->createElementNS($deployment->getNamespace(), 'ContributorPlace');
                $contributorNode->appendChild($contributorPlaceNode);
                $contributorPlaceNode->appendChild($this->buildTextNode($doc, 'ContributorPlaceRelator', '04'));
                $contributorPlaceNode->appendChild($this->buildTextNode($doc, 'CountryCode', $author->getCountry()));
                unset($contributorPlaceNode);
            }

            $sequence++;
            $descDetailNode->appendChild($contributorNode);

            unset($contributorNode);
            unset($userGroup);
            unset($author);
        }

        if (sizeof($authors) == 0) { // this will probably never happen, but include the possibility.
            $descDetailNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'NoContributor')); // empty state of fact.
        }

        /* --- Add Language element --- */

        if ($onixCodelistItemDao->codeExistsInList(LocaleConversion::get3LetterIsoFromLocale($pubLocale), '74')) {
            $languageNode = $doc->createElementNS($deployment->getNamespace(), 'Language');
            $languageNode->appendChild($this->buildTextNode($doc, 'LanguageRole', '01'));
            $languageNode->appendChild($this->buildTextNode($doc, 'LanguageCode', LocaleConversion::get3LetterIsoFromLocale($pubLocale)));
            $descDetailNode->appendChild($languageNode);
        }

        /* --- add Extents for 03 (front matter), 04 (back matter), 08 for digital works ---*/

        if ($publicationFormat->getFrontMatter() > 0) {
            // 03 - Pages
            $extentNode = $this->createExtentNode($doc, $deployment, '03', $publicationFormat->getFrontMatter(), '03');
            $descDetailNode->appendChild($extentNode);
            unset($extentNode);
        }

        if ($publicationFormat->getBackMatter() > 0) {
            $extentNode = $this->createExtentNode($doc, $deployment, '04', $publicationFormat->getBackMatter(), '03');
            $descDetailNode->appendChild($extentNode);
            unset($extentNode);
        }

        if (!$publicationFormat->getPhysicalFormat()) { // EBooks and digital content have extent information about file sizes
            $fileSize = $publicationFormat->getFileSize() ? $publicationFormat->getFileSize() : $publicationFormat->getCalculatedFileSize();
            $extentNode = $this->createExtentNode($doc, $deployment, '22', $fileSize, '19'); // 22 -> Filesize, 19 -> Mbytes
            $descDetailNode->appendChild($extentNode);
            unset($extentNode);
        }

        /* --- Add Subject elements --- */

        if ($publication->getData('subjects')) {
            $subjectNode = $doc->createElementNS($deployment->getNamespace(), 'Subject');
            $mainSubjectNode = $doc->createElementNS($deployment->getNamespace(), 'MainSubject'); // Always empty as per 3.0 spec.

            $subjectNode->appendChild($mainSubjectNode);
            $subjectNode->appendChild($this->buildTextNode($doc, 'SubjectSchemeIdentifier', '12')); // 12 is BIC subject category code list.
            $subjectNode->appendChild($this->buildTextNode($doc, 'SubjectSchemeVersion', '2')); // Version 2 of ^^

            $allSubjects = ($publication->getData('subjects')[$pubLocale]);
            $subjectNode->appendChild($this->buildTextNode($doc, 'SubjectCode', trim(join(', ', $allSubjects))));
            $descDetailNode->appendChild($subjectNode);
        }

        if ($publication->getData('keywords')) {
            $allKeywords = ($publication->getData('keywords')[$pubLocale]);
            $keywordNode = $doc->createElementNS($deployment->getNamespace(), 'Subject');
            $keywordNode->appendChild($this->buildTextNode($doc, 'SubjectSchemeIdentifier', '20')); // Keywords
            $keywordNode->appendChild($this->buildTextNode($doc, 'SubjectHeadingText', trim(join(', ', $allKeywords))));
            $descDetailNode->appendChild($keywordNode);
        }

        /* --- Add Audience elements --- */

        if ($submission->getData('audience')) {
            $audienceNode = $doc->createElementNS($deployment->getNamespace(), 'Audience');
            $descDetailNode->appendChild($audienceNode);
            $audienceNode->appendChild($this->buildTextNode($doc, 'AudienceCodeType', $submission->getData('audience')));
            $audienceNode->appendChild($this->buildTextNode($doc, 'AudienceCodeValue', '01'));
        }

        if ($submission->getData('audienceRangeQualifier') != '') {
            $audienceRangeNode = $doc->createElementNS($deployment->getNamespace(), 'AudienceRange');
            $descDetailNode->appendChild($audienceRangeNode);
            $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangeQualifier', $submission->getData('audienceRangeQualifier')));

            if ($submission->getData('audienceRangeExact') != '') {
                $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangePrecision', '01')); // Exact, list31
                $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangeValue', $submission->getData('audienceRangeExact')));
            } else { // if not exact, then include the From -> To possibilities
                if ($submission->getData('audienceRangeFrom') != '') {
                    $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangePrecision', '03')); // from
                    $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangeValue', $submission->getData('audienceRangeFrom')));
                }
                if ($submission->getData('audienceRangeTo') != '') {
                    $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangePrecision', '04')); // to
                    $audienceRangeNode->appendChild($this->buildTextNode($doc, 'AudienceRangeValue', $submission->getData('audienceRangeTo')));
                }
            }
        }

        $productNode->appendChild($descDetailNode);
        unset($descDetailNode);

        // Back to assembling Product node.
        /* --- Collateral Detail --- */

        $collateralDetailNode = $doc->createElementNS($deployment->getNamespace(), 'CollateralDetail');
        $productNode->appendChild($collateralDetailNode);

        $abstract = strip_tags($publication->getData('abstract', $pubLocale));

        $textContentNode = $doc->createElementNS($deployment->getNamespace(), 'TextContent');
        $collateralDetailNode->appendChild($textContentNode);
        $textContentNode->appendChild($this->buildTextNode($doc, 'TextType', '02')); // short description
        $textContentNode->appendChild($this->buildTextNode($doc, 'ContentAudience', '00')); // Any audience
        $textContentNode->appendChild($this->buildTextNode($doc, 'Text', substr($abstract, 0, 250))); // Any audience

        $textContentNode = $doc->createElementNS($deployment->getNamespace(), 'TextContent');
        $collateralDetailNode->appendChild($textContentNode);

        $textContentNode->appendChild($this->buildTextNode($doc, 'TextType', '03')); // description
        $textContentNode->appendChild($this->buildTextNode($doc, 'ContentAudience', '00')); // Any audience
        $textContentNode->appendChild($this->buildTextNode($doc, 'Text', $abstract)); // Any audience

        $supportingResourceNode = $doc->createElementNS($deployment->getNamespace(), 'SupportingResource');
        $collateralDetailNode->appendChild($supportingResourceNode);
        $supportingResourceNode->appendChild($this->buildTextNode($doc, 'ResourceContentType', '01')); // Front cover
        $supportingResourceNode->appendChild($this->buildTextNode($doc, 'ContentAudience', '00')); // Any audience
        $supportingResourceNode->appendChild($this->buildTextNode($doc, 'ResourceMode', '03')); // A still image

        $resourceVersionNode = $doc->createElementNS($deployment->getNamespace(), 'ResourceVersion');
        $supportingResourceNode->appendChild($resourceVersionNode);
        $resourceVersionNode->appendChild($this->buildTextNode($doc, 'ResourceForm', '01')); // Linkable resource
        $resourceVersionNode->appendChild($this->buildTextNode($doc, 'ResourceLink', $publication->getCoverImageUrl($context->getId(), $pubLocale)));

        /* --- Publishing Detail --- */

        $publishingDetailNode = $doc->createElementNS($deployment->getNamespace(), 'PublishingDetail');
        $productNode->appendChild($publishingDetailNode);

        if ($publicationFormat->getImprint()) {
            $imprintNode = $doc->createElementNS($deployment->getNamespace(), 'Imprint');
            $publishingDetailNode->appendChild($imprintNode);
            $imprintNode->appendChild($this->buildTextNode($doc, 'ImprintName', $publicationFormat->getImprint()));
            unset($imprintNode);
        }

        $publisherNode = $doc->createElementNS($deployment->getNamespace(), 'Publisher');
        $publishingDetailNode->appendChild($publisherNode);

        $publisherNode->appendChild($this->buildTextNode($doc, 'PublishingRole', '01')); // Publisher
        $publisherNode->appendChild($this->buildTextNode($doc, 'PublisherName', $context->getData('publisher')));
        if ($context->getData('location') != '') {
            $publishingDetailNode->appendChild($this->buildTextNode($doc, 'CityOfPublication', $context->getData('location')));
        }

        $websiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
        $publisherNode->appendChild($websiteNode);

        $websiteNode->appendChild($this->buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Publisher's B2C website
        $websiteNode->appendChild($this->buildTextNode($doc, 'WebsiteLink', $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $context->getPath(), urlLocaleForPage: '')));

        $websiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
        $publisherNode->appendChild($websiteNode);

        $websiteNode->appendChild($this->buildTextNode($doc, 'WebsiteRole', '29')); // 29 -> Web page for full content

        $submissionBestId = $publication->getData('submissionId');

        if ($publication->getData('urlPath') != '') {
            $submissionBestId = $publication->getData('urlPath');
        }

        $websiteNode->appendChild($this->buildTextNode($doc, 'WebsiteLink', $request->url($context->getPath(), 'catalog', 'book', [$submissionBestId])));

        /* --- Publishing Dates --- */

        $publicationDates = $publicationFormat->getPublicationDates();
        while ($date = $publicationDates->next()) {
            $pubDateNode = $doc->createElementNS($deployment->getNamespace(), 'PublishingDate');
            $publishingDetailNode->appendChild($pubDateNode);

            $pubDateNode->appendChild($this->buildTextNode($doc, 'PublishingDateRole', $date->getRole()));

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
            $salesRightsNode = $doc->createElementNS($deployment->getNamespace(), 'SalesRights');
            $publishingDetailNode->appendChild($salesRightsNode);
            $salesRightsNode->appendChild($this->buildTextNode($doc, 'SalesRightsType', $salesRights->getType()));

            // now do territories and countries.
            $territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');
            $salesRightsNode->appendChild($territoryNode);

            $salesCountriesIncluded = sizeof($salesRights->getCountriesIncluded()) > 0;
            $salesRegionsIncluded = sizeof($salesRights->getRegionsIncluded()) > 0;
            $salesCountriesExcluded = sizeof($salesRights->getCountriesExcluded()) > 0;
            $salesRegionsExcluded = sizeof($salesRights->getRegionsExcluded()) > 0;

            if ($salesRights->getROWSetting()) {
                $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsIncluded', 'WORLD'));
                $salesRightsROW = $salesRights;
            } elseif ($salesCountriesIncluded) {
                $territoryNode->appendChild($this->buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $salesRights->getCountriesIncluded()))));
                if (!in_array('WORLD', $salesRights->getRegionsIncluded())) {
                    if ($salesRegionsIncluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $salesRights->getRegionsIncluded()))));
                    }
                    if ($salesRegionsExcluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                    }
                } elseif ($salesRegionsExcluded) {
                    $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                }
            } elseif ($salesRegionsIncluded) {
                $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $salesRights->getRegionsIncluded()))));
                if (in_array('WORLD', $salesRights->getRegionsIncluded())) {
                    if ($salesCountriesExcluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $salesRights->getCountriesExcluded()))));
                    }
                    if ($salesRegionsExcluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                    }
                }
            }

            unset($territoryNode);
            unset($salesRightsNode);
            unset($salesRights);
        }
        if ($salesRightsROW != null) {
            $publishingDetailNode->appendChild($this->buildTextNode($doc, 'ROWSalesRightsType', $salesRightsROW->getType()));
        }

        /* --- Related Material --- */

        unset($identificationCodes[$publicationFormat->getId()]);  // remove identifiers for the current publication format

        if (count($identificationCodes) > 0) {
            $relatedMaterialNode = $doc->createElementNS($deployment->getNamespace(), 'RelatedMaterial');

            $relatedProductNode = $doc->createElementNS($deployment->getNamespace(), 'RelatedProduct');
            $relatedProductNode->appendChild($this->buildTextNode($doc, 'ProductRelationCode', '06')); // alternative format

            foreach ($identificationCodes as $pubId => $idCodes) {
                foreach ($idCodes as $code => $value) {
                    $productIdentifierNode = $doc->createElementNS($deployment->getNamespace(), 'ProductIdentifier');
                    $productIdentifierNode->appendChild($this->buildTextNode($doc, 'ProductIDType', $code));
                    $productIdentifierNode->appendChild($this->buildTextNode($doc, 'IDValue', $value));
                    $relatedProductNode->appendChild($productIdentifierNode);
                    unset($productIdentifierNode);
                }
            }
            $relatedMaterialNode->appendChild($relatedProductNode);
            $productNode->appendChild($relatedMaterialNode);
        }

        /* --- Product Supply. We create one of these per defined Market. --- */

        /** @var RepresentativeDAO $representativeDao */
        $representativeDao = DAORegistry::getDAO('RepresentativeDAO');

        $markets = $publicationFormat->getMarkets();

        while ($market = $markets->next()) {
            $productSupplyNode = $doc->createElementNS($deployment->getNamespace(), 'ProductSupply');
            $productNode->appendChild($productSupplyNode);

            $marketNode = $doc->createElementNS($deployment->getNamespace(), 'Market');
            $productSupplyNode->appendChild($marketNode);

            $territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');

            $marketCountriesIncluded = sizeof($market->getCountriesIncluded()) > 0;
            $marketRegionsIncluded = sizeof($market->getRegionsIncluded()) > 0;
            $marketCountriesExcluded = sizeof($market->getCountriesExcluded()) > 0;
            $marketRegionsExcluded = sizeof($market->getRegionsExcluded()) > 0;

            if ($marketCountriesIncluded) {
                $territoryNode->appendChild($this->buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $market->getCountriesIncluded()))));
                if (!in_array('WORLD', $market->getRegionsIncluded())) {
                    if ($marketRegionsIncluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $market->getRegionsIncluded()))));
                    }
                    if ($marketRegionsExcluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
                    }
                } elseif ($marketRegionsExcluded) {
                    $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
                }
            } elseif ($marketRegionsIncluded) {
                $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $market->getRegionsIncluded()))));
                if (in_array('WORLD', $market->getRegionsIncluded())) {
                    if ($marketCountriesExcluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $market->getCountriesExcluded()))));
                    }
                    if ($marketRegionsExcluded) {
                        $territoryNode->appendChild($this->buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
                    }
                }
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

                $representativeNode->appendChild($this->buildTextNode($doc, 'AgentRole', $agent->getRole()));
                $representativeNode->appendChild($this->buildTextNode($doc, 'AgentName', $agent->getName()));

                if ($agent->getUrl() != '') {
                    $agentWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                    $representativeNode->appendChild($agentWebsiteNode);

                    $agentWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
                    $agentWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteLink', $agent->getUrl()));
                }
                unset($representativeNode);
            }

            $marketPubDetailNode->appendChild($this->buildTextNode($doc, 'MarketPublishingStatus', '04')); // Active

            // MarketDate is a required field on the form. If that changes, this should be wrapped in a conditional.
            $marketDateNode = $doc->createElementNS($deployment->getNamespace(), 'MarketDate');
            $marketPubDetailNode->appendChild($marketDateNode);

            $marketDateNode->appendChild($this->buildTextNode($doc, 'MarketDateRole', $market->getDateRole()));
            $marketDateNode->appendChild($this->buildTextNode($doc, 'DateFormat', $market->getDateFormat()));
            $marketDateNode->appendChild($this->buildTextNode($doc, 'Date', $market->getDate()));

            unset($marketDateNode);
            unset($marketPubDetailNode);

            /* --- Supplier Detail Information --- */

            $supplier = $representativeDao->getById($market->getSupplierId());

            $supplyDetailNode = $doc->createElementNS($deployment->getNamespace(), 'SupplyDetail');
            $productSupplyNode->appendChild($supplyDetailNode);

            $supplierNode = $doc->createElementNS($deployment->getNamespace(), 'Supplier');
            $supplyDetailNode->appendChild($supplierNode);
            if (isset($supplier)) {
                $supplierNode->appendChild($this->buildTextNode($doc, 'SupplierRole', $supplier->getRole()));
                $supplierNode->appendChild($this->buildTextNode($doc, 'SupplierName', $supplier->getName()));
                if ($supplier->getPhone()) {
                    $supplierNode->appendChild($this->buildTextNode($doc, 'TelephoneNumber', $supplier->getPhone()));
                }

                if ($supplier->getEmail()) {
                    $supplierNode->appendChild($this->buildTextNode($doc, 'EmailAddress', $supplier->getEmail()));
                }

                if ($supplier->getUrl() != '') {
                    $supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                    $supplierNode->appendChild($supplierWebsiteNode);

                    $supplierWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
                    $supplierWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteLink', $supplier->getUrl()));

                    unset($supplierWebsiteNode);
                }

                $supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                $supplierNode->appendChild($supplierWebsiteNode);

                $supplierWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteRole', '29')); // 29 -> Web page for full content
                $supplierWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteLink', $request->url($context->getPath(), 'catalog', 'book', [$submissionBestId])));
            } else { // No suppliers specified, use the Press settings instead.

                $supplierNode->appendChild($this->buildTextNode($doc, 'SupplierRole', '09')); // Publisher supplying to end customers
                $supplierNode->appendChild($this->buildTextNode($doc, 'SupplierName', $context->getData('publisher')));

                if ($context->getData('contactEmail') != '') {
                    $supplierNode->appendChild($this->buildTextNode($doc, 'EmailAddress', $context->getData('contactEmail')));
                }

                $supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                $supplierNode->appendChild($supplierWebsiteNode);

                $supplierWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
                $supplierWebsiteNode->appendChild($this->buildTextNode($doc, 'WebsiteLink', $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $context->getPath(), urlLocaleForPage: '')));
            }
            unset($supplierNode);
            unset($supplierWebsiteNode);

            if ($publicationFormat->getReturnableIndicatorCode() != '') {
                $returnsNode = $doc->createElementNS($deployment->getNamespace(), 'ReturnsConditions');
                $supplyDetailNode->appendChild($returnsNode);

                $returnsNode->appendChild($this->buildTextNode($doc, 'ReturnsCodeType', '02'));  // we support the BISAC codes for these
                $returnsNode->appendChild($this->buildTextNode($doc, 'ReturnsCode', $publicationFormat->getReturnableIndicatorCode()));

                unset($returnsNode);
            }

            $supplyDetailNode->appendChild($this->buildTextNode(
                $doc,
                'ProductAvailability',
                $publicationFormat->getProductAvailabilityCode() ? $publicationFormat->getProductAvailabilityCode() : '20'
            )); // assume 'available' if not specified.

            $priceNode = $doc->createElementNS($deployment->getNamespace(), 'Price');
            $supplyDetailNode->appendChild($priceNode);

            $excludeTaxNode = false;

            if ($market->getPriceTypeCode() != '') {
                $priceNode->appendChild($this->buildTextNode($doc, 'PriceType', $market->getPriceTypeCode()));
                $priceTypeTaxEx = ['02', '04', '07', '09', '12', '14', '17', '22', '24', '27', '34', '42']; // Price type codes that include tax
                if (in_array($market->getPriceTypeCode(), $priceTypeTaxEx)) {
                    $excludeTaxNode = true;
                }
            }

            if ($market->getDiscount() != '') {
                $discountNode = $doc->createElementNS($deployment->getNamespace(), 'Discount');
                $priceNode->appendChild($discountNode);
                $discountNode->appendChild($this->buildTextNode($doc, 'DiscountPercent', $market->getDiscount()));
                unset($discountNode);
            }

            $priceNode->appendChild($this->buildTextNode($doc, 'PriceAmount', $market->getPrice()));

            if (!$excludeTaxNode && ($market->getTaxTypeCode() != '' || $market->getTaxRateCode() != '')) {
                $taxNode = $doc->createElementNS($deployment->getNamespace(), 'Tax');
                $priceNode->appendChild($taxNode);

                if ($market->getTaxTypeCode()) {
                    $taxNode->appendChild($this->buildTextNode($doc, 'TaxType', $market->getTaxTypeCode()));
                }
                if ($market->getTaxRateCode()) {
                    $taxNode->appendChild($this->buildTextNode($doc, 'TaxRateCode', $market->getTaxRateCode()));
                    if ($market->getTaxRateCode() == 'Z') {
                        $taxNode->appendChild($this->buildTextNode($doc, 'TaxRatePercent', '0')); // Zero-rated tax rate type
                    }
                }
                $taxNode->appendChild($this->buildTextNode($doc, 'TaxableAmount', $market->getPrice())); // Taxable amount defaults to full price
                unset($taxNode);
            }

            if ($market->getCurrencyCode() != '') {
                $priceNode->appendChild($this->buildTextNode($doc, 'CurrencyCode', $market->getCurrencyCode())); // CAD, GBP, USD, etc
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
     *
     * @throws DOMException
     */
    public function createMeasurementNode(DOMDocument $doc, Onix30ExportDeployment $deployment, string $type, string $measurement, string $unitCode): DOMElement
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
     *
     * @throws DOMException
     */
    public function createExtentNode(DOMDocument $doc, Onix30ExportDeployment $deployment, string $type, string $extentValue, string $extentUnit): DOMElement
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
     *
     * @throws DOMException
     */
    public function buildTextNode(DOMDocument $doc, string $nodeName, string $textContent): DOMElement
    {
        $deployment = $this->getDeployment();
        $node = $doc->createElementNS($deployment->getNamespace(), $nodeName);
        $node->appendChild($doc->createTextNode($textContent));
        return $node;
    }
}
