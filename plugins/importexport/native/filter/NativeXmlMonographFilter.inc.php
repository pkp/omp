<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFilter.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlMonographFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a set of monographs.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlSubmissionFilter');

class NativeXmlMonographFilter extends NativeXmlSubmissionFilter
{
    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return 'plugins.importexport.native.filter.NativeXmlMonographFilter';
    }

    /**
     * Populate the submission object from the node
     *
     * @param Submission $submission
     * @param DOMElement $node
     *
     * @return Submission
     */
    public function populateObject($submission, $node)
    {
        $deployment = $this->getDeployment();

        $workType = $node->getAttribute('work_type');
        $submission->setData('workType', $workType);

        return parent::populateObject($submission, $node);
    }

    /**
     * Handle an element whose parent is the submission element.
     *
     * @param DOMElement $n
     * @param Submission $submission
     */
    public function handleChildElement($n, $submission)
    {
        switch ($n->tagName) {
            case 'publication':
                $this->parsePublication($n, $submission);
                break;
            default:
                parent::handleChildElement($n, $submission);
        }
    }

    /**
     * Get the import filter for a given element.
     *
     * @param string $elementName Name of XML element
     *
     * @return Filter
     */
    public function getImportFilter($elementName)
    {
        $deployment = $this->getDeployment();
        $submission = $deployment->getSubmission();
        $importClass = null; // Scrutinizer
        switch ($elementName) {
            case 'submission_file':
                $importClass = 'SubmissionFile';
                break;
            case 'publication':
                $importClass = 'Publication';
                break;
            default:
                $deployment->addError(ASSOC_TYPE_SUBMISSION, $submission->getId(), __('plugins.importexport.common.error.unknownElement', ['param' => $elementName]));
        }
        // Caps on class name for consistency with imports, whose filter
        // group names are generated implicitly.
        $currentFilter = PKPImportExportFilter::getFilter('native-xml=>' . $importClass, $this->getDeployment());
        return $currentFilter;
    }

    /**
     * Parse a publication format and add it to the submission.
     *
     * @param DOMElement $n
     * @param Submission $submission
     */
    public function parsePublication($n, $submission)
    {
        $importFilter = $this->getImportFilter($n->tagName);

        $formatDoc = new DOMDocument();
        $formatDoc->appendChild($formatDoc->importNode($n, true));
        return $importFilter->execute($formatDoc);
    }
}
