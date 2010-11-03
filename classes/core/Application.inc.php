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


import('lib.pkp.classes.core.PKPApplication');

define('PHP_REQUIRED_VERSION', '4.2.0');

define('ASSOC_TYPE_PRESS', 0x0000200);
define('ASSOC_TYPE_MONOGRAPH', 0x0000201);
define('ASSOC_TYPE_PRODUCTION_ASSIGNMENT',	0x0000202);
define('ASSOC_TYPE_MONOGRAPH_FILE',	0x0000203);
define('ASSOC_TYPE_REVIEW_RESPONSE',	0x0000204);

define('CONTEXT_PRESS', 1);

define('WORKFLOW_STAGE_ID_SUBMISSION', 1);
define('WORKFLOW_STAGE_ID_INTERNAL_REVIEW', 2);
define('WORKFLOW_STAGE_ID_EXTERNAL_REVIEW', 3);
define('WORKFLOW_STAGE_ID_EDITING', 4);
define('WORKFLOW_STAGE_ID_PRODUCTION', 5);

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
			'AnnouncementDAO' => 'classes.announcement.AnnouncementDAO',
			'AnnouncementTypeDAO' => 'classes.announcement.AnnouncementTypeDAO',
			'BookFileTypeDAO' => 'classes.bookFile.BookFileTypeDAO',
			'MonographEmailLogDAO' => 'classes.monograph.log.MonographEmailLogDAO',
			'MonographEventLogDAO' => 'classes.monograph.log.MonographEventLogDAO',
			'ArtworkFileDAO' => 'classes.monograph.ArtworkFileDAO',
			'MonographCommentDAO' => 'classes.monograph.MonographCommentDAO',
			'MonographSearchDAO' => 'classes.search.MonographSearchDAO',
			'MonographDAO' => 'classes.monograph.MonographDAO',
			'NoteDAO' => 'classes.note.NoteDAO',
			'ProductionAssignmentDAO' => 'classes.submission.productionAssignment.ProductionAssignmentDAO',
			'PublicationFormatDAO' => 'classes.publicationFormat.PublicationFormatDAO',
			'SeriesDAO' => 'classes.press.SeriesDAO',
			'DivisionDAO' => 'classes.press.DivisionDAO',
			'SeriesEditorsDAO' => 'classes.press.SeriesEditorsDAO',
			'MonographFileDAO' => 'classes.monograph.MonographFileDAO',
			'MonographGalleyDAO' => 'classes.monograph.MonographGalleyDAO',
			'NotificationStatusDAO' => 'classes.press.NotificationStatusDAO',
			'AuthorDAO' => 'classes.monograph.AuthorDAO',
			'AuthorSubmissionDAO' => 'classes.submission.author.AuthorSubmissionDAO',
			'ChapterDAO' => 'classes.monograph.ChapterDAO',
			'ChapterAuthorDAO' => 'classes.monograph.ChapterAuthorDAO',
			'ProductionEditorSubmissionDAO' => 'classes.submission.productionEditor.ProductionEditorSubmissionDAO',
			'CopyeditorSubmissionDAO' => 'classes.submission.copyeditor.CopyeditorSubmissionDAO',
			'EditorSubmissionDAO' => 'classes.submission.editor.EditorSubmissionDAO',
			'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
			'DesignerSubmissionDAO' => 'classes.submission.designer.DesignerSubmissionDAO',
			'PluginSettingsDAO' => 'classes.plugins.PluginSettingsDAO',
			'PressDAO' => 'classes.press.PressDAO',
			'PressSettingsDAO' => 'classes.press.PressSettingsDAO',
			'ReviewAssignmentDAO' => 'classes.submission.reviewAssignment.ReviewAssignmentDAO',
			'ReviewerSubmissionDAO' => 'classes.submission.reviewer.ReviewerSubmissionDAO',
			'ReviewFormDAO' => 'lib.pkp.classes.reviewForm.ReviewFormDAO',
			'ReviewRoundDAO' => 'classes.monograph.reviewRound.ReviewRoundDAO',
			'ReviewFormElementDAO' => 'lib.pkp.classes.reviewForm.ReviewFormElementDAO',
			'ReviewFormResponseDAO' => 'lib.pkp.classes.reviewForm.ReviewFormResponseDAO',
			'LibraryFileDAO' => 'classes.press.LibraryFileDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'RoleDAO' => 'classes.security.RoleDAO',
			'UserGroupDAO' => 'lib.pkp.classes.security.UserGroupDAO',
			'UserGroupAssignmentDAO' => 'lib.pkp.classes.security.UserGroupAssignmentDAO',
			'UserGroupStageAssignmentDAO' => 'classes.workflow.UserGroupStageAssignmentDAO',
			'SeriesEditorSubmissionDAO' => 'classes.submission.seriesEditor.SeriesEditorSubmissionDAO',
			'UserDAO' => 'classes.user.UserDAO',
			'UserSettingsDAO' => 'classes.user.UserSettingsDAO',
			'ViewsDAO' => 'classes.views.ViewsDAO'
		));
	}

	/**
	 * Get the list of plugin categories for this application.
	 */
	function getPluginCategories() {
		return array(
			// NB: Meta-data plug-ins are first in the list as this
			// will make them being loaded (and installed) first.
			// This is necessary as many other plug-in categories
			// depend on meta-data. This is a very rudimentary type of
			// dependency management for plug-ins.
			'metadata',
			'auth',
			'blocks',
			'generic',
			'importexport',
			'citationLookup',
			'citationOutput',
			'citationParser',
			'themes'
		);
	}
}

?>
