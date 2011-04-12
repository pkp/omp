<?php

/**
 * @file classes/manager/form/GroupForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupForm
 * @ingroup controllers_grid_settings_masthead_form
 * @see Group
 *
 * @brief Form for press managers to create/edit groups.
 */



import('lib.pkp.classes.form.Form');
import('lib.pkp.classes.group.Group');

class GroupForm extends Form {
	/** @var groupId int the ID of the group being edited */
	var $group;

	/**
	 * Constructor
	 * @param group Group object; null to create new
	 */
	function GroupForm($group = null) {
		$this->group =& $group;
		parent::Form('controllers/grid/settings/masthead/form/groupForm.tpl');

		// Group title is provided
		$this->addCheck(new FormValidatorLocale($this, 'title', 'required', 'manager.groups.form.groupTitleRequired', Locale::getPrimaryLocale()));
		$this->addCheck(new FormValidatorPost($this));

	}

	/**
	 * Get the list of localized field names for this object
	 * @return array
	 */
	function getLocaleFieldNames() {
		$groupDao =& DAORegistry::getDAO('GroupDAO');
		return $groupDao->getLocaleFieldNames();
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign_by_ref('group', $this->group);
		$templateMgr->assign('helpTopicId', 'press.managementPages.groups');
		$templateMgr->assign('groupContextOptions', array(
			GROUP_CONTEXT_EDITORIAL_TEAM => 'manager.groups.context.editorialTeam',
			GROUP_CONTEXT_PEOPLE => 'manager.groups.context.people'
		));
		return parent::fetch($request);
	}

	/**
	 * Initialize form data from current group group.
	 */
	function initData() {
		if ($this->group != null) {
			$this->_data = array(
				'title' => $this->group->getTitle(null), // Localized
				'context' => $this->group->getContext()
			);
		} else {
			$this->_data = array(
				'context' => GROUP_CONTEXT_EDITORIAL_TEAM
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('title', 'context'));
	}

	/**
	 * Save group group.
	 * @see Form::execute()
	 */
	function execute() {
		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$press =& Request::getPress();

		if (!isset($this->group)) {
			$this->group = $groupDao->newDataObject();
		}

		$this->group->setAssocType(ASSOC_TYPE_PRESS);
		$this->group->setAssocId($press->getId());
		$supportedLocales = $press->getSupportedLocaleNames();
		$title = $this->getData('title');
		if (!empty($supportedLocales)) {
			foreach ($press->getSupportedLocaleNames() as $localeKey => $localeName) {
				$this->group->setTitle($title[$localeKey], $localeKey);
			}
		} else {
			$this->group->setTitle($this->getData('title'), Locale::getLocale()); // Localized
		}
		$this->group->setContext($this->getData('context'));

		// Eventually this will be a general Groups feature; for now,
		// we're just using it to display press team entries in About.
		$this->group->setAboutDisplayed(true);

		// Update or insert group group
		if ($this->group->getId() != null) {
			$groupDao->updateObject($this->group);
		} else {
			$this->group->setSequence(REALLY_BIG_NUMBER);
			$groupDao->insertGroup($this->group);

			// Re-order the groups so the new one is at the end of the list.
			$groupDao->resequenceGroups($this->group->getAssocType(), $this->group->getAssocId());
		}

		return true;
	}
}

?>
