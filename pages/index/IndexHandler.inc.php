<?php

/**
 * @file pages/index/IndexHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IndexHandler
 * @ingroup pages_index
 *
 * @brief Handle site index requests.
 */

import('lib.pkp.pages.index.PKPIndexHandler');

use APP\core\Application;
use APP\facades\Repo;
use APP\observers\events\UsageEvent;
use APP\spotlight\Spotlight;
use APP\template\TemplateManager;

class IndexHandler extends PKPIndexHandler
{
    //
    // Public handler operations
    //
    /**
     * If no press is selected, display list of presses.
     * Otherwise, display the index page for the selected press.
     *
     * @param array $args
     * @param Request $request
     */
    public function index($args, $request)
    {
        $this->validate(null, $request);
        $press = $request->getPress();

        if (!$press) {
            $press = $this->getTargetContext($request, $hasNoContexts);
            if ($press) {
                // There's a target context but no press in the current request. Redirect.
                $request->redirect($press->getPath());
            }
            if ($hasNoContexts && Validation::isSiteAdmin()) {
                // No contexts created, and this is the admin.
                $request->redirect(null, 'admin', 'contexts');
            }
        }

        $this->setupTemplate($request);

        if ($press) {
            // Display the current press home.
            $this->_displayPressIndexPage($press, $request);
        } else {
            // Display the site home.
            $site = $request->getSite();
            $this->_displaySiteIndexPage($site, $request);
        }
    }


    //
    // Private helper methods.
    //
    /**
     * Display the site index page.
     *
     * @param Site $site
     * @param Request $request
     */
    public function _displaySiteIndexPage($site, $request)
    {
        $templateMgr = TemplateManager::getManager($request);
        $pressDao = DAORegistry::getDAO('PressDAO'); /** @var PressDAO $pressDao */

        if ($site->getRedirect() && ($press = $pressDao->getById($site->getRedirect())) != null) {
            $request->redirect($press->getPath());
        }

        $templateMgr->assign([
            'pageTitleTranslated' => $site->getLocalizedTitle(),
            'about' => $site->getLocalizedAbout(),
            'pressesFilesPath' => $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/presses/',
            'presses' => $pressDao->getAll(true)->toArray(),
            'site' => $site,
        ]);
        $templateMgr->setCacheability(TemplateManager::CACHEABILITY_PUBLIC);
        $templateMgr->display('frontend/pages/indexSite.tpl');
    }

    /**
     * Display a given press index page.
     *
     * @param Press $press
     * @param Request $request
     */
    public function _displayPressIndexPage($press, $request)
    {
        $templateMgr = TemplateManager::getManager($request);

        // Display New Releases
        if ($press->getSetting('displayNewReleases')) {
            $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
            $newReleases = $newReleaseDao->getMonographsByAssoc(ASSOC_TYPE_PRESS, $press->getId());
            $templateMgr->assign('newReleases', $newReleases);
        }

        // Assign header and content for home page.
        $templateMgr->assign('additionalHomeContent', $press->getLocalizedSetting('additionalHomeContent'));
        $templateMgr->assign('homepageImage', $press->getLocalizedSetting('homepageImage'));
        $templateMgr->assign('pageTitleTranslated', $press->getLocalizedSetting('name'));

        // Display creative commons logo/licence if enabled.
        $templateMgr->assign('displayCreativeCommons', $press->getSetting('includeCreativeCommons'));

        $this->_setupAnnouncements($press, $templateMgr);

        // Display Featured Books
        if ($press->getSetting('displayFeaturedBooks')) {
            $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
            $featuredMonographIds = $featureDao->getSequencesByAssoc(ASSOC_TYPE_PRESS, $press->getId());
            $featuredMonographs = [];
            if (!empty($featuredMonographIds)) {
                foreach ($featuredMonographIds as $submissionId => $value) {
                    $featuredMonographs[] = Repo::submission()->get($submissionId);
                }
            }
            $templateMgr->assign('featuredMonographs', $featuredMonographs);
        }

        // Display In Spotlight
        if ($press->getSetting('displayInSpotlight')) {
            // Include random spotlight items for the press home page.
            $spotlightDao = DAORegistry::getDAO('SpotlightDAO'); /** @var SpotlightDAO $spotlightDao */
            $spotlights = $spotlightDao->getRandomByPressId($press->getId(), Spotlight::MAX_SPOTLIGHTS_VISIBLE);
            $templateMgr->assign('spotlights', $spotlights);
        }

        $templateMgr->display('frontend/pages/index.tpl');
        if (!$request->isDNTSet()) {
            event(new UsageEvent(Application::ASSOC_TYPE_PRESS, $press->getId(), $press->getId()));
        }
        return;
    }
}
