<?php

/**
 * @file plugins/importexport/native/NativeImportExportPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeImportExportPlugin
 *
 * @brief Native XML import/export plugin
 */

namespace APP\plugins\importexport\native;

class NativeImportExportPlugin extends \PKP\plugins\importexport\native\PKPNativeImportExportPlugin
{
    /**
     * @see ImportExportPlugin::display()
     */
    public function display($args, $request)
    {
        $context = $request->getContext();
        $user = $request->getUser();
        $deployment = new NativeImportExportDeployment($context, $user);

        $this->setDeployment($deployment);

        parent::display($args, $request);

        if ($this->isResultManaged) {
            if ($this->result) {
                return $this->result;
            }

            return false;
        }

        switch ($this->opType) {
            default:
                $dispatcher = $request->getDispatcher();
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }
    }

    /**
     * @see ImportExportPlugin::getImportFilter
     */
    public function getImportFilter($xmlFile)
    {
        $filter = 'native-xml=>monograph';

        $xmlString = file_get_contents($xmlFile);

        return [$filter, $xmlString];
    }

    /**
     * @see ImportExportPlugin::getExportFilter
     */
    public function getExportFilter($exportType)
    {
        $filter = false;
        if ($exportType == 'exportSubmissions') {
            $filter = 'monograph=>native-xml';
        }

        return $filter;
    }

    /**
     * @see ImportExportPlugin::getAppSpecificDeployment
     */
    public function getAppSpecificDeployment($journal, $user)
    {
        return new NativeImportExportDeployment($journal, $user);
    }
}
