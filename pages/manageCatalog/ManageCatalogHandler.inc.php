<?php

/**
 * @file pages/manageCatalog/ManageCatalogHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ManageCatalogHandler
 * @ingroup pages_manageCatalog
 *
 * @brief Handle requests for catalog management.
 */

use APP\core\Application;
use APP\facades\Repo;
use APP\handler\Handler;
use APP\submission\Collector;
use APP\submission\Submission;

use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use PKP\db\DAORegistry;
use PKP\security\authorization\PKPSiteAccessPolicy;
use PKP\security\Role;

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
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER],
            ['index']
        );
    }


    //
    // Implement template methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     *
     * @param $request PKPRequest
     * @param $args array
     * @param $roleAssignments array
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
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function index($args, $request)
    {
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);
        $context = $request->getContext();

        // Catalog list
        [$catalogSortBy, $catalogSortDir] = explode('-', $context->getData('catalogSortOption'));
        $catalogSortBy = empty($catalogSortBy) ? Collector::ORDERBY_DATE_PUBLISHED : $catalogSortBy;
        $catalogSortDir = $catalogSortDir == Collector::ORDER_DIR_ASC ? 'ASC' : 'DESC';
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
        $total = Repo::submission()->getCount($collector);
        $submissions = Repo::submission()->getMany($collector->limit($catalogList->count));

        $userGroupDao = DAORegistry::getDAO('UserGroupDAO'); /** @var UserGroupDAO $userGroupDao */
        $userGroups = $userGroupDao->getByContextId($context->getId())->toArray();

        $catalogList->set([
            'items' => Repo::submission()->getSchemaMap()->mapManyToSubmissionsList($submissions, $userGroups),
            'itemsMax' => $total,
        ]);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setState([
            'components' => [
                'catalog' => $catalogList->getConfig()
            ]
        ]);
        return $templateMgr->display('manageCatalog/index.tpl');
    }
}
