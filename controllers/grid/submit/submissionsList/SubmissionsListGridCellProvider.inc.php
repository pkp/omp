<?php

/**
 * @file classes/controllers/grid/submit/submissionsList/SubmissionsListGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListGridCellProvider
 * @ingroup controllers_grid_submissionsList
 *
 * @brief Class for a cell provider that can retrieve labels from submissions
 */

import('controllers.grid.DataObjectGridCellProvider');

class SubmissionsListGridCellProvider extends DataObjectGridCellProvider {
	/** @var string The current role, used to determine what URL to link to title */
	var $roleId;
	
	/**
	 * Constructor
	 */
	function SubmissionsListGridCellProvider($roleId = null) {
		$this->roleId = $roleId;
		parent::DataObjectGridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This method extracts the label information from a submission
	 * @see DataObjectGridCellProvider::getLabel()
	 * @param $element DataObject
	 * @param $columnId string
	 */
	function getLabel(&$element, $columnId) {
		assert(is_a($element, 'DataObject') && !empty($columnId));
		switch ($columnId) {
			case 'id':
				return $element->getId();
			case 'dateSubmitted':
				if($element->getDateSubmitted()) {
					return strftime(Config::getVar('general', 'date_format_trunc'), strtotime($element->getDateSubmitted()));
				} else {
					return "&mdash";	
				}
			case 'title':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign_by_ref('submission', $element);
				$templateMgr->assign_by_ref('roleId', $this->roleId);
				return $templateMgr->fetch('controllers/grid/submissionsList/submissionName.tpl');
			case 'authors':
				// FIXME: Use Submission->getAuthorString() when it is refactored to use AuthorDAO instead of internal array (Bug 5231)
				$authorDao =& DAORegistry::getDAO('AuthorDAO');
				$authors =& $authorDao->getAuthorsByMonographId($element->getId());
				$authorList = array();
				while ($author =& $authors->next()) {
					$authorList[] = $author->getLastName();
					unset($author);
				}
				return implode(", ", $authorList);
			case 'status':
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign_by_ref('submission', $element);
				$templateMgr->assign_by_ref('monographId', $element->getId());
				return $templateMgr->fetch('controllers/grid/submissionsList/submissionStatus.tpl');
			case 'dateDue':
				return strftime(Config::getVar('general', 'date_format_trunc'), strtotime($element->getDateDue()));
			case 'dateAssigned':
				return strftime(Config::getVar('general', 'date_format_trunc'), strtotime($element->getDateNotified()));
			case 'reviewRound':
				return $element->getRound();
		}
	}
}