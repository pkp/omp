<?php

/**
 * @file controllers/grid/catalogEntry/RepresentativesGridHandler.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class RepresentativesGridHandler
 *
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for representatives.
 */

namespace APP\controllers\grid\catalogEntry;

use APP\controllers\grid\catalogEntry\form\RepresentativeForm;
use APP\core\Application;
use APP\core\Request;
use APP\monograph\RepresentativeDAO;
use APP\notification\Notification;
use APP\notification\NotificationManager;
use APP\publicationFormat\MarketDAO;
use APP\submission\Submission;
use PKP\controllers\grid\CategoryGridHandler;
use PKP\controllers\grid\GridColumn;
use PKP\core\JSONMessage;
use PKP\db\DAO;
use PKP\db\DAORegistry;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\security\authorization\SubmissionAccessPolicy;
use PKP\security\Role;

class RepresentativesGridHandler extends CategoryGridHandler
{
    /** @var Submission */
    public $_monograph;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_MANAGER, Role::ROLE_ID_SITE_ADMIN, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT],
            ['fetchGrid', 'fetchCategory', 'fetchRow', 'addRepresentative', 'editRepresentative',
                'updateRepresentative', 'deleteRepresentative']
        );
    }


    //
    // Getters/Setters
    //
    /**
     * Get the monograph associated with this grid.
     *
     * @return Submission
     */
    public function getMonograph()
    {
        return $this->_monograph;
    }

    /**
     * Set the Monograph
     *
     * @param Submission
     */
    public function setMonograph($monograph)
    {
        $this->_monograph = $monograph;
    }


    //
    // Overridden methods from PKPHandler
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
        $this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * @copydoc CategoryGridHandler::initialize
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);

        // Retrieve the authorized monograph.
        $this->setMonograph($this->getAuthorizedContextObject(Application::ASSOC_TYPE_MONOGRAPH));

        $representativeId = (int) $request->getUserVar('representativeId'); // set if editing or deleting a representative entry

        if ($representativeId != 0) {
            $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
            $representative = $representativeDao->getById($representativeId, $this->getMonograph()->getId());
            if (!isset($representative)) {
                throw new \Exception('Representative referenced outside of authorized monograph context!');
            }
        }

        // Basic grid configuration
        $this->setTitle('grid.catalogEntry.representatives');

        // Grid actions
        $router = $request->getRouter();
        $actionArgs = $this->getRequestArgs();
        $this->addAction(
            new LinkAction(
                'addRepresentative',
                new AjaxModal(
                    $router->url($request, null, null, 'addRepresentative', null, $actionArgs),
                    __('grid.action.addRepresentative'),
                    'modal_add_item'
                ),
                __('grid.action.addRepresentative'),
                'add_item'
            )
        );

        // Columns
        $cellProvider = new RepresentativesGridCellProvider();
        $this->addColumn(
            new GridColumn(
                'name',
                'grid.catalogEntry.representativeName',
                null,
                null,
                $cellProvider
            )
        );
        $this->addColumn(
            new GridColumn(
                'role',
                'grid.catalogEntry.representativeRole',
                null,
                null,
                $cellProvider
            )
        );
    }


    //
    // Overridden methods from GridHandler
    //
    /**
     * @see GridHandler::getRowInstance()
     *
     * @return RepresentativesGridRow
     */
    public function getRowInstance()
    {
        return new RepresentativesGridRow($this->getMonograph());
    }

    /**
     * @see CategoryGridHandler::getCategoryRowInstance()
     *
     * @return RepresentativesGridCategoryRow
     */
    public function getCategoryRowInstance()
    {
        return new RepresentativesGridCategoryRow();
    }

    /**
     * @see CategoryGridHandler::loadCategoryData()
     *
     * @param null|mixed $filter
     */
    public function loadCategoryData($request, &$category, $filter = null)
    {
        $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
        if ($category['isSupplier']) {
            $representatives = $representativeDao->getSuppliersByMonographId($this->getMonograph()->getId());
        } else {
            $representatives = $representativeDao->getAgentsByMonographId($this->getMonograph()->getId());
        }
        return $representatives->toAssociativeArray();
    }

    /**
     * @see CategoryGridHandler::getCategoryRowIdParameterName()
     */
    public function getCategoryRowIdParameterName()
    {
        return 'representativeCategoryId';
    }

    /**
     * @see CategoryGridHandler::getRequestArgs()
     */
    public function getRequestArgs()
    {
        $monograph = $this->getMonograph();
        return array_merge(
            parent::getRequestArgs(),
            ['submissionId' => $monograph->getId()]
        );
    }

    /**
     * @see GridHandler::loadData
     *
     * @param null|mixed $filter
     */
    public function loadData($request, $filter = null)
    {
        // set our labels for the two Representative categories
        $categories = [
            ['name' => 'grid.catalogEntry.agentsCategory', 'isSupplier' => false],
            ['name' => 'grid.catalogEntry.suppliersCategory', 'isSupplier' => true]
        ];

        return $categories;
    }


    //
    // Public Representatives Grid Actions
    //

    public function addRepresentative($args, $request)
    {
        return $this->editRepresentative($args, $request);
    }

    /**
     * Edit a representative entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function editRepresentative($args, $request)
    {
        // Identify the representative entry to be updated
        $representativeId = (int) $request->getUserVar('representativeId');
        $monograph = $this->getMonograph();

        $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
        $representative = $representativeDao->getById($representativeId, $monograph->getId());

        // Form handling
        $representativeForm = new RepresentativeForm($monograph, $representative);
        $representativeForm->initData();

        return new JSONMessage(true, $representativeForm->fetch($request));
    }

    /**
     * Update a representative entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function updateRepresentative($args, $request)
    {
        // Identify the representative entry to be updated
        $representativeId = $request->getUserVar('representativeId');
        $monograph = $this->getMonograph();

        $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
        $representative = $representativeDao->getById($representativeId, $monograph->getId());

        // Form handling
        $representativeForm = new RepresentativeForm($monograph, $representative);
        $representativeForm->readInputData();
        if ($representativeForm->validate()) {
            $representativeId = $representativeForm->execute();

            if (!isset($representative)) {
                // This is a new entry
                $representative = $representativeDao->getById($representativeId, $monograph->getId());
                // New added entry action notification content.
                $notificationContent = __('notification.addedRepresentative');
            } else {
                // entry edit action notification content.
                $notificationContent = __('notification.editedRepresentative');
            }

            // Create trivial notification.
            $currentUser = $request->getUser();
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => $notificationContent]);

            // Prepare the grid row data
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($representativeId);
            $row->setData($representative);
            $row->initialize($request);

            // Render the row into a JSON response
            return DAO::getDataChangedEvent($representativeId, (int) $representative->getIsSupplier());
        } else {
            return new JSONMessage(true, $representativeForm->fetch($request));
        }
    }

    /**
     * Delete a representative entry
     *
     * @param array $args
     * @param Request $request
     *
     * @return JSONMessage JSON object
     */
    public function deleteRepresentative($args, $request)
    {
        // Identify the representative entry to be deleted
        $representativeId = $request->getUserVar('representativeId');

        $representativeDao = DAORegistry::getDAO('RepresentativeDAO'); /** @var RepresentativeDAO $representativeDao */
        $representative = $representativeDao->getById($representativeId, $this->getMonograph()->getId());

        if (!$representative) {
            return new JSONMessage(false, __('manager.setup.errorDeletingItem'));
        }

        // Don't allow a representative to be deleted if they are associated
        // with a publication format's market metadata
        $submission = $this->getAuthorizedContextObject(Application::ASSOC_TYPE_SUBMISSION);
        /** @var MarketDAO */
        $marketDao = DAORegistry::getDAO('MarketDAO');
        foreach ($submission->getData('publications') as $publication) {
            foreach ($publication->getData('publicationFormats') as $publicationFormat) {
                $markets = $marketDao->getByPublicationFormatId($publicationFormat->getId())->toArray();
                foreach ($markets as $market) {
                    if (in_array($representative->getId(), [$market->getAgentId(), $market->getSupplierId()])) {
                        return new JSONMessage(false, __('manager.representative.inUse'));
                    }
                }
            }
        }

        $representativeDao->deleteObject($representative);
        $currentUser = $request->getUser();
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification($currentUser->getId(), Notification::NOTIFICATION_TYPE_SUCCESS, ['contents' => __('notification.removedRepresentative')]);
        return DAO::getDataChangedEvent($representative->getId(), (int) $representative->getIsSupplier());
    }
}
