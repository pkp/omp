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
use APP\publication\Publication;
use APP\publicationFormat\PublicationFormat;
use APP\submission\Submission;
use DOMDocument;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\filter\FilterGroup;
use PKP\submission\SubmissionLanguageDAO;

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
        $senderNode->appendChild($this->_buildTextNode($doc, 'SenderName', $context->getName($context->getPrimaryLocale())));
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
        $productNode->appendChild($this->_buildTextNode($doc, 'RecordReference', $request->url($context->getPath(), 'monograph', 'view', [$submission->getId()])));
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
                $epubLicenseNode->appendChild($this->_buildTextNode($doc, 'EpubLicenseName', $licenseName));

                $epubLicenseExpressionNode = $doc->createElementNS($deployment->getNamespace(), 'EpubLicenseExpression');
                $epubLicenseNode->appendChild($epubLicenseExpressionNode);

                $epubLicenseExpressionNode->appendChild($this->_buildTextNode($doc, 'EpubLicenseExpressionType', '02'));
                $epubLicenseExpressionNode->appendChild($this->_buildTextNode($doc, 'EpubLicenseExpressionLink', $licenseUrl));
            }
        }

        /* --- Collection information, first for series and then for product --- */

        /* --- Series information, if this monograph is part of one. --- */
        $seriesId = $submission->getCurrentPublication()->getData('seriesId');
        $series = $seriesId ? Repo::section()->get($seriesId) : null;
        if ($series != null) {
            $seriesCollectionNode = $doc->createElementNS($deployment->getNamespace(), 'Collection');
            $seriesCollectionNode->appendChild($this->_buildTextNode($doc, 'CollectionType', '10')); // publisher series.

            $seriesTitleDetailNode = $doc->createElementNS($deployment->getNamespace(), 'TitleDetail');
            $seriesTitleDetailNode->appendChild($this->_buildTextNode($doc, 'TitleType', '01'));
            $seriesCollectionNode->appendChild($seriesTitleDetailNode);

            $titleElementNode = $doc->createElementNS($deployment->getNamespace(), 'TitleElement');
            $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleElementLevel', '02')); // Collection level title
            $seriesTitleDetailNode->appendChild($titleElementNode);

            if ($submission->getCurrentPublication()->getData('seriesPosition')) {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'PartNumber', $submission->getCurrentPublication()->getData('seriesPosition')));
            }

            $seriesLocale = $pubLocale;
            // If the series title doesn't exist in the submission locale, use the press locale
            if ($series->getTitle($seriesLocale, false) == '') {
                $seriesLocale = $context->getPrimaryLocale();
            }

            if ($series->getPrefix($seriesLocale) == '' || $series->getTitle($seriesLocale, false) == '') {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleText', trim(join(' ', [$series->getPrefix($seriesLocale), $series->getTitle($seriesLocale, false)]))));
            } else {
                if ($series->getPrefix($seriesLocale) != '') {
                    $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitlePrefix', $series->getPrefix($seriesLocale)));
                } else {
                    $titleElementNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'NoPrefix'));
                }

                $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleWithoutPrefix', $series->getTitle($seriesLocale, false)));
            }

            if ($series->getSubtitle($seriesLocale) != '') {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'Subtitle', $series->getSubtitle($seriesLocale)));
            }
        } else {
            $seriesCollectionNode = $doc->createElementNS($deployment->getNamespace(), 'NoCollection');
        }
        $descDetailNode->appendChild($seriesCollectionNode);

        /* --- and now product level info --- */

        $productTitleDetailNode = $doc->createElementNS($deployment->getNamespace(), 'TitleDetail');
        $productTitleDetailNode->appendChild($this->_buildTextNode($doc, 'TitleType', '01'));
        $descDetailNode->appendChild($productTitleDetailNode);

        $titleElementNode = $doc->createElementNS($deployment->getNamespace(), 'TitleElement');
        $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleElementLevel', '01'));

        $productTitleDetailNode->appendChild($titleElementNode);

        if (!$publication->getData('prefix', $pubLocale) || !$publication->getData('title', $pubLocale)) {
            $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleText', trim($publication->getData('prefix', $pubLocale) ?? $publication->getData('title', $pubLocale))));
        } else {
            if ($publication->getData('prefix', $pubLocale)) {
                $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitlePrefix', $publication->getData('prefix', $pubLocale)));
            } else {
                $titleElementNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'NoPrefix'));
            }

            $titleElementNode->appendChild($this->_buildTextNode($doc, 'TitleWithoutPrefix', strip_tags($publication->getData('title', $pubLocale))));
        }

        if ($subTitle = $publication->getData('subtitle', $pubLocale)) {
            $titleElementNode->appendChild($this->_buildTextNode($doc, 'Subtitle', $subTitle));
        }

        /* --- Contributor information --- */

        $authors = $publication->getData('authors'); // sorts by sequence.
        $sequence = 1;
        foreach ($authors as $author) {
            $contributorNode = $doc->createElementNS($deployment->getNamespace(), 'Contributor');
            $contributorNode->appendChild($this->_buildTextNode($doc, 'SequenceNumber', $sequence));

            $userGroup = Repo::userGroup()->get($author->getUserGroupId());

            $userGroupOnixMap = ['default.groups.name.author' => 'A01', 'default.groups.name.volumeEditor' => 'B01', 'default.groups.name.chapterAuthor' => 'A01', 'default.groups.name.translator' => 'B06', 'default.groups.name.editor' => 'B21']; // From List17, ContributorRole types.

            $nameKey = $userGroup->getData('nameLocaleKey');
            $role = array_key_exists($nameKey, $userGroupOnixMap) ? $userGroupOnixMap[$nameKey] : 'Z99'; // Z99 - unknown contributor type.

            $contributorNode->appendChild($this->_buildTextNode($doc, 'ContributorRole', $role));
            $contributorNode->appendChild($this->_buildTextNode($doc, 'PersonName', $author->getFullName(false, false, $pubLocale)));
            $contributorNode->appendChild($this->_buildTextNode($doc, 'PersonNameInverted', $author->getFullName(false, true, $pubLocale)));
            $contributorNode->appendChild($this->_buildTextNode($doc, 'NamesBeforeKey', $author->getGivenName($pubLocale)));
            if ($author->getFamilyName($pubLocale) != '') {
                $contributorNode->appendChild($this->_buildTextNode($doc, 'KeyNames', $author->getFamilyName($pubLocale)));
            } else {
                $contributorNode->appendChild($this->_buildTextNode($doc, 'KeyNames', $author->getFullName(false, false, $pubLocale)));
            }

            if ($author->getBiography($pubLocale) != '') {
                $contributorNode->appendChild($this->_buildTextNode($doc, 'BiographicalNote', $author->getBiography($pubLocale)));
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
            $descDetailNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'NoContributor')); // empty state of fact.
        }

        /* --- Add Language elements --- */

        $submissionLanguageDao = DAORegistry::getDAO('SubmissionLanguageDAO'); /** @var SubmissionLanguageDAO $submissionLanguageDao */
        $allLanguages = $submissionLanguageDao->getLanguages($publication->getId(), array_keys(Locale::getSupportedFormLocales()));
        $uniqueLanguages = [];
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

        if ($publication->getData('subjects')) {
            $subjectNode = $doc->createElementNS($deployment->getNamespace(), 'Subject');
            $mainSubjectNode = $doc->createElementNS($deployment->getNamespace(), 'MainSubject'); // Always empty as per 3.0 spec.

            $subjectNode->appendChild($mainSubjectNode);
            $subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectSchemeIdentifier', '12')); // 12 is BIC subject category code list.
            $subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectSchemeVersion', '2')); // Version 2 of ^^

            $allSubjects = ($publication->getData('subjects')[$pubLocale]);
            $subjectNode->appendChild($this->_buildTextNode($doc, 'SubjectCode', trim(join(', ', $allSubjects))));
            $descDetailNode->appendChild($subjectNode);
        }

        if ($publication->getData('keywords')) {
            $allKeywords = ($publication->getData('keywords')[$pubLocale]);
            $keywordNode = $doc->createElementNS($deployment->getNamespace(), 'Subject');
            $keywordNode->appendChild($this->_buildTextNode($doc, 'SubjectSchemeIdentifier', '20')); // Keywords
            $keywordNode->appendChild($this->_buildTextNode($doc, 'SubjectHeadingText', trim(join(', ', $allKeywords))));
            $descDetailNode->appendChild($keywordNode);
        }
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

        $abstract = strip_tags($publication->getData('abstract', $pubLocale));

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
        $resourceVersionNode->appendChild($this->_buildTextNode($doc, 'ResourceLink', $publication->getCoverImageUrl($context->getId(), $pubLocale)));

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
        $websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $request->url($context->getPath())));

        $websiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
        $publisherNode->appendChild($websiteNode);

        $websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '29')); // 29 -> Web page for full content

        $submissionBestId = $publication->getData('submissionId');

        if ($publication->getData('urlPath') != '') {
            $submissionBestId = $publication->getData('urlPath');
        }

        $websiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $request->url($context->getPath(), 'catalog', 'book', $submissionBestId)));

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
            $salesRightsNode = $doc->createElementNS($deployment->getNamespace(), 'SalesRights');
            $publishingDetailNode->appendChild($salesRightsNode);
            $salesRightsNode->appendChild($this->_buildTextNode($doc, 'SalesRightsType', $salesRights->getType()));

            // now do territories and countries.
            $territoryNode = $doc->createElementNS($deployment->getNamespace(), 'Territory');
            $salesRightsNode->appendChild($territoryNode);

            $salesCountriesIncluded = sizeof($salesRights->getCountriesIncluded()) > 0;
            $salesRegionsIncluded = sizeof($salesRights->getRegionsIncluded()) > 0;
            $salesCountriesExcluded = sizeof($salesRights->getCountriesExcluded()) > 0;
            $salesRegionsExcluded = sizeof($salesRights->getRegionsExcluded()) > 0;

            if ($salesRights->getROWSetting()) {
                $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', 'WORLD'));
                $salesRightsROW = $salesRights;
            } elseif ($salesCountriesIncluded) {
                $territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $salesRights->getCountriesIncluded()))));
                if (!in_array('WORLD', $salesRights->getRegionsIncluded())) {
                    if ($salesRegionsIncluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $salesRights->getRegionsIncluded()))));
                    }
                    if ($salesRegionsExcluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                    }
                } elseif ($salesRegionsExcluded) {
                    $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                }
            } elseif ($salesRegionsIncluded) {
                $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $salesRights->getRegionsIncluded()))));
                if (in_array('WORLD', $salesRights->getRegionsIncluded())) {
                    if ($salesCountriesExcluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $salesRights->getCountriesExcluded()))));
                    }
                    if ($salesRegionsExcluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $salesRights->getRegionsExcluded()))));
                    }
                }
            }

            unset($territoryNode);
            unset($salesRightsNode);
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

            $marketCountriesIncluded = sizeof($market->getCountriesIncluded()) > 0;
            $marketRegionsIncluded = sizeof($market->getRegionsIncluded()) > 0;
            $marketCountriesExcluded = sizeof($market->getCountriesExcluded()) > 0;
            $marketRegionsExcluded = sizeof($market->getRegionsExcluded()) > 0;

            if ($marketCountriesIncluded) {
                $territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesIncluded', trim(join(' ', $market->getCountriesIncluded()))));
                if (!in_array('WORLD', $market->getRegionsIncluded())) {
                    if ($marketRegionsIncluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $market->getRegionsIncluded()))));
                    }
                    if ($marketRegionsExcluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
                    }
                } elseif ($marketRegionsExcluded) {
                    $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
                }
            } elseif ($marketRegionsIncluded) {
                $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsIncluded', trim(join(' ', $market->getRegionsIncluded()))));
                if (in_array('WORLD', $market->getRegionsIncluded())) {
                    if ($marketCountriesExcluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'CountriesExcluded', trim(join(' ', $market->getCountriesExcluded()))));
                    }
                    if ($marketRegionsExcluded) {
                        $territoryNode->appendChild($this->_buildTextNode($doc, 'RegionsExcluded', trim(join(' ', $market->getRegionsExcluded()))));
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

            $supplierNode = $doc->createElementNS($deployment->getNamespace(), 'Supplier');
            $supplyDetailNode->appendChild($supplierNode);
            if (isset($supplier)) {
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

                $supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                $supplierNode->appendChild($supplierWebsiteNode);

                $supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '29')); // 29 -> Web page for full content
                $supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $request->url($context->getPath(), 'catalog', 'book', $submissionBestId)));
            } else { // No suppliers specified, use the Press settings instead.

                $supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierRole', '09')); // Publisher supplying to end customers
                $supplierNode->appendChild($this->_buildTextNode($doc, 'SupplierName', $context->getData('publisher')));

                if ($context->getData('contactEmail') != '') {
                    $supplierNode->appendChild($this->_buildTextNode($doc, 'EmailAddress', $context->getData('contactEmail')));
                }

                $supplierWebsiteNode = $doc->createElementNS($deployment->getNamespace(), 'Website');
                $supplierNode->appendChild($supplierWebsiteNode);

                $supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteRole', '18')); // 18 -> Public website
                $supplierWebsiteNode->appendChild($this->_buildTextNode($doc, 'WebsiteLink', $request->url($context->getPath())));
            }
            unset($supplierNode);
            unset($supplierWebsiteNode);

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

            $excludeTaxNode = false;

            if ($market->getPriceTypeCode() != '') {
                $priceNode->appendChild($this->_buildTextNode($doc, 'PriceType', $market->getPriceTypeCode()));
                $priceTypeTaxEx = ['02', '04', '07', '09', '12', '14', '17', '22', '24', '27', '34', '42']; // Price type codes that include tax
                if (in_array($market->getPriceTypeCode(), $priceTypeTaxEx)) {
                    $excludeTaxNode = true;
                }
            }

            if ($market->getDiscount() != '') {
                $discountNode = $doc->createElementNS($deployment->getNamespace(), 'Discount');
                $priceNode->appendChild($discountNode);
                $discountNode->appendChild($this->_buildTextNode($doc, 'DiscountPercent', $market->getDiscount()));
                unset($discountNode);
            }

            $priceNode->appendChild($this->_buildTextNode($doc, 'PriceAmount', $market->getPrice()));

            if (!$excludeTaxNode && ($market->getTaxTypeCode() != '' || $market->getTaxRateCode() != '')) {
                $taxNode = $doc->createElementNS($deployment->getNamespace(), 'Tax');
                $priceNode->appendChild($taxNode);

                if ($market->getTaxTypeCode()) {
                    $taxNode->appendChild($this->_buildTextNode($doc, 'TaxType', $market->getTaxTypeCode()));
                }
                if ($market->getTaxRateCode()) {
                    $taxNode->appendChild($this->_buildTextNode($doc, 'TaxRateCode', $market->getTaxRateCode()));
                    if ($market->getTaxRateCode() == 'Z') {
                        $taxNode->appendChild($this->_buildTextNode($doc, 'TaxRatePercent', '0')); // Zero-rated tax rate type
                    }
                }
                $taxNode->appendChild($this->_buildTextNode($doc, 'TaxableAmount', $market->getPrice())); // Taxable amount defaults to full price
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
