<?php

/**
 * @file ReviewSetupHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewSetupHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for review setup functions. 
 */

// $Id$

import('workflow.WorkflowProcess');
import('pages.manager.ManagerHandler');

class ReviewSetupHandler extends ManagerHandler {
	/** the reviewTypeId associated with this request **/
	var $reviewTypeId;
	
	/** the review type word **/
	var $entityType;
	
	/**
	 * Constructor
	 */	
	function ReviewSetupHandler() {
		parent::ManagerHandler();
	}
	
	function reviewSignoff($args) {
		if ( isset($args[0]) ) {
			$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));
		} else {
			$this->reviewTypeId = WORKFLOW_PROCESS_ASSESSMENT_INTERNAL;
			$this->reviewType = 'internal';
		}
		$this->validate();
		$this->setupTemplate();
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();		
		$useCustomInternal = $press->getSetting('useCustomInternalReviewSignoff');
		$useCustomExternal = $press->getSetting('useCustomExternalReviewSignoff');

		if ( !($useCustomInternal || $useCustomExternal) 
			|| ($this->reviewTypeId == WORKFLOW_PROCESS_ASSESSMENT_INTERNAL && !$useCustomInternal) 
			|| ($this->reviewTypeId == WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL && !$useCustomExternal )) {
			Request::redirect(null, 'manager');
		}

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$users =& $signoffEntitiesDao->getSignoffUsers($press->getId(), $this->reviewTypeId);
		$groups =& $signoffEntitiesDao->getSignoffGroups($press->getId(), $this->reviewTypeId);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('groups', $groups);

		switch ($this->reviewTypeId) {
			case WORKFLOW_PROCESS_ASSESSMENT_INTERNAL: 
				$templateMgr->assign('reviewType', 'internal');
				break;
			case WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL:
				$templateMgr->assign('reviewType', 'external');
				break;
		}
						
		$templateMgr->display('manager/signoffGroups/index.tpl');
		}
	
	function selectSignoffUser($args) {
		$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));
		$this->validate();
		$this->setupTemplate(true, $args[0]);
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if (Request::getUserVar('roleSymbolic')!=null) $roleSymbolic = Request::getUserVar('roleSymbolic');
		else $roleSymbolic = isset($args[0])?$args[0]:'all';

		if ($roleSymbolic != 'all' && String::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
			$roleId = $roleDao->getRoleIdFromPath($matches[1]);
			if ($roleId == null) {
				Request::redirect(null, null, 'all');
			}
			$roleName = $roleDao->getRoleName($roleId, true);

		} else {
			$roleId = 0;
			$roleName = 'manager.people.allUsers';
		}

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		$searchType = null;
		$searchMatch = null;
		$search = Request::getUserVar('search');
		$searchInitial = Request::getUserVar('searchInitial');
		if (isset($search)) {
			$searchType = Request::getUserVar('searchField');
			$searchMatch = Request::getUserVar('searchMatch');

		} else if (isset($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_INITIAL;
			$search = $searchInitial;
		}

		$rangeInfo = Handler::getRangeInfo('users');

		if ($roleId) {
			$users =& $roleDao->getUsersByRoleId($roleId, $press->getId(), $searchType, $search, $searchMatch, $rangeInfo);
			$templateMgr->assign('roleId', $roleId);
			switch($roleId) {
				case ROLE_ID_PRESS_MANAGER:
					$helpTopicId = 'press.roles.manager';
					break;
				case ROLE_ID_AUTHOR:
					$helpTopicId = 'press.roles.author';
					break;
				case ROLE_ID_EDITOR:
					$helpTopicId = 'press.roles.editor';
					break;
				case ROLE_ID_REVIEWER:
					$helpTopicId = 'press.roles.reviewer';
					break;
				case ROLE_ID_ACQUISITIONS_EDITOR:
					$helpTopicId = 'press.roles.acquisitionsEditor';
					break;
				case ROLE_ID_DESIGNER:
					$helpTopicId = 'press.roles.designer';
					break;
				case ROLE_ID_COPYEDITOR:
					$helpTopicId = 'press.roles.copyeditor';
					break;
				case ROLE_ID_PROOFREADER:
					$helpTopicId = 'press.roles.proofreader';
					break;
				case ROLE_ID_COMMITTEE_MEMBER:
					$helpTopicId = 'press.roles.editorialMember';
					break;
				case ROLE_ID_PRODUCTION_EDITOR:
					$helpTopicId = 'press.roles.productionEditor';
					break;
				case ROLE_ID_READER:
					$helpTopicId = 'press.roles.reader';
					break;
				default:
					$helpTopicId = 'press.roles.index';
					break;
			}
		} else {
			$users =& $roleDao->getUsersByPressId($press->getId(), $searchType, $search, $searchMatch, $rangeInfo);
			$helpTopicId = 'press.users.allUsers';
		}

		$templateMgr->assign('currentUrl', Request::url(null, 'people', 'all'));
		$templateMgr->assign('roleName', $roleName);
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', Request::getUser());
		$templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', Request::getUserVar('searchInitial'));

		$templateMgr->assign('helpTopicId', $helpTopicId);
		$fieldOptions = Array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_INTERESTS => 'user.interests',
			USER_FIELD_EMAIL => 'user.email'
		);

		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('rolePath', $roleDao->getRolePath($roleId));
		$templateMgr->assign('alphaList', explode(' ', Locale::translate('common.alphaList')));
		$templateMgr->assign('roleSymbolic', $roleSymbolic);

		$session =& Request::getSession();
        $session->setSessionVar('enrolmentReferrer', Request::getRequestedArgs());

		$templateMgr->display('manager/signoffGroups/selectUsers.tpl');
	}

	function selectSignoffGroup($args) {
		$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));
		$this->validate();
		$this->setupTemplate(true, $args[0]);
		$press =& Request::getPress();

		$rangeInfo =& Handler::getRangeInfo('groups');

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$groups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, $press->getId(), null, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('groups', $groups);		
		$templateMgr->display('manager/signoffGroups/selectGroups.tpl');
	}
		
	function removeSignoffGroup($args) {
		$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));
		$this->validate();
		$press =& Request::getPress();
		$reviewTypeId = $this->reviewTypeId;
		
		$groupId = (int) Request::getUserVar('groupId');

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->remove(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_GROUP, $groupId);
		Request::redirect(null, null, 'reviewSignoff', $this->reviewType);
	}
	
	function removeSignoffUser($args) {
		$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));
		$this->validate();
		$press =& Request::getPress();
		$reviewTypeId = $this->reviewTypeId;
		$userId = (int) Request::getUserVar('userId');

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->remove(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_USER, $userId);
		Request::redirect(null, null, 'reviewSignoff', $this->reviewType);
	}
	
	function addSignoffGroup($args) {
		$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));
		$this->validate();
		$this->setupTemplate();
		$press =& Request::getPress();
		$reviewTypeId = $this->reviewTypeId;

		$groupId = Request::getUserVar('groupId');

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->build(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_GROUP, $groupId);
		Request::redirect(null, null, 'reviewSignoff', $this->reviewType);
	}
	
	function addSignoffUser($args) {
		$this->addCheck(new HandlerValidatorCustom($this, false, null, null, array(&$this, '_validReviewType'), $args[0]));		
		$this->validate();
		$reviewTypeId = $this->reviewTypeId;
		$userId = Request::getUserVar('userId');

		$this->setupTemplate();
		$press =& Request::getPress();

		$signoffEntitiesDao =& DAORegistry::getDAO('SignoffEntityDAO');
		$signoffEntitiesDao->build(WORKFLOW_PROCESS_ASSESSMENT, $reviewTypeId, $press->getId(),
									SIGNOFF_ENTITY_TYPE_USER, $userId);
		Request::redirect(null, null, 'reviewSignoff', $this->reviewType);
	}


	function setupTemplate($subclass = false, $reviewType = null) {
		parent::setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'reviewSignoff'), 'manager.reviewSignoff.process'));
		}
		$templateMgr->assign('reviewType', $reviewType);	
	}
	
	function _validReviewType($reviewType) {
		switch ($reviewType) {
			case 'internal':
				$this->reviewTypeId = WORKFLOW_PROCESS_ASSESSMENT_INTERNAL;
				$this->reviewType = 'internal';
				break;
			case 'external':
				$this->reviewTypeId = WORKFLOW_PROCESS_ASSESSMENT_EXTERNAL;
				$this->reviewType = 'external';
				break;
			default:
				Request::redirect(null, 'manager');
		}
		
		return true;
	}	
}

?>
