<?php

/**
 * @file pages/index/IndexHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class IndexHandler
 *
 * @ingroup pages_index
 *
 * @brief Handle site index requests.
 */

namespace APP\pages\index;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\observers\events\UsageEvent;
use APP\press\FeatureDAO;
use APP\press\NewReleaseDAO;
use APP\press\Press;
use APP\press\PressDAO;
use APP\template\TemplateManager;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\pages\index\PKPIndexHandler;
use PKP\security\Validation;
use PKP\site\Site;

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
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'highlights' => $this->getHighlights($press),
        ]);

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
            $newReleases = $newReleaseDao->getMonographsByAssoc(Application::ASSOC_TYPE_PRESS, $press->getId());
            $templateMgr->assign('newReleases', $newReleases);
        }

        $templateMgr->assign([
            'additionalHomeContent' => $press->getLocalizedSetting('additionalHomeContent'),
            'homepageImage' => $press->getLocalizedSetting('homepageImage'),
            'pageTitleTranslated' => $press->getLocalizedSetting('name'),
            'displayCreativeCommons' => $press->getSetting('includeCreativeCommons'),
            'authorUserGroups' => Repo::userGroup()->getCollector()->filterByRoleIds([\PKP\security\Role::ROLE_ID_AUTHOR])->filterByContextIds([$press->getId()])->getMany()->remember(),
        ]);

        $this->_setupAnnouncements($press, $templateMgr);

        // Display Featured Books
        if ($press->getSetting('displayFeaturedBooks')) {
            $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
            $featuredMonographIds = $featureDao->getSequencesByAssoc(Application::ASSOC_TYPE_PRESS, $press->getId());
            $featuredMonographs = [];
            if (!empty($featuredMonographIds)) {
                foreach ($featuredMonographIds as $submissionId => $value) {
                    $featuredMonographs[] = Repo::submission()->get($submissionId);
                }
            }
            $templateMgr->assign('featuredMonographs', $featuredMonographs);
        }

        $templateMgr->display('frontend/pages/index.tpl');
        event(new UsageEvent(Application::ASSOC_TYPE_PRESS, $press));
        return;
    }
}
