<?php

/**
 * @file plugins/metadata/dc11/filter/Dc11SchemaPublicationFormatAdapter.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2000-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Dc11SchemaPublicationFormatAdapter
 *
 * @ingroup plugins_metadata_dc11_filter
 *
 * @see PublicationFormat
 * @see PKPDc11Schema
 *
 * @brief Adapter that injects/extracts Dublin Core schema compliant meta-data
 * into/from a PublicationFormat object.
 */

namespace APP\plugins\metadata\dc11\filter;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use APP\facades\Repo;
use APP\oai\omp\OAIDAO;
use APP\press\Press;
use APP\publicationFormat\PublicationFormat;
use APP\section\Section;
use APP\submission\Submission;
use PKP\controlledVocab\ControlledVocab;
use PKP\db\DAORegistry;
use PKP\facades\Locale;
use PKP\i18n\LocaleConversion;
use PKP\metadata\MetadataDataObjectAdapter;
use PKP\metadata\MetadataDescription;
use PKP\plugins\Hook;
use PKP\plugins\PluginRegistry;

class Dc11SchemaPublicationFormatAdapter extends MetadataDataObjectAdapter
{
    //
    // Implement template methods from MetadataDataObjectAdapter
    //
    /**
     * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
     *
     * @param MetadataDescription $dc11Description
     * @param PublicationFormat $publicationFormat
     */
    public function &injectMetadataIntoDataObject(&$dc11Description, &$publicationFormat)
    {
        // Not implemented
        assert(false);
    }

    /**
     * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
     *
     * @param PublicationFormat $publicationFormat
     *
     * @return MetadataDescription
     *
     * @hook Dc11SchemaPublicationFormatAdapter::extractMetadataFromDataObject [[&$this, $monograph, $press, &$dc11Description]]
     */
    public function extractMetadataFromDataObject(&$publicationFormat)
    {
        assert(is_a($publicationFormat, 'PublicationFormat'));

        // Retrieve data that belongs to the publication format.
        // FIXME: Retrieve this data from the respective entity DAOs rather than
        // from the OAIDAO once we've migrated all OAI providers to the
        // meta-data framework. We're using the OAIDAO here because it
        // contains cached entities and avoids extra database access if this
        // adapter is called from an OAI context.
        $oaiDao = DAORegistry::getDAO('OAIDAO'); /** @var OAIDAO $oaiDao */
        $publication = Repo::publication()->get($publicationFormat->getData('publicationId'));
        $monograph = Repo::submission()->get($publication->getData('submissionId'));
        $press = $oaiDao->getPress($monograph->getData('contextId'));
        $series = $oaiDao->getSeries($publication->getData('seriesId')); /** @var Section $series */
        $dc11Description = $this->instantiateMetadataDescription();

        // Title
        $publication = $monograph->getCurrentPublication();
        $this->_addLocalizedElements($dc11Description, 'dc:title', $publication->getFullTitles());

        // Creator
        foreach ($publication->getData('authors') as $author) {
            $this->_addLocalizedElements($dc11Description, 'dc:creator', $author->getFullNames(false, true));
        }

        // Subject
        $subjects = array_merge_recursive(
            Repo::controlledVocab()->getBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_KEYWORD,
                Application::ASSOC_TYPE_PUBLICATION,
                $publication->getId()
            ),
            Repo::controlledVocab()->getBySymbolic(
                ControlledVocab::CONTROLLED_VOCAB_SUBMISSION_SUBJECT,
                Application::ASSOC_TYPE_PUBLICATION,
                $publication->getId()
            )
        );
        $this->_addLocalizedElements($dc11Description, 'dc:subject', $subjects);

        // Description
        $this->_addLocalizedElements($dc11Description, 'dc:description', $publication->getData('abstract'));

        // Publisher
        $publisherInstitution = $press->getSetting('publisherInstitution');
        if (!empty($publisherInstitution)) {
            $publishers = [$press->getPrimaryLocale() => $publisherInstitution];
        } else {
            $publishers = $press->getName(null); // Default
        }
        $this->_addLocalizedElements($dc11Description, 'dc:publisher', $publishers);

        // Contributor
        $contributors = $monograph->getData('sponsor');
        if (is_array($contributors)) {
            foreach ($contributors as $locale => $contributor) {
                $contributors[$locale] = array_map(trim(...), explode(';', $contributor));
            }
            $this->_addLocalizedElements($dc11Description, 'dc:contributor', $contributors);
        }

        // Date
        // FIXME: should we use the publication dates of the publication format? If yes,
        // in which role preference order?
        if ($monograph instanceof Submission) {
            if ($datePublished = $publication->getData('datePublished')) {
                $dc11Description->addStatement('dc:date', date('Y-m-d', strtotime($datePublished)));
            }
        }

