<?php

/**
 * @file plugins/importexport/onix30/Onix30ExportPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportPlugin
 * @ingroup plugins_importexport_onix30
 *
 * @brief ONIX 3.0 XML import/export plugin
 */

use APP\template\TemplateManager;
use PKP\plugins\ImportExportPlugin;

class Onix30ExportPlugin extends ImportExportPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return $success;
        }
        if ($success && $this->getEnabled()) {
            $this->addLocaleData();
            $this->import('Onix30ExportDeployment');
        }
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     *
     * @return string name of plugin
     */
    public function getName()
    {
        return 'Onix30ExportPlugin';
    }

    /**
     * Get the display name.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.importexport.onix30.displayName');
    }

    /**
     * Get the display description.
     *
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.importexport.onix30.description');
    }

    /**
     * @copydoc ImportExportPlugin::getPluginSettingsPrefix()
     */
    public function getPluginSettingsPrefix()
    {
        return 'onix30';
    }

    /**
     * Display the plugin.
     *
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request)
    {
        $templateMgr = TemplateManager::getManager($request);

        $context = $request->getContext();
        $user = $request->getUser();
        $deployment = $this->getAppSpecificDeployment($context, $user);
        $this->setDeployment($deployment);

        parent::display($args, $request);

        $templateMgr->assign('plugin', $this);

        switch (array_shift($args)) {
            case 'index':
            case '':
                $apiUrl = $request->getDispatcher()->url($request, PKPApplication::ROUTE_API, $context->getPath(), 'submissions');
                $submissionsListPanel = new \APP\components\listPanels\SubmissionsListPanel(
                    'submissions',
                    __('common.publications'),
                    [
                        'apiUrl' => $apiUrl,
                        'count' => 100,
                        'getParams' => new stdClass(),
                        'lazyLoad' => true,
                    ]
                );
                $submissionsConfig = $submissionsListPanel->getConfig();
                $submissionsConfig['addUrl'] = '';
                $submissionsConfig['filters'] = array_slice($submissionsConfig['filters'], 1);
                $templateMgr->setState([
                    'components' => [
                        'submissions' => $submissionsConfig,
                    ],
                ]);
                $templateMgr->assign([
                    'pageComponent' => 'ImportExportPage',
                ]);
                $templateMgr->display($this->getTemplateResource('index.tpl'));
                break;
            case 'exportSubmissionsBounce':
                $tab = $this->getBounceTab(
                    $request,
                    __('plugins.importexport.native.export.submissions.results'),
                    'exportSubmissions',
                    ['selectedSubmissions' => $request->getUserVar('selectedSubmissions')]
                );

                return $tab;
            case 'exportSubmissions':
                $submissionIds = (array) $request->getUserVar('selectedSubmissions');

                $this->getExportSubmissionsDeployment($submissionIds, $this->_childDeployment);

                $result = $this->getExportTemplateResult($this->getDeployment(), $templateMgr, 'submissions');

                return $result;
            case 'downloadExportFile':
                $downloadPath = $request->getUserVar('downloadFilePath');
                $this->downloadExportedFile($downloadPath);
                break;
            default:
                $dispatcher = $request->getDispatcher();
                $dispatcher->handle404();
        }
    }

    /**
     * @copydoc ImportExportPlugin::executeCLI($scriptName, $args)
     */
    public function executeCLI($scriptName, &$args)
    {
        throw new BadMethodCallException();
    }

    /**
     * @copydoc ImportExportPlugin::usage
     */
    public function usage($scriptName)
    {
        throw new BadMethodCallException();
    }

    /**
     * @see PKPNativeImportExportPlugin::getExportFilter
     */
    public function getExportFilter($exportType)
    {
        $filter = false;
        if ($exportType == 'exportSubmissions') {
            $filter = 'monographs=>onix30-xml';
        }

        return $filter;
    }

    /**
     * @see ImportExportPlugin::getAppSpecificDeployment
     */
    public function getAppSpecificDeployment($context, $user)
    {
        return new Onix30ExportDeployment($context, $user);
    }
}
