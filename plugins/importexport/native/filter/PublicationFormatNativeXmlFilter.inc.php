<?php

/**
 * @file plugins/importexport/native/filter/PublicationFormatNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a PublicationFormat to a Native XML document.
 */

use APP\facades\Repo;
use PKP\xsl\XSLTransformer;

import('lib.pkp.plugins.importexport.native.filter.RepresentationNativeXmlFilter');

class PublicationFormatNativeXmlFilter extends RepresentationNativeXmlFilter
{
    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return 'plugins.importexport.native.filter.PublicationFormatNativeXmlFilter';
    }

    //
    // Extend functions in RepresentationNativeXmlFilter
    //
    /**
     * Create and return a representation node. Extend the parent class
     * with publication format specific data.
     *
     * @param $doc DOMDocument
     * @param $representation PublicationFormat
     *
     * @return DOMElement
     */
    public function createRepresentationNode($doc, $representation)
    {
        $representationNode = parent::createRepresentationNode($doc, $representation);
        $representationNode->setAttribute('approved', $representation->getIsApproved() ? 'true' : 'false');
        $representationNode->setAttribute('available', $representation->getIsAvailable() ? 'true' : 'false');
        $representationNode->setAttribute('physical_format', $representation->getPhysicalFormat() ? 'true' : 'false');
        $representationNode->setAttribute('url_path', $representation->getData('urlPath'));
        $representationNode->setAttribute('entry_key', $representation->getData('entryKey'));

        // If all nexessary press settings exist, export ONIX metadata
        $context = $this->getDeployment()->getContext();
        if ($context->getContactName() && $context->getContactEmail() && $context->getData('publisher') && $context->getData('location') && $context->getData('codeType') && $context->getData('codeValue')) {
            $publication = $this->getDeployment()->getPublication();
            $submission = $this->getDeployment()->getSubmission();

            $filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
            $nativeExportFilters = $filterDao->getObjectsByGroup('monograph=>onix30-xml');
            assert(count($nativeExportFilters) == 1); // Assert only a single serialization filter
            $exportFilter = array_shift($nativeExportFilters);

            $request = Application::get()->getRequest();
            $exportFilter->setDeployment(new Onix30ExportDeployment($request->getContext(), $request->getUser()));

            $onixDoc = $exportFilter->execute($submission);
            if ($onixDoc) { // we do this to ensure validation.
                // assemble just the Product node we want.
                $publicationFormatDOMElement = $exportFilter->createProductNode($doc, $submission, $representation);
                if ($publicationFormatDOMElement && $publicationFormatDOMElement instanceof DOMElement) {
                    $xslTransformer = new XSLTransformer();
                    $xslFile = 'plugins/importexport/native/onixProduct2NativeXml.xsl';
                    $productXml = $publicationFormatDOMElement->ownerDocument->saveXML($publicationFormatDOMElement);
                    $filteredXml = $xslTransformer->transform($productXml, XSL_TRANSFORMER_DOCTYPE_STRING, $xslFile, XSL_TRANSFORMER_DOCTYPE_FILE, XSL_TRANSFORMER_DOCTYPE_STRING);
                    $representationFragment = $doc->createDocumentFragment();
                    $representationFragment->appendXML($filteredXml);
                    $representationNode->appendChild($representationFragment);
                } else {
                    $deployment = $this->getDeployment();
                    $deployment->addError(ASSOC_TYPE_PUBLICATION, $representation->getId(), __('plugins.importexport.publicationformat.exportFailed'));

                    throw new Exception(__('plugins.importexport.publicationformat.exportFailed'));
                }
            }
        }
        return $representationNode;
    }

    /**
     * Get the available submission files for a representation
     *
     * @param $representation Representation
     *
     * @return Iterator
     */
    public function getFiles($representation)
    {
        $deployment = $this->getDeployment();
        $submission = $deployment->getSubmission();
        $collector = Repo::submissionFiles()
            ->getCollector()
            ->filterBySubmissionIds([$submission->getId()])
            ->filterByAssoc(
                ASSOC_TYPE_PUBLICATION_FORMAT,
                [$representation->getId()]
            );

        return Repo::submissionFiles()->getMany($collector);
    }
}
