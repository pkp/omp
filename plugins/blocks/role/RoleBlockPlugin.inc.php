<?php

/**
 * @file RoleBlockPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleBlockPlugin
 * @ingroup plugins_blocks_role
 *
 * @brief Class for role block plugin
 */



import('lib.pkp.classes.plugins.BlockPlugin');

class RoleBlockPlugin extends BlockPlugin {
	/**
	 * Install default settings on press creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return Locale::translate('plugins.block.role.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return Locale::translate('plugins.block.role.description');
	}

	/**
	 * Override the block contents based on the current role being
	 * browsed.
	 * @return string
	 */
	function getBlockTemplateFilename() {
		$press =& Request::getPress();
		$user =& Request::getUser();
		if (!$press || !$user) return null;

		$userId = $user->getId();
		$pressId = $press->getId();

		$templateMgr =& TemplateManager::getManager();

		switch (Request::getRequestedPage()) {
			case 'author': switch (Request::getRequestedOp()) {
				case 'submit':
				case 'saveSubmit':
					// Block disabled for submission
					return null;
				default:
					$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
					$submissionsCount = $authorSubmissionDao->getSubmissionsCount($userId, $pressId);
					$templateMgr->assign('submissionsCount', $submissionsCount);
					return 'author.tpl';
			}
			case 'copyeditor':
				$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
				$submissionsCount = $copyeditorSubmissionDao->getSubmissionsCount($userId, $pressId);
				$templateMgr->assign('submissionsCount', $submissionsCount);
				return 'copyeditor.tpl';
			case 'editor':
				if (Request::getRequestedOp() == 'index') return null;
				$editorSubmissionDao =& DAORegistry::getDAO('EditorSubmissionDAO');
				$submissionsCount =& $editorSubmissionDao->getCount($press->getId());
				$templateMgr->assign('submissionsCount', $submissionsCount);
				return 'editor.tpl';
			case 'seriesEditor':
				$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
				$submissionsCount =& $seriesEditorSubmissionDao->getSeriesEditorSubmissionsCount($userId, $pressId);
				$templateMgr->assign('submissionsCount', $submissionsCount);
				return 'seriesEditor.tpl';
			case 'proofreader':
				$proofreaderSubmissionDao =& DAORegistry::getDAO('ProofreaderSubmissionDAO');
				$submissionsCount = $proofreaderSubmissionDao->getSubmissionsCount($userId, $pressId);
				$templateMgr->assign('submissionsCount', $submissionsCount);
				return 'proofreader.tpl';
			case 'reviewer':
				$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
				$submissionsCount = $reviewerSubmissionDao->getSubmissionsCount($userId, $pressId);
				$templateMgr->assign('submissionsCount', $submissionsCount);
				return 'reviewer.tpl';
		}
		return null;
	}
}

?>
