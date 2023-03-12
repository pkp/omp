<?php

/**
 * @file plugins/importexport/native/filter/ArtworkFileNativeXmlFilter.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileNativeXmlFilter
 * @brief Filter to convert an artwork file to a Native XML document
 */

namespace APP\plugins\importexport\native\filter;

use DOMDocument;
use DOMElement;
use PKP\submissionFile\SubmissionFile;

class MonographFileNativeXmlFilter extends \PKP\plugins\importexport\native\filter\SubmissionFileNativeXmlFilter
{
    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return (string) self::class;
    }


    //
    // Implement/override functions from SubmissionFileNativeXmlFilter
    //
    /**
     * Create and return a submissionFile node.
     */
    public function createSubmissionFileNode(DOMDocument $doc, SubmissionFile $submissionFile): ?DOMElement
    {
        $deployment = $this->getDeployment();
        $submissionFileNode = parent::createSubmissionFileNode($doc, $submissionFile);

        if ($submissionFile->getData('directSalesPrice')) {
            $submissionFileNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'directSalesPrice', $submissionFile->getData('directSalesPrice')));
        }

        if ($submissionFile->getData('salesType')) {
            $submissionFileNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'salesType', $submissionFile->getData('salesType')));
        }

        // FIXME: is permission file ID implemented?
        // FIXME: is chapter ID implemented?
        // FIXME: is contact author ID implemented?

        return $submissionFileNode;
    }

    /**
     * Get the submission file element name
     */
    public function getSubmissionFileElementName()
    {
        return 'submission_file';
    }
}
