<?php

/**
 * @file plugins/importexport/native/filter/MonographNativeXmlFilter.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MonographNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Monograph to a Native XML document.
 */

namespace APP\plugins\importexport\native\filter;

class MonographNativeXmlFilter extends \PKP\plugins\importexport\native\filter\SubmissionNativeXmlFilter
{
    //
    // Implement template methods from PersistableFilter
    //
    /**
     * @copydoc PersistableFilter::getClassName()
     */
    public function getClassName()
    {
        return 'plugins.importexport.native.filter.MonographNativeXmlFilter';
    }


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
     * @param Submission $submission
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

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\importexport\native\filter\MonographNativeXmlFilter', '\MonographNativeXmlFilter');
}
