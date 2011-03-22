<?php

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
define('ASSOC_TYPE_REVIEW_ASSIGNMENT',	0x0000205);
define('ASSOC_TYPE_MONOGRAPH_EMAIL_LOG_ENTRY', 0x0000206);
define('ASSOC_TYPE_WORKFLOW_STAGE', 0x0000207);

define('CONTEXT_PRESS', 1);

define('WORKFLOW_STAGE_ID_PUBLISHED', 0); // FIXME? See bug #6463.
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

	/**
	 * Get a list of contexts for this application.
	 * @return array
	 */
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
			'AuthorDAO' => 'classes.monograph.AuthorDAO',
			'AuthorSubmissionDAO' => 'classes.submission.author.AuthorSubmissionDAO',
			'ChapterAuthorDAO' => 'classes.monograph.ChapterAuthorDAO',
			'ChapterDAO' => 'classes.monograph.ChapterDAO',
			'DivisionDAO' => 'classes.press.DivisionDAO',
			'EditorSubmissionDAO' => 'classes.submission.editor.EditorSubmissionDAO',
			'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
			'GenreDAO' => 'classes.monograph.GenreDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'LibraryFileDAO' => 'classes.press.LibraryFileDAO',
			'MonographCommentDAO' => 'classes.monograph.MonographCommentDAO',
			'MonographDAO' => 'classes.monograph.MonographDAO',
			'MonographEmailLogDAO' => 'classes.log.MonographEmailLogDAO',
			'MonographEventLogDAO' => 'classes.log.MonographEventLogDAO',
			'MonographFileEmailLogDAO' => 'classes.log.MonographFileEmailLogDAO',
			'MonographFileEventLogDAO' => 'classes.log.MonographFileEventLogDAO',
			'MonographSearchDAO' => 'classes.search.MonographSearchDAO',
			'NoteDAO' => 'classes.note.NoteDAO',
			'NotificationStatusDAO' => 'classes.press.NotificationStatusDAO',
			'PluginSettingsDAO' => 'classes.plugins.PluginSettingsDAO',
			'PressDAO' => 'classes.press.PressDAO',
			'PressSettingsDAO' => 'classes.press.PressSettingsDAO',
			'ProductionAssignmentDAO' => 'classes.submission.productionAssignment.ProductionAssignmentDAO',
			'ProductionEditorSubmissionDAO' => 'classes.submission.productionEditor.ProductionEditorSubmissionDAO',
			'PublicationFormatDAO' => 'classes.publicationFormat.PublicationFormatDAO',
			'ReviewAssignmentDAO' => 'classes.submission.reviewAssignment.ReviewAssignmentDAO',
			'ReviewerSubmissionDAO' => 'classes.submission.reviewer.ReviewerSubmissionDAO',
			'ReviewFormDAO' => 'lib.pkp.classes.reviewForm.ReviewFormDAO',
			'ReviewFormElementDAO' => 'lib.pkp.classes.reviewForm.ReviewFormElementDAO',
			'ReviewFormResponseDAO' => 'lib.pkp.classes.reviewForm.ReviewFormResponseDAO',
			'ReviewRoundDAO' => 'classes.monograph.reviewRound.ReviewRoundDAO',
			'RoleDAO' => 'classes.security.RoleDAO',
			'SeriesDAO' => 'classes.press.SeriesDAO',
			'SeriesEditorsDAO' => 'classes.press.SeriesEditorsDAO',
			'SeriesEditorSubmissionDAO' => 'classes.submission.seriesEditor.SeriesEditorSubmissionDAO',
			'SubmissionFileDAO' => 'classes.monograph.SubmissionFileDAO',
			'UserGroupAssignmentDAO' => 'lib.pkp.classes.security.UserGroupAssignmentDAO',
			'UserGroupDAO' => 'lib.pkp.classes.security.UserGroupDAO',
			'UserGroupStageAssignmentDAO' => 'classes.workflow.UserGroupStageAssignmentDAO',
			'UserDAO' => 'classes.user.UserDAO',
			'UserSettingsDAO' => 'classes.user.UserSettingsDAO',
			'ViewsDAO' => 'classes.views.ViewsDAO'
		));
	}

	/**
	 * Get the list of plugin categories for this application.
	 * @return array
	 */
	function getPluginCategories() {
		return array(
			// NB: Meta-data plug-ins are first in the list as this
			// will make them being loaded (and installed) first.
			// This is necessary as several other plug-in categories
			// depend on meta-data. This is a very rudimentary type of
			// dependency management for plug-ins.
			'metadata',
			'blocks',
			'generic',
			'importexport'
		);
	}
}

?>
