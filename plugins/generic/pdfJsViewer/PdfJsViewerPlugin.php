<?php

/**
 * @file plugins/generic/pdfJsViewer/PdfJsViewerPlugin.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PdfJsViewerPlugin
 *
 * @brief Class for PdfJsViewer plugin
 */

namespace APP\plugins\generic\pdfJsViewer;

use APP\core\Application;
use APP\publication\Publication;
use APP\template\TemplateManager;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

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
                Hook::add('CatalogBookHandler::view', [$this, 'viewCallback'], Hook::SEQUENCE_LATE);
                Hook::add('CatalogBookHandler::download', [$this, 'downloadCallback'], Hook::SEQUENCE_LATE);
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

        $request = $this->getRequest();
		$dispatcher = $request->getDispatcher();

        if ($submissionFile->getData('mimetype') == 'application/pdf') {
            /** @var ?Publication */
            $filePublication = null;
            foreach ($submission->getData('publications') as $publication) {
                if ($publication->getId() === $publicationFormat->getData('publicationId')) {
                    $filePublication = $publication;
                    break;
                }
            }
            $request = Application::get()->getRequest();
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign([
                'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
                'isLatestPublication' => $submission->getData('currentPublicationId') === $publicationFormat->getData('publicationId'),
                'filePublication' => $filePublication,
                'downloadUrl' => $dispatcher->url($request, ROUTE_PAGE, null, null, 'download', array($submission->getBestId(), $publicationFormat->getBestId(), $submissionFile->getBestId()), array('inline' => true)),
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
}
