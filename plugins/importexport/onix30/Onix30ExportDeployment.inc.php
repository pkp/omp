<?php

/**
 * @file plugins/importexport/onix30/Onix30ExportDeployment.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportDeployment
 * @ingroup plugins_importexport_onix30
 *
 * @brief Class configuring the native import/export process to this
 * application's specifics.
 */

import('lib.pkp.classes.plugins.importexport.PKPImportExportDeployment');

class Onix30ExportDeployment extends PKPImportExportDeployment
{
    //
    // Deploymenturation items for subclasses to override
    //
    /**
     * Get the submission node name
     *
     * @return string
     */
    public function getSubmissionNodeName()
    {
        return 'ONIXMessage';
    }

    /**
     * Get the schema filename.
     *
     * @return string
     */
    public function getSchemaFilename()
    {
        return 'ONIX_BookProduct_3.0_reference.xsd';
    }

    /**
     * Get the namespace URN
     *
     * @return string
     */
    public function getNamespace()
    {
        return 'http://ns.editeur.org/onix/3.0/reference';
    }
}
