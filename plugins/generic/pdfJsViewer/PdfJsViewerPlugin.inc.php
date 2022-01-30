<?php

/**
 * @file plugins/generic/pdfJsViewer/PdfJsViewerPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PdfJsViewerPlugin
 * @ingroup plugins_generic_pdfJsViewer
 *
 * @brief Class for PdfJsViewer plugin
 */

use APP\template\TemplateManager;
use PKP\plugins\GenericPlugin;

class PdfJsViewerPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            if ($this->getEnabled($mainContextId)) {
                HookRegistry::register('CatalogBookHandler::view', [$this, 'viewCallback'], HOOK_SEQUENCE_LATE);
                HookRegistry::register('CatalogBookHandler::download', [$this, 'downloadCallback'], HOOK_SEQUENCE_LATE);
            }
            return true;
        }
        return false;
    }

    /**
     * Install default settings on press creation.
     *
     * @return string
     */
    public function getContextSpecificPluginSettingsFile()
    {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the display name of this plugin.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.pdfJsViewer.displayName');
    }

    /**
     * Get a description of the plugin.
     */
    public function getDescription()
    {
        return __('plugins.generic.pdfJsViewer.description');
    }

    /**
     * Callback to view the PDF content rather than downloading.
     *
     * @param string $hookName
     * @param array $args
     *
     * @return bool
     */
    public function viewCallback($hookName, $args)
    {
        $submission = & $args[1];
        $publicationFormat = & $args[2];
        $submissionFile = & $args[3];

        if ($submissionFile->getData('mimetype') == 'application/pdf') {
            foreach ($submission->getData('publications') as $publication) {
                if ($publication->getId() === $publicationFormat->getData('publicationId')) {
                    $filePublication = $publication;
                    break;
                }
            }
            $request = Application::get()->getRequest();
            $router = $request->getRouter();
            $dispatcher = $request->getDispatcher();
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
                'isLatestPublication' => $submission->getData('currentPublicationId') === $publicationFormat->getData('publicationId'),
                'filePublication' => $filePublication,
            ]);

            $templateMgr->display($this->getTemplateResource('display.tpl'));
            return true;
        }

        return false;
    }

    /**
     * Callback for download function
     *
     * @param string $hookName
     * @param array $params
     *
     * @return bool
     */
    public function downloadCallback($hookName, $params)
    {
        $submission = & $params[1];
        $publicationFormat = & $params[2];
        $submissionFile = & $params[3];
        $inline = & $params[4];

        $request = Application::get()->getRequest();
        $mimetype = $submissionFile->getData('mimetype');
        if ($mimetype == 'application/pdf' && $request->getUserVar('inline')) {
            // Turn on the inline flag to ensure that the content
            // disposition header doesn't foil the PDF embedding
            // plugin.
            $inline = true;
        }

        // Return to regular handling
        return false;
    }

    /**
     * Get the plugin base URL.
     *
     * @param PKPRequest $request
     *
     * @return string
     */
    private function _getPluginUrl($request)
    {
        return $request->getBaseUrl() . '/' . $this->getPluginPath();
    }
}
