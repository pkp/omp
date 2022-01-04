<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridHandler
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Handle chapter grid requests.
 */

// import chapter grid specific classes
import('lib.pkp.controllers.grid.users.author.PKPAuthorGridCellProvider');
import('controllers.grid.users.chapter.ChapterGridCategoryRow');

use APP\facades\Repo;
use APP\notification\NotificationManager;
use APP\template\TemplateManager;
use PKP\controllers\grid\CategoryGridHandler;
use PKP\controllers\grid\feature\OrderCategoryGridItemsFeature;
use PKP\controllers\grid\GridColumn;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\security\authorization\PublicationAccessPolicy;

use PKP\security\Role;
use PKP\submission\PKPSubmission;

class ChapterGridHandler extends CategoryGridHandler
{
    /** @var boolean */
    public $_readOnly;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [Role::ROLE_ID_AUTHOR, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_ASSISTANT],
            [
                'fetchGrid', 'fetchRow', 'fetchCategory', 'saveSequence',
                'addChapter', 'editChapter', 'editChapterTab', 'updateChapter', 'deleteChapter',
                'addAuthor', 'editAuthor', 'updateAuthor', 'deleteAuthor'
            ]
        );
        $this->addRoleAssignment(
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER, Role::ROLE_ID_ASSISTANT],
            ['identifiers', 'updateIdentifiers', 'clearPubId',]
        );
        $this->addRoleAssignment(Role::ROLE_ID_REVIEWER, ['fetchGrid', 'fetchRow']);
    }


    //
    // Getters and Setters
    //
    /**
     * Get the monograph associated with this chapter grid.
     *
     * @return Monograph
     */
    public function getMonograph()
    {
        return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
    }

    /**
     * Get the publication associated with this chapter grid.
     *
     * @return Publication
     */
    public function getPublication()
    {
        return $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION);
    }

    /**
     * Get whether or not this grid should be 'read only'
     *
     * @return boolean
     */
    public function getReadOnly()
    {
        return $this->_readOnly;
    }

    /**
     * Set the boolean for 'read only' status
     *
     * @param $readOnly boolean
     */
    public function setReadOnly($readOnly)
    {
        $this->_readOnly = $readOnly;
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
        $this->addPolicy(new PublicationAccessPolicy($request, $args, $roleAssignments));
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * @copydoc CategoryGridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);

        $this->setTitle('submission.chapters');

        AppLocale::requireComponents(LOCALE_COMPONENT_APP_DEFAULT, LOCALE_COMPONENT_PKP_DEFAULT, LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION);

        if ($this->getPublication()->getData('status') === PKPSubmission::STATUS_PUBLISHED) {
            $this->setReadOnly(true);
        }

        if (!$this->getReadOnly()) {
            // Grid actions
            $router = $request->getRouter();
            $actionArgs = $this->getRequestArgs();

            $this->addAction(
                new LinkAction(
                    'addChapter',
                    new AjaxModal(
                        $router->url($request, null, null, 'addChapter', null, $actionArgs),
                        __('submission.chapter.addChapter'),
                        'modal_add_item'
                    ),
                    __('submission.chapter.addChapter'),
                    'add_item'
                )
            );
        }

        // Columns
        // reuse the cell providers for the AuthorGrid
        $cellProvider = new PKPAuthorGridCellProvider($this->getPublication());
        $this->addColumn(
            new GridColumn(
                'name',
                'author.users.contributor.name',
                null,
                null,
                $cellProvider,
                ['width' => 50, 'alignment' => GridColumn::COLUMN_ALIGNMENT_LEFT]
            )
        );
        $this->addColumn(
            new GridColumn(
                'email',
                'author.users.contributor.email',
                null,
                null,
                $cellProvider
            )
        );
        $this->addColumn(
            new GridColumn(
                'role',
                'author.users.contributor.role',
                null,
                null,
                $cellProvider
            )
        );
    }

    /**
     * @see GridHandler::initFeatures()
     */
    public function initFeatures($request, $args)
    {
        if ($this->canAdminister($request->getUser())) {
            $this->setReadOnly(false);
            return [new OrderCategoryGridItemsFeature(OrderCategoryGridItemsFeature::ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS, true, $this)];
        } else {
            $this->setReadOnly(true);
            return [];
        }
    }

    /**
     * @see GridDataProvider::getRequestArgs()
     */
    public function getRequestArgs()
    {
        return array_merge(
            parent::getRequestArgs(),
            [
                'submissionId' => $this->getMonograph()->getId(),
                'publicationId' => $this->getPublication()->getId(),
            ]
        );
    }

    /**
     * Determines if there should be add/edit actions on this grid.
     *
     * @param $user User
     *
     * @return boolean
     */
    public function canAdminister($user)
    {
        $submission = $this->getMonograph();
        $publication = $this->getPublication();
        $userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

        if ($publication->getData('status') === PKPSubmission::STATUS_PUBLISHED) {
            return false;
        }

        if (in_array(Role::ROLE_ID_SITE_ADMIN, $userRoles)) {
            return true;
        }

        // Incomplete submissions can be edited. (Presumably author.)
        if ($submission->getDateSubmitted() == null) {
            return true;
        }

        // The user may not be allowed to edit the metadata
        if (Repo::submission()->canEditPublication($submission->getId(), $user->getId())) {
            return true;
        }

        // Default: Read-only.
        return false;
    }

    /**
     * @see CategoryGridHandler::getCategoryRowIdParameterName()
     */
    public function getCategoryRowIdParameterName()
    {
        return 'chapterId';
    }


    /**
     * @see GridHandler::loadData
     */
    public function loadData($request, $filter)
    {
        return DAORegistry::getDAO('ChapterDAO')
            ->getByPublicationId($this->getPublication()->getId())
            ->toAssociativeArray();
    }


    //
    // Extended methods from GridHandler
    //
    /**
     * @see GridHandler::getDataElementSequence()
     */
    public function getDataElementSequence($gridDataElement)
    {
        return $gridDataElement->getSequence();
    }

    /**
     * @see GridHandler::setDataElementSequence()
     */
    public function setDataElementSequence($request, $chapterId, $chapter, $newSequence)
    {
        if (!$this->canAdminister($request->getUser())) {
            return;
        }

        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /* @var $chapterDao ChapterDAO */
        $chapter->setSequence($newSequence);
        $chapterDao->updateObject($chapter);
    }


    //
    // Implement template methods from CategoryGridHandler
    //
    /**
     * @see CategoryGridHandler::getCategoryRowInstance()
     */
    public function getCategoryRowInstance()
    {
        $monograph = $this->getMonograph();
        $row = new ChapterGridCategoryRow($monograph, $this->getPublication(), $this->getReadOnly());
        import('controllers.grid.users.chapter.ChapterGridCategoryRowCellProvider');
        $row->setCellProvider(new ChapterGridCategoryRowCellProvider());
        return $row;
    }

    /**
     * @see CategoryGridHandler::loadCategoryData()
     *
     * @param null|mixed $filter
     */
    public function loadCategoryData($request, &$chapter, $filter = null)
    {
        return $chapter->getAuthors();
    }

    /**
     * @see CategoryGridHandler::getDataElementInCategorySequence()
     */
    public function getDataElementInCategorySequence($categoryId, &$author)
    {
        return $author->getSequence();
    }

    /**
     * @see CategoryGridHandler::setDataElementInCategorySequence()
     */
    public function setDataElementInCategorySequence($chapterId, &$author, $newSequence)
    {
        if (!$this->canAdminister(Application::get()->getRequest()->getUser())) {
            return;
        }

        $monograph = $this->getMonograph();

        // Remove the chapter author id.
        Repo::author()->removeFromChapter($author->getId(), $chapterId);

        // Add it again with the correct sequence value.
        // FIXME: primary authors not set for chapter authors.
        Repo::author()->addToChapter($author->getId(), $chapterId, false, $newSequence);
    }


    //
    // Public Chapter Grid Actions
    //
    /**
     * Edit chapter pub ids
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function identifiers($args, $request)
    {
        $chapter = $this->_getChapterFromRequest($request);

        import('controllers.tab.pubIds.form.PublicIdentifiersForm');
        $form = new PublicIdentifiersForm($chapter);
        $form->initData();
        return new JSONMessage(true, $form->fetch($request));
    }

    /**
     * Update chapter pub ids
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function updateIdentifiers($args, $request)
    {
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }

        $chapter = $this->_getChapterFromRequest($request);

        import('controllers.tab.pubIds.form.PublicIdentifiersForm');
        $form = new PublicIdentifiersForm($chapter);
        $form->readInputData();
        if ($form->validate()) {
            $form->execute();
            return DAO::getDataChangedEvent();
        } else {
            return new JSONMessage(true, $form->fetch($request));
        }
    }

    /**
     * Clear chapter pub id
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function clearPubId($args, $request)
    {
        if (!$request->checkCSRF()) {
            return new JSONMessage(false);
        }
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }

        $chapter = $this->_getChapterFromRequest($request);

        import('controllers.tab.pubIds.form.PublicIdentifiersForm');
        $form = new PublicIdentifiersForm($chapter);
        $form->clearPubId($request->getUserVar('pubIdPlugIn'));
        return new JSONMessage(true);
    }

    /**
     * Add a chapter.
     *
     * @param $args array
     * @param $request Request
     */
    public function addChapter($args, $request)
    {
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }
        // Calling editChapterTab() with an empty row id will add
        // a new chapter.
        return $this->editChapterTab($args, $request);
    }

    /**
     * Edit a chapter metadata modal
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function editChapter($args, $request)
    {
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }
        $chapter = $this->_getChapterFromRequest($request);

        // Check if this is a remote galley
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'submissionId' => $this->getMonograph()->getId(),
            'publicationId' => $this->getPublication()->getId(),
            'chapterId' => $chapter->getId(),
        ]);

        if (array_intersect([Role::ROLE_ID_MANAGER, Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_ASSISTANT], $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES))) {
            $publisherIdEnabled = in_array('chapter', (array) $request->getContext()->getData('enablePublisherId'));
            $pubIdPlugins = PluginRegistry::getPlugins('pubIds');
            $pubIdEnabled = false;
            foreach ($pubIdPlugins as $pubIdPlugin) {
                if ($pubIdPlugin->isObjectTypeEnabled('Chapter', $request->getContext()->getId())) {
                    $pubIdEnabled = true;
                    break;
                }
            }
            $templateMgr->assign('showIdentifierTab', $publisherIdEnabled || $pubIdEnabled);
        }

        return new JSONMessage(true, $templateMgr->fetch('controllers/grid/users/chapter/editChapter.tpl'));
    }

    /**
     * Edit a chapter
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function editChapterTab($args, $request)
    {
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }
        $chapter = $this->_getChapterFromRequest($request);

        // Form handling
        import('controllers.grid.users.chapter.form.ChapterForm');
        $chapterForm = new ChapterForm($this->getMonograph(), $this->getPublication(), $chapter);
        $chapterForm->initData();

        return new JSONMessage(true, $chapterForm->fetch($request));
    }

    /**
     * Update a chapter
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function updateChapter($args, $request)
    {
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }
        // Identify the chapter to be updated
        $chapter = $this->_getChapterFromRequest($request);

        // Form initialization
        import('controllers.grid.users.chapter.form.ChapterForm');
        $chapterForm = new ChapterForm($this->getMonograph(), $this->getPublication(), $chapter);
        $chapterForm->readInputData();

        // Form validation
        if ($chapterForm->validate()) {
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($request->getUser()->getId());
            $chapterForm->execute();
            return DAO::getDataChangedEvent($chapterForm->getChapter()->getId());
        } else {
            // Return an error
            return new JSONMessage(false);
        }
    }

    /**
     * Delete a chapter
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function deleteChapter($args, $request)
    {
        if (!$this->canAdminister($request->getUser())) {
            return new JSONMessage(false);
        }
        // Identify the chapter to be deleted
        $chapter = $this->_getChapterFromRequest($request);
        $chapterId = $chapter->getId();

        // remove Authors assigned to this chapter first
        Repo::author()->removeChapterAuthors($chapter);

        $chapterDao = DAORegistry::getDAO('ChapterDAO'); /* @var $chapterDao ChapterDAO */
        $chapterDao->deleteById($chapterId);
        return DAO::getDataChangedEvent();
    }

    /**
     * Fetch and validate the chapter from the request arguments
     */
    public function _getChapterFromRequest($request)
    {
        return DAORegistry::getDAO('ChapterDAO')->getChapter(
            (int) $request->getUserVar('chapterId'),
            $this->getPublication()->getId()
        );
    }
}
