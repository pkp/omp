<?php

/**
 * @file plugins/importexport/native/filter/PublicationNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Article to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.PKPPublicationNativeXmlFilter');

class PublicationNativeXmlFilter extends PKPPublicationNativeXmlFilter
{
    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return 'plugins.importexport.native.filter.PublicationNativeXmlFilter';
    }

    /**
     * Get the representation export filter group name
     *
     * @return string
     */
    public function getRepresentationExportFilterGroupName()
    {
        return 'publication-format=>native-xml';
    }

    //
    // Submission conversion functions
    //
    /**
     * Create and return a submission node.
     *
     * @param DOMDocument $doc
     * @param Publication $entity
     *
     * @return DOMElement
     */
    public function createEntityNode($doc, $entity)
    {
        $deployment = $this->getDeployment();
        $entityNode = parent::createEntityNode($doc, $entity);

        $deployment->setPublication($entity);

        // Add the series, if one is designated.
        $seriesNode = $this->createSeriesNode($this, $doc, $entity);
        if ($seriesNode) {
            $entityNode->appendChild($seriesNode);

            $entityNode->setAttribute('series_position', $entity->getData('seriesPosition'));
        }


        $chapters = $entity->getData('chapters');
        if ($chapters && count($chapters) > 0) {
            $this->addChapters($doc, $entityNode, $entity);
        }

        // cover images
        import('lib.pkp.plugins.importexport.native.filter.PKPNativeFilterHelper');
        $nativeFilterHelper = new PKPNativeFilterHelper();
        $coversNode = $nativeFilterHelper->createPublicationCoversNode($this, $doc, $entity);
        if ($coversNode) {
            $entityNode->appendChild($coversNode);
        }

        return $entityNode;
    }

    /**
     * Add the chapter metadata for a publication to its DOM element.
     *
     * @param DOMDocument $doc
     * @param DOMElement $entityNode
     * @param Publication $entity
     */
    public function addChapters($doc, $entityNode, $entity)
    {
        $currentFilter = PKPImportExportFilter::getFilter('chapter=>native-xml', $this->getDeployment());

        $chapters = $entity->getData('chapters');
        if ($chapters && count($chapters) > 0) {
            $chaptersDoc = $currentFilter->execute($chapters);
            if ($chaptersDoc && $chaptersDoc->documentElement instanceof DOMElement) {
                $clone = $doc->importNode($chaptersDoc->documentElement, true);
                $entityNode->appendChild($clone);
            } else {
                $deployment = $this->getDeployment();
                $deployment->addError(ASSOC_TYPE_PUBLICATION, $entity->getId(), __('plugins.importexport.chapter.exportFailed'));

                throw new Exception(__('plugins.importexport.chapter.exportFailed'));
            }
        }
    }

    /**
     * Create and return an object covers node.
     *
     * @param NativeExportFilter $filter
     * @param DOMDocument $doc
     * @param Publication $object
     *
     * @return DOMElement
     */
    public function createSeriesNode($filter, $doc, $object)
    {
        $deployment = $filter->getDeployment();

        $context = $deployment->getContext();

        $seriesNode = null;
        if ($seriesId = $object->getData('seriesId')) {
            $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
            $series = $seriesDao->getById($seriesId, $context->getId());

            if ($series) {
                $seriesNode = $doc->createElementNS($deployment->getNamespace(), 'series');

                // Add metadata
                $this->createLocalizedNodes($doc, $seriesNode, 'title', $series->getData('title'));
                $this->createLocalizedNodes($doc, $seriesNode, 'subtitle', $series->getData('subtitle'));
                $this->createLocalizedNodes($doc, $seriesNode, 'description', $series->getData('description'));

                $seriesNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'printIssn', $series->getData('printIssn')));
                $seriesNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'onlineIssn', $series->getData('onlineIssn')));

                $seriesNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'path', $series->getData('path')));
                $seriesNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'sequence', $series->getData('sequence')));
            }
        }

        return $seriesNode;
    }
}
