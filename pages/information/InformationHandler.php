<?php

/**
 * @file pages/information/InformationHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class InformationHandler
 *
 * @ingroup pages_information
 *
 * @brief Display press information.
 */

namespace APP\pages\information;

use APP\core\Application;
use APP\core\Request;
use APP\handler\Handler;
use APP\template\TemplateManager;

class InformationHandler extends Handler
{
    /**
     * Display the information page for the press.
     *
     * @param array $args
     * @param Request $request
     */
    public function index($args, $request)
    {
        $this->validate(null, $request);
        $press = $request->getPress();
        if ($press == null) {
            $request->redirect(Application::SITE_CONTEXT_PATH);
        }

        $this->setupTemplate($request);

        $contentOnly = $request->getUserVar('contentOnly');

        switch (array_shift($args)) {
            case 'readers':
                $content = $press->getLocalizedSetting('readerInformation');
                $pageTitle = 'navigation.infoForReaders.long';
                $pageCrumbTitle = 'navigation.infoForReaders';
                break;
            case 'authors':
                $content = $press->getLocalizedSetting('authorInformation');
                $pageTitle = 'navigation.infoForAuthors.long';
                $pageCrumbTitle = 'navigation.infoForAuthors';
                break;
            case 'librarians':
                $content = $press->getLocalizedSetting('librarianInformation');
                $pageTitle = 'navigation.infoForLibrarians.long';
                $pageCrumbTitle = 'navigation.infoForLibrarians';
                break;
            case 'competingInterestPolicy':
                $content = $press->getLocalizedSetting('competingInterestPolicy');
                $pageTitle = $pageCrumbTitle = 'navigation.competingInterestPolicy';
                break;
            case 'sampleCopyrightWording':
                $content = __('manager.setup.copyrightNotice.sample');
                $pageTitle = $pageCrumbTitle = 'manager.setup.copyrightNotice';
                break;
            default:
                $request->redirect($press->getPath());
                return;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageCrumbTitle', $pageCrumbTitle);
        $templateMgr->assign('pageTitle', $pageTitle);
        $templateMgr->assign('content', $content);
        $templateMgr->assign('contentOnly', $contentOnly); // Hide the header and footer code

        $templateMgr->display('frontend/pages/information.tpl');
    }

    public function readers($args, $request)
    {
        $this->index(['readers'], $request);
    }

    public function authors($args, $request)
    {
        $this->index(['authors'], $request);
    }

    public function librarians($args, $request)
    {
        $this->index(['librarians'], $request);
    }

    public function competingInterestPolicy($args, $request)
    {
        return $this->index(['competingInterestPolicy'], $request);
    }

    public function sampleCopyrightWording($args, $request)
    {
        $this->index(['sampleCopyrightWording'], $request);
    }

    /**
     * Initialize the template.
     */
    public function setupTemplate($request)
    {
        parent::setupTemplate($request);
        if (!$request->getPress()->getSetting('restrictSiteAccess')) {
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->setCacheability(TemplateManager::CACHEABILITY_PUBLIC);
        }
    }
}
