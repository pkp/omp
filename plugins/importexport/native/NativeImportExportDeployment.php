<?php

/**
 * @file plugins/importexport/native/NativeImportExportDeployment.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2000-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeImportExportDeployment
 * @brief Class configuring the native import/export process to this
 * application's specifics.
 */

namespace APP\plugins\importexport\native;

class NativeImportExportDeployment extends \PKP\plugins\importexport\native\PKPNativeImportExportDeployment
{
    //
    // Deployment items for subclasses to override
    //
    /**
     * Get the submission node name
     *
     * @return string
     */
    public function getSubmissionNodeName()
    {
        return 'monograph';
    }

    /**
     * Get the submissions node name
     *
     * @return string
     */
    public function getSubmissionsNodeName()
    {
        return 'monographs';
    }

    /**
     * Get the representation node name
     */
    public function getRepresentationNodeName()
    {
        return 'publication_format';
    }

    /**
     * Get the schema filename.
     *
     * @return string
     */
    public function getSchemaFilename()
    {
        return 'native.xsd';
    }
}
