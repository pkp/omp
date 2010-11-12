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
			'ArtworkFileDAO' => 'classes.monograph.ArtworkFileDAO',
			'AuthorDAO' => 'classes.monograph.AuthorDAO',
			'AuthorSubmissionDAO' => 'classes.submission.author.AuthorSubmissionDAO',
			'MonographFileTypeDAO' => 'classes.monograph.MonographFileTypeDAO',
			'ChapterAuthorDAO' => 'classes.monograph.ChapterAuthorDAO',
			'ChapterDAO' => 'classes.monograph.ChapterDAO',
			'DesignerSubmissionDAO' => 'classes.submission.designer.DesignerSubmissionDAO',
			'DivisionDAO' => 'classes.press.DivisionDAO',
			'EditorSubmissionDAO' => 'classes.submission.editor.EditorSubmissionDAO',
			'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'LibraryFileDAO' => 'classes.press.LibraryFileDAO',
			'MonographCommentDAO' => 'classes.monograph.MonographCommentDAO',
			'MonographDAO' => 'classes.monograph.MonographDAO',
			'MonographEmailLogDAO' => 'classes.monograph.log.MonographEmailLogDAO',
			'MonographEventLogDAO' => 'classes.monograph.log.MonographEventLogDAO',
			'MonographFileDAO' => 'classes.monograph.MonographFileDAO',
			'MonographGalleyDAO' => 'classes.monograph.MonographGalleyDAO',
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
			'importexport',
			'themes'
		);
	}
}

?>
