<?php

/**
 * @file plugins/generic/usageEvent/UsageEventPlugin.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageEventPlugin
 * @ingroup plugins_generic_usageEvent
 *
 * @brief Implements application specifics for usage event generation.
 */

namespace APP\plugins\generic\usageEvent;

use APP\core\Application;
use APP\submission\Submission;

class UsageEventPlugin extends \PKP\plugins\generic\usageEvent\PKPUsageEventPlugin
{
    //
    // Protected methods.
    //
    /**
     * @see PKPUsageEventPlugin::getEventHooks()
     */
    protected function getEventHooks()
    {
        $hooks = parent::getEventHooks();
        $ompHooks = [
            'CatalogBookHandler::view',
            'CatalogBookHandler::download',
            'HtmlMonographFilePlugin::monographDownload',
            'HtmlMonographFilePlugin::monographDownloadFinished',
        ];

        return array_merge($hooks, $ompHooks);
    }

    /**
     * @copydoc PKPUsageEventPlugin::getDownloadFinishedEventHooks()
     */
    protected function getDownloadFinishedEventHooks()
    {
        return array_merge(parent::getDownloadFinishedEventHooks(), [
            'HtmlMonographFilePlugin::monographDownloadFinished'
        ]);
    }

    /**
     * @see PKPUsageEventPlugin::getUsageEventData()
     */
    protected function getUsageEventData($hookName, $hookArgs, $request, $router, $templateMgr, $context)
    {
        [$pubObject, $downloadSuccess, $assocType, $idParams, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams] =
            parent::getUsageEventData($hookName, $hookArgs, $request, $router, $templateMgr, $context);

        if (!$pubObject) {
            switch ($hookName) {
                // Catalog index page, series content page and monograph abstract.
                case 'TemplateManager::display':
                    $page = $router->getRequestedPage($request);
                    $op = $router->getRequestedOp($request);
                    $args = $router->getRequestedArgs($request);

                    $wantedPages = ['catalog'];
                    $wantedOps = ['index', 'book', 'series'];

                    if (!in_array($page, $wantedPages) || !in_array($op, $wantedOps)) {
                        break;
                    }

                    // consider book versioning:
                    // if the operation is 'book' and the arguments count > 1
                    // the arguments must be: $submissionId/version/$publicationId.
                    if ($op == 'book' && count($args) > 1) {
                        if ($args[1] !== 'version') {
                            break;
                        } elseif (count($args) != 3) {
                            break;
                        }
                        $publicationId = (int) $args[2];
                    }

                    $press = $templateMgr->getTemplateVars('currentContext'); /** @var Press $press */
                    $series = $templateMgr->getTemplateVars('series'); /** @var Series $series */
                    $submission = $templateMgr->getTemplateVars('publishedSubmission');

                    // No published objects, no usage event.
                    if (!$press && !$series && !$submission) {
                        break;
                    }

                    if ($press) {
                        $pubObject = $press;
                        $assocType = Application::ASSOC_TYPE_PRESS;
                    }

                    if ($series) {
                        $pubObject = $series;
                        $assocType = Application::ASSOC_TYPE_SERIES;
                        $canonicalUrlParams = [$series->getPath()];
                        $idParams = ['s' . $series->getId()];
                    }

                    if ($submission) {
                        $pubObject = $submission;
                        $assocType = Application::ASSOC_TYPE_MONOGRAPH;
                        $canonicalUrlParams = [$pubObject->getId()];
                        $idParams = ['m' . $pubObject->getId()];
                        if (isset($publicationId)) {
                            // no need to check if the publication exists (for the submisison),
                            // 404 would be returned and the usage event would not be there
                            $canonicalUrlParams = [$pubObject->getId(), 'version', $publicationId];
                        }
                    }

                    $downloadSuccess = true;
                    $canonicalUrlOp = $op;
                    break;

                    // Publication format file.
                case 'CatalogBookHandler::view':
                case 'CatalogBookHandler::download':
                case 'HtmlMonographFilePlugin::monographDownload':
                    $pubObject = $hookArgs[3];
                    $assocType = Application::ASSOC_TYPE_SUBMISSION_FILE;
                    $canonicalUrlOp = 'download';
                    $submission = $hookArgs[1];
                    $publicationFormat = $hookArgs[2];
                    // if file is not a publication format file (e.g. CSS or images), there is no usage event.
                    if ($pubObject->getData('assocId') != $publicationFormat->getId()) {
                        return false;
                    }
                    $canonicalUrlParams = [$submission->getId(), $pubObject->getData('assocId'), $pubObject->getId()];
                    $idParams = ['m' . $submission->getId(), 'f' . $pubObject->getId()];
                    $downloadSuccess = false;
                    break;
                default:
                    // Why are we called from an unknown hook?
                    assert(false);
            }

            switch ($assocType) {
                case Application::ASSOC_TYPE_PRESS:
                case Application::ASSOC_TYPE_SERIES:
                case Application::ASSOC_TYPE_MONOGRAPH:
                case Application::ASSOC_TYPE_SUBMISSION_FILE:
                    $canonicalUrlPage = 'catalog';
                    break;
            }
        }

        return [$pubObject, $downloadSuccess, $assocType, $idParams, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams];
    }

    /**
     * @see PKPUsageEventPlugin::getHtmlPageAssocTypes()
     */
    protected function getHtmlPageAssocTypes()
    {
        return [
            Application::ASSOC_TYPE_PRESS,
            Application::ASSOC_TYPE_SERIES,
            Application::ASSOC_TYPE_MONOGRAPH
        ];
    }

    /**
     * @see PKPUsageEventPlugin::isPubIdObjectType()
     */
    protected function isPubIdObjectType($pubObject)
    {
        return $pubObject instanceof Submission;
    }
}
