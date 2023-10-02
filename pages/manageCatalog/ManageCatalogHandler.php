<?php

/**
 * @file pages/manageCatalog/ManageCatalogHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogHandler
 *
 * @ingroup pages_manageCatalog
 *
 * @brief Handle requests for catalog management.
 */

namespace APP\pages\manageCatalog;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\submission\Collector;
use APP\submission\Submission;
use APP\template\TemplateManager;
use PKP\db\DAORegistry;
use PKP\security\authorization\PKPSiteAccessPolicy;
use PKP\security\Role;
use PKP\submission\GenreDAO;

class ManageCatalogHandler extends Handler
{
    /** @copydoc PKPHandler::_isBackendPage */
    public $_isBackendPage = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRoleAssignment(
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN],
            ['index']
        );
    }


    //
    // Implement template methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     *
     * @param Request $request
     * @param array $args
     * @param array $roleAssignments
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * @copydoc PKPHandler::initialize()
     */
    public function initialize($request)
    {
        $this->setupTemplate($request);

        // Call parent method.
        parent::initialize($request);
    }


    //
    // Public handler methods
    //
    /**
     * Show the catalog management home.
     *
     * @param array $args
     * @param Request $request
     */
    public function index($args, $request)
    {
        $context = $request->getContext();

        // Catalog list
        $catalogSortBy = Collector::ORDERBY_DATE_PUBLISHED;
        $catalogSortDir = 'DESC';
        if ($context->getData('catalogSortOption')) {
            [$catalogSortBy, $catalogSortDir] = explode('-', $context->getData('catalogSortOption'));
        }
        $catalogList = new \APP\components\listPanels\CatalogListPanel(
            'catalog',
            __('submission.list.monographs'),
            [
                'apiUrl' => $request->getDispatcher()->url(
                    $request,
                    Application::ROUTE_API,
                    $context->getPath(),
                    '_submissions'
                ),
                'catalogSortBy' => $catalogSortBy,
                'catalogSortDir' => $catalogSortDir,
                'getParams' => [
                    'status' => Submission::STATUS_PUBLISHED,
                    'orderByFeatured' => true,
                    'orderBy' => $catalogSortBy,
                    'orderDirection' => $catalogSortDir,
                ],
            ]
        );

        $collector = Repo::submission()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->filterByStatus([Submission::STATUS_PUBLISHED])
            ->orderBy($catalogSortBy, $catalogSortDir)
            ->orderByFeatured();
        $total = $collector->getCount();
        $submissions = $collector->limit($catalogList->count)->getMany();

        $userGroups = Repo::userGroup()->getCollector()
            ->filterByContextIds([$context->getId()])
            ->getMany();

        /** @var GenreDAO $genreDao */
        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genres = $genreDao->getByContextId($context->getId())->toArray();

        $items = Repo::submission()->getSchemaMap()
            ->mapManyToSubmissionsList($submissions, $userGroups, $genres)
            ->values();

        $catalogList->set([
            'items' => $items,
            'itemsMax' => $total,
        ]);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setState([
            'components' => [
                'catalog' => $catalogList->getConfig()
            ]
        ]);

        $templateMgr->assign([
            'pageComponent' => 'ManageCatalogPage',
        ]);

        return $templateMgr->display('manageCatalog/index.tpl');
    }
}