        // Type
        $types = array_merge_recursive(
            [Locale::getLocale() => __('rt.metadata.pkp.dctype')],
            (array) $publication->getData('type')
        );
        $this->_addLocalizedElements($dc11Description, 'dc:type', $types);

        // Format
        $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO'); /** @var ONIXCodelistItemDAO $onixCodelistItemDao */
        $entryKeys = $onixCodelistItemDao->getCodes('150'); // List150 is for object formats
        if ($publicationFormat->getEntryKey()) {
            $formatName = $entryKeys[$publicationFormat->getEntryKey()];
            $dc11Description->addStatement('dc:format', $formatName);
        }

        // Identifier: URL
        $request = Application::get()->getRequest();
        if ($monograph instanceof Submission) {
            $dc11Description->addStatement('dc:identifier', $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $press->getPath(), 'catalog', 'book', [$publication->getData('urlPath') ?? $monograph->getId()], urlLocaleForPage: ''));
        }

        // Public identifiers (e.g. DOI, URN)
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

        $context = $request->getContext();
        if (!$context) {
            $contextDao = Application::getContextDAO();
            /** @var Press */
            $context = $contextDao->getById($monograph->getData('contextId'));
        }
        if ($context->areDoisEnabled()) {
            $doi = $publicationFormat->getDoi();
            if ($doi) {
                $dc11Description->addStatement('dc:identifier', $doi);
            }
            $publicationDoi = $publication->getData('doiObject');
            if ($publicationDoi) {
                $dc11Description->addStatement('dc:relation', $publicationDoi->getData('doi'));
            }
        }

        // Identifier: others
        $identificationCodeFactory = $publicationFormat->getIdentificationCodes();
        while ($identificationCode = $identificationCodeFactory->next()) {
            $dc11Description->addStatement('dc:identifier', $identificationCode->getValue());
        }

        // Source (press title and pages)
        $sources = $press->getName(null);
        $pages = $publication->getData('pages');
        if (!empty($pages)) {
            $pages = '; ' . $pages;
        }
        foreach ($sources as $locale => $source) {
            $sources[$locale] .= '; ';
            $sources[$locale] .= $pages;
        }
        $this->_addLocalizedElements($dc11Description, 'dc:source', $sources);

        // Language
        $submissionLanguage = $monograph->getData('locale');
        if (!empty($submissionLanguage)) {
            $dc11Description->addStatement('dc:language', LocaleConversion::getIso3FromLocale($submissionLanguage));
        }

        $pubFormatFiles = Repo::submissionFile()
            ->getCollector()
            ->filterBySubmissionIds([$monograph->getId()])
            ->filterByAssoc(Application::ASSOC_TYPE_PUBLICATION_FORMAT)
            ->getMany();

        // Relation (Add publication file format to monograph / edited volume)
        foreach ($pubFormatFiles as $file) {
            {
                if ($file->getData('assocId') == $publicationFormat->getData('id')) {
                    $relation = $request->getDispatcher()->url($request, Application::ROUTE_PAGE, $press->getData('urlPath'), 'catalog', 'view', [$publication->getData('urlPath') ?? $monograph->getId(), $publicationFormat->getId(), $file->getId()], urlLocaleForPage: '');
                    $dc11Description->addStatement('dc:relation', $relation);
                }
            }
        }

        // Coverage
        $coverage = (array) $publication->getData('coverage');
        $this->_addLocalizedElements($dc11Description, 'dc:coverage', $coverage);

        // Rights
        $salesRightsFactory = $publicationFormat->getSalesRights();
        while ($salesRight = $salesRightsFactory->next()) {
            $dc11Description->addStatement('dc:rights', $salesRight->getNameForONIXCode());
        }

        Hook::call('Dc11SchemaPublicationFormatAdapter::extractMetadataFromDataObject', [&$this, $monograph, $press, &$dc11Description]);

        return $dc11Description;
    }

    /**
     * @see MetadataDataObjectAdapter::getDataObjectMetadataFieldNames()
     *
     * @param bool $translated
     */
    public function getDataObjectMetadataFieldNames($translated = true)
    {
        // All DC fields are mapped.
        return [];
    }


    //
    // Private helper methods
    //
    /**
     * Add an array of localized values to the given description.
     *
     * @param MetadataDescription $description
     * @param string $propertyName
     * @param array $localizedValues
     */
    public function _addLocalizedElements(&$description, $propertyName, $localizedValues)
    {
        foreach (stripAssocArray((array) $localizedValues) as $locale => $values) {
            if (is_scalar($values)) {
                $values = [$values];
            }
            foreach ($values as $value) {
                $description->addStatement($propertyName, $value, $locale);
                unset($value);
            }
        }
    }
}
