<?php

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Application
 * @ingroup core
 * @see PKPApplication
 *
 * @brief Class describing this application.
 *
 */

// $Id$


import('core.PKPApplication');

define('PHP_REQUIRED_VERSION', '4.2.0');

define('ASSOC_TYPE_PRESS',			0x0000200);
define('ASSOC_TYPE_MONOGRAPH',			0x0000201);
define('ASSOC_TYPE_PRODUCTION_ASSIGNMENT',	0x0000202);
define('ASSOC_TYPE_MONOGRAPH_FILE',	0x0000203);

define('CONTEXT_PRESS', 1);

class Application extends PKPApplication {
	function Application() {
		parent::PKPApplication();
	}

	/**
	 * Get the "context depth" of this application, i.e. the number of
	 * parts of the URL after index.php that represent the context of
	 * the current request (e.g. Journal [1], or Conference and
	 * Scheduled Conference [2], or Press [1]).
	 * @return int
	 */
	function getContextDepth() {
		return 1;
	}

	function getContextList() {
		return array('press');
	}

	/**
	 * Get the symbolic name of this application
	 * @return string
	 */
	function getName() {
		return 'omp';
	}

	/**
	 * Get the locale key for the name of this application.
	 * @return string
	 */
	function getNameKey() {
		return('common.openMonographPress');
	}

	/**
	 * Get the URL to the XML descriptor for the current version of this
	 * application.
	 * @return string
	 */
	function getVersionDescriptorUrl() {
		return('http://pkp.sfu.ca/omp/xml/omp-version.xml');
	}

	/**
	 * Get the map of DAOName => full.class.Path for this application.
	 * @return array
	 */
	function getDAOMap() {
		return array_merge(parent::getDAOMap(), array(
			'AnnouncementDAO' => 'announcement.AnnouncementDAO',
			'AnnouncementTypeDAO' => 'announcement.AnnouncementTypeDAO',
			'BookFileTypeDAO' => 'bookFile.BookFileTypeDAO',
			'MonographEmailLogDAO' => 'monograph.log.MonographEmailLogDAO',
			'MonographEventLogDAO' => 'monograph.log.MonographEventLogDAO',
			'ArtworkFileDAO' => 'monograph.ArtworkFileDAO',
			'MonographCommentDAO' => 'monograph.MonographCommentDAO',
			'MonographSearchDAO' => 'search.MonographSearchDAO',
			'MonographDAO' => 'monograph.MonographDAO',
			'ProductionAssignmentDAO' => 'submission.productionAssignment.ProductionAssignmentDAO',
			'PublicationFormatDAO' => 'publicationFormat.PublicationFormatDAO',
			'SeriesDAO' => 'press.SeriesDAO',
			'DivisionDAO' => 'press.DivisionDAO',
			'SeriesEditorsDAO' => 'press.SeriesEditorsDAO',
			'MonographFileDAO' => 'monograph.MonographFileDAO',
			'MonographGalleyDAO' => 'monograph.MonographGalleyDAO',
			'NotificationStatusDAO' => 'press.NotificationStatusDAO',
			'AuthorDAO' => 'monograph.AuthorDAO',
			'AuthorSubmissionDAO' => 'submission.author.AuthorSubmissionDAO',
			'ChapterDAO' => 'monograph.ChapterDAO',
			'ChapterAuthorDAO' => 'monograph.ChapterAuthorDAO',
			'ProductionEditorSubmissionDAO' => 'submission.productionEditor.ProductionEditorSubmissionDAO',
			'CopyeditorSubmissionDAO' => 'submission.copyeditor.CopyeditorSubmissionDAO',
			'EditAssignmentDAO' => 'submission.editAssignment.EditAssignmentDAO',
			'EditorSubmissionDAO' => 'submission.editor.EditorSubmissionDAO',
			'EmailTemplateDAO' => 'mail.EmailTemplateDAO',
			'DesignerSubmissionDAO' => 'submission.designer.DesignerSubmissionDAO',
			'PluginSettingsDAO' => 'plugins.PluginSettingsDAO',
			'PressDAO' => 'press.PressDAO',
			'PressSettingsDAO' => 'press.PressSettingsDAO',
			'ReviewAssignmentDAO' => 'submission.reviewAssignment.ReviewAssignmentDAO',
			'ReviewerSubmissionDAO' => 'submission.reviewer.ReviewerSubmissionDAO',
			'ReviewFormDAO' => 'reviewForm.ReviewFormDAO',
			'ReviewRoundDAO' => 'monograph.reviewRound.ReviewRoundDAO',
			'ReviewFormElementDAO' => 'reviewForm.ReviewFormElementDAO',
			'ReviewFormResponseDAO' => 'reviewForm.ReviewFormResponseDAO',
			'LibraryFileDAO' => 'press.LibraryFileDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'RoleDAO' => 'security.RoleDAO',
			'UserGroupDAO' => 'security.UserGroupDAO',
			'UserGroupAssignmentDAO' => 'security.UserGroupAssignmentDAO',
			'UserGroupStageAssignmentDAO' => 'workflow.UserGroupStageAssignmentDAO',
			'SeriesEditorSubmissionDAO' => 'submission.seriesEditor.SeriesEditorSubmissionDAO',
			'UserDAO' => 'user.UserDAO',
			'UserSettingsDAO' => 'user.UserSettingsDAO'
		));
	}

	/**
	 * Get the list of plugin categories for this application.
	 */
	function getPluginCategories() {
		return array(
			'auth',
			'blocks',
			'generic',
			'importexport',
			'themes'
		);
	}

	/**
	 * Instantiate the help object for this application.
	 * @return object
	 */
	function &instantiateHelp() {
		import('help.Help');
		$help = new Help();
		return $help;
	}
}

?>
