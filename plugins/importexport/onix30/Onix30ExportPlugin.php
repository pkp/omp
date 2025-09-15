<?php

/**
 * @file plugins/importexport/onix30/Onix30ExportPlugin.php
 *
 * Copyright (c) 2014-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportPlugin
 *
 * @ingroup plugins_importexport_onix30
 *
 * @brief ONIX 3.0 XML import/export plugin
 */

namespace APP\plugins\importexport\onix30;

use APP\core\Application;
use APP\core\Request;
use APP\template\TemplateManager;
use BadMethodCallException;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PKP\core\PKPApplication;
use PKP\plugins\ImportExportPlugin;
use PKP\plugins\PluginRegistry;
use stdClass;

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
        if (Application::isUnderMaintenance()) {
            return $success;
        }
        if ($success && $this->getEnabled()) {
            $this->addLocaleData();
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
     * @param Request $request
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
                    ['selectedSubmissions' => $request->getUserVar('selectedSubmissions'), 'validation' => $request->getUserVar('validation')]
                );

                return $tab;
            case 'exportSubmissions':
                $submissionIds = (array) $request->getUserVar('selectedSubmissions');

                $noValidation = $request->getUserVar('validation') ? ['noValidation' => 0] : ['noValidation' => 1];

                $this->getExportSubmissionsDeployment($submissionIds, $this->_childDeployment, $noValidation);

                $result = $this->getExportTemplateResult($this->getDeployment(), $templateMgr, 'submissions');

                return $result;
            case 'downloadExportFile':
                $exportedFileDatePart = $request->getUserVar('exportedFileDatePart');
                $exportedFileContentNamePart = $request->getUserVar('exportedFileContentNamePart');
                $downloadSuccess = $this->downloadExportedFile($exportedFileContentNamePart, $exportedFileDatePart, $this->getDeployment());

                if (!$downloadSuccess) {
                    throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
                }

                break;
            default:
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
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

    /**
     * Helper function to retrieve funding data when available.
     */
    public function getFundingData(int $contextId, int $submissionId): Collection|false
    {
        if (!PluginRegistry::getPlugin('generic', 'FundingPlugin')) {
            return false;
        }

        $fundingData = DB::table('funders AS f')
            ->select('f.funder_id', 'f.funder_identification', 'fs.setting_value as funder_name', 'fa.funder_award_id', 'fa.funder_award_number')
            ->where('submission_id', $submissionId)
            ->where('context_id', $contextId)
            ->leftJoin(
                'funder_settings AS fs',
                fn (JoinClause $j) => $j->on('f.funder_id', '=', 'fs.funder_id')
                    ->where('fs.setting_name', '=', 'funderName')
            )
            ->leftjoin('funder_awards AS fa', 'f.funder_id', '=', 'fa.funder_id')
            ->get()
            ->groupBy('funder_id');

        return $fundingData ?? false;
    }
}
