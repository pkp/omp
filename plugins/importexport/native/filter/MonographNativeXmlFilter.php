<?php

/**
 * @file plugins/importexport/native/filter/MonographNativeXmlFilter.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographNativeXmlFilter
 * @brief Class that converts a Monograph to a Native XML document.
 */

namespace APP\plugins\importexport\native\filter;

class MonographNativeXmlFilter extends \PKP\plugins\importexport\native\filter\SubmissionNativeXmlFilter
{
    //
    // Implement abstract methods from SubmissionNativeXmlFilter
    //
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
     * @param \DOMDocument $doc
     * @param \APP\submission\Submission $submission
     *
     * @return \DOMElement
     */
    public function createSubmissionNode($doc, $submission)
    {
        $submissionNode = parent::createSubmissionNode($doc, $submission);
        $submissionNode->setAttribute('work_type', $submission->getData('workType'));
        return $submissionNode;
    }
}
