<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFilter.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlMonographFilter
 * @brief Class that converts a Native XML document to a set of monographs.
 */

namespace APP\plugins\importexport\native\filter;

use APP\core\Application;
use PKP\plugins\importexport\PKPImportExportFilter;

class NativeXmlMonographFilter extends \PKP\plugins\importexport\native\filter\NativeXmlSubmissionFilter
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
        $workType = $node->getAttribute('work_type');
        $submission->setData('workType', $workType);

        return parent::populateObject($submission, $node);
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
                $deployment->addError(Application::ASSOC_TYPE_SUBMISSION, $submission->getId(), __('plugins.importexport.common.error.unknownElement', ['param' => $elementName]));
        }
        // Caps on class name for consistency with imports, whose filter
        // group names are generated implicitly.
        $currentFilter = PKPImportExportFilter::getFilter('native-xml=>' . $importClass, $this->getDeployment());
        return $currentFilter;
    }
}
