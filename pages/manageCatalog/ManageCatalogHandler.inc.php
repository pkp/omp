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

import('classes.handler.Handler');

use \PKP\core\JSONMessage;
use \PKP\submission\PKPSubmissionDAO;

use \APP\template\TemplateManager;

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
            [ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER],
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
        import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
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
        $catalogSortBy = empty($catalogSortBy) ? PKPSubmissionDAO::ORDERBY_DATE_PUBLISHED : $catalogSortBy;
        $catalogSortDir = $catalogSortDir == SORT_DIRECTION_ASC ? 'ASC' : 'DESC';
        $catalogList = new \APP\components\listPanels\CatalogListPanel(
            'catalog',
            __('submission.list.monographs'),
            [
                'apiUrl' => $request->getDispatcher()->url(
                    $request,
                    PKPApplication::ROUTE_API,
                    $context->getPath(),
                    '_submissions'
                ),
                'catalogSortBy' => $catalogSortBy,
                'catalogSortDir' => $catalogSortDir,
                'getParams' => [
                    'status' => STATUS_PUBLISHED,
                    'orderByFeatured' => true,
                    'orderBy' => $catalogSortBy,
                    'orderDirection' => $catalogSortDir,
                ],
            ]
        );

        $submissionService = \Services::get('submission');
        $params = array_merge($catalogList->getParams, [
            'count' => $catalogList->count,
            'contextId' => $context->getId(),
        ]);
        $submissionsIterator = $submissionService->getMany($params);
        $items = [];
        foreach ($submissionsIterator as $submission) {
            $items[] = $submissionService->getBackendListProperties($submission, ['request' => $request]);
        }
        $catalogList->set([
            'items' => $items,
            'itemsMax' => $submissionService->getMax($params),
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
