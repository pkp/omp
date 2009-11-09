<?php

/**
 * @file classes/manager/form/setup/PressSetupStep3Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep3Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 3 of press setup.
 */

// $Id: PressSetupStep3Form.inc.php,v 1.14 2009/11/09 16:23:47 tylerl Exp $

import('manager.form.setup.PressSetupForm');
import('role.FlexibleRole');

class PressSetupStep3Form extends PressSetupForm {

	/**
	 * Constructor.
	 */
	function PressSetupStep3Form() {
		parent::PressSetupForm(
			3,
			array(
			)
		);
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		parent::initData();
		$press =& Request::getPress();
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$flexibleRoles = $flexibleRoleDao->getEnabledByPressId($press->getId());
		$roleArrangements = array();
		$additionalRoles = array();
		$idMap = array();

		for ($i=0,$count=count($flexibleRoles); $i<$count; $i++) {
			$additionalRoles[$flexibleRoles[$i]->getType()][$i+1]['name'] = $flexibleRoles[$i]->getName();
			$additionalRoles[$flexibleRoles[$i]->getType()][$i+1]['abbrev'] = $flexibleRoles[$i]->getAbbrev();
			$additionalRoles[$flexibleRoles[$i]->getType()][$i+1]['flexibleRoleId'] = $flexibleRoles[$i]->getId();
			$idMap[$flexibleRoles[$i]->getId()] = $i+1;
		}

		foreach($this->getFlexibleRoleArrangements() as $id => $roleArrangement) {
			$roleIds[$id] = $flexibleRoleDao->getByArrangementId($press->getId(), $id, true);
			foreach ($roleIds[$id] as $roleArrangement) {
				$roleArrangements[$id][$idMap[$roleArrangement]] = '';
			}
		}

		$this->_data = array_merge($this->_data,
				array(
					'additionalRoles' => $additionalRoles,
					'submissionRoles' => $roleArrangements[FLEXIBLE_ROLE_ARRANGEMENT_SUBMISSION],
					'internalReviewRoles' => $roleArrangements[FLEXIBLE_ROLE_ARRANGEMENT_INTERNAL_REVIEW],
					'externalReviewRoles' => $roleArrangements[FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW],
					'editorialRoles' => $roleArrangements[FLEXIBLE_ROLE_ARRANGEMENT_EDITORIAL],
					'productionRoles' => $roleArrangements[FLEXIBLE_ROLE_ARRANGEMENT_PRODUCTION],
					'nextRoleId' => $i + 1
				)
			);
	}

	/**
	 * Get a list of field names for which localized settings are used
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('newBookFileName', 'newBookFileDesignation', 'newPublicationFormatName', 'newPublicationFormatDesignation');
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
				'additionalRoles', 'newBookFileSortable', 'newBookFileName', 'newBookFileDesignation', 
				'bookFileTypeSelect', 'newPublicationFormatName', 'newPublicationFormatDesignation', 'publicationFormatSelect',
				'newRole', 'deletedFlexibleRoles', 'nextRoleId', 'submissionRoles', 'internalReviewRoles', 'externalReviewRoles', 
				'editorialRoles', 'productionRoles', 'bookFileTypeUpdate'
			)
		);
		parent::readInputData();
	}

	/**
	 * Get the list of flexible role arrangements.
	 * @return array
	 */
	function getFlexibleRoleArrangements() {
		return array(
			FLEXIBLE_ROLE_ARRANGEMENT_SUBMISSION => 'submissionRoles', 
			FLEXIBLE_ROLE_ARRANGEMENT_INTERNAL_REVIEW => 'internalReviewRoles', 
			FLEXIBLE_ROLE_ARRANGEMENT_EXTERNAL_REVIEW => 'externalReviewRoles', 
			FLEXIBLE_ROLE_ARRANGEMENT_EDITORIAL => 'editorialRoles', 
			FLEXIBLE_ROLE_ARRANGEMENT_PRODUCTION => 'productionRoles'
		);
	}

	/**
	 * Display the form
	 */
	function display() {
		$bookFileTypeDao =& DAORegistry::getDAO('BookFileTypeDAO');
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();

		$bookFileTypes =& $bookFileTypeDao->getEnabledByPressId($press->getId());
		$publicationFormats =& $publicationFormatDao->getEnabledByPressId($press->getId());

		$templateMgr->assign_by_ref('bookFileTypes', $bookFileTypes);
		$templateMgr->assign_by_ref('publicationFormats', $publicationFormats);

		parent::display();
	}

	/**
	 * Execute the form, but first clean up role data.
	 */
	function execute() {
		$press =& Request::getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$flexibleRoleDao =& DAORegistry::getDAO('FlexibleRoleDAO');

		$additionalRoles = $this->getData('additionalRoles');

		foreach ($additionalRoles as $type => $roles) {
			foreach ($roles as $key => $additionalRole) {
				if (!empty($additionalRole['flexibleRoleId'])) {
					$flexibleRoleId = $additionalRole['flexibleRoleId'];
					// Update an existing flexible role
					$flexibleRole =& $flexibleRoleDao->getById($flexibleRoleId);
					$isExistingFlexibleRole = true;
				} else {
					// Create a new flexible role
					$flexibleRole = $flexibleRoleDao->newDataObject();
					$isExistingFlexibleRole = false;
				}

				$flexibleRole->setPressId($press->getId());
				$flexibleRole->setName($additionalRole['name'], null);
				$flexibleRole->setAbbrev($additionalRole['abbrev'], null);
				$flexibleRole->setType($type);
				$flexibleRole->setEnabled(true);

				$flexibleRole->clearAssociatedArrangements();

				foreach ($this->getFlexibleRoleArrangements() as $id => $arrangement) {
					$arrangementRoles = $this->getData($arrangement);
					if (isset($arrangementRoles[$key])) {
						$flexibleRole->addAssociatedArrangement($id);
					}
				}

				if (!$isExistingFlexibleRole) {
					$flexibleRoleDao->insertObject($flexibleRole);
				} else {
					$flexibleRoleDao->updateObject($flexibleRole);
				}
				unset($flexibleRole);
			}
		}

		// Remove deleted flexible roles
		$deletedFlexibleRoles = explode(':', $this->getData('deletedFlexibleRoles'));
		for ($i=0, $count=count($deletedFlexibleRoles); $i < $count; $i++) {
			$flexibleRoleDao->deleteById($deletedFlexibleRoles[$i]);
		}

		return parent::execute();
	}
}

?>
