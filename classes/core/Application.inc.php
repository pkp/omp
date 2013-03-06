<?php

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
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

define('ASSOC_TYPE_PRESS',			0x0000200);
define('ASSOC_TYPE_MONOGRAPH',			ASSOC_TYPE_SUBMISSION);
define('ASSOC_TYPE_PRODUCTION_ASSIGNMENT',	0x0000202);
define('ASSOC_TYPE_MONOGRAPH_FILE',		0x0000203);
define('ASSOC_TYPE_REVIEW_RESPONSE',		0x0000204);
define('ASSOC_TYPE_REVIEW_ASSIGNMENT',		0x0000205);
define('ASSOC_TYPE_MONOGRAPH_EMAIL_LOG_ENTRY',	0x0000206);
define('ASSOC_TYPE_WORKFLOW_STAGE',		0x0000207);
define('ASSOC_TYPE_NOTE',			0x0000208);
define('ASSOC_TYPE_PUBLICATION_FORMAT',		0x0000209);
define('ASSOC_TYPE_ANNOUNCEMENT',		0x000020A);
define('ASSOC_TYPE_REVIEW_ROUND',		0x000020B);
define('ASSOC_TYPE_CATEGORY',			0x000020D);
define('ASSOC_TYPE_SERIES',			0x000020E);
define('ASSOC_TYPE_MONOGRAPH_FILES',		0x000020F);
define('ASSOC_TYPE_PUBLISHED_MONOGRAPH',	0x0000210);

define('CONTEXT_PRESS', 1);

// To expose ORDER_CATEGORY_GRID_... constants via JS
import('lib.pkp.classes.controllers.grid.feature.OrderCategoryGridItemsFeature');

class Application extends PKPApplication {
	/**
	 * Constructor
	 */
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
			'CataloguingMetadataFieldDAO' => 'classes.cataloguingMetadata.CataloguingMetadataFieldDAO',
			'ChapterAuthorDAO' => 'classes.monograph.ChapterAuthorDAO',
			'ChapterDAO' => 'classes.monograph.ChapterDAO',
			'CategoryDAO' => 'classes.press.CategoryDAO',
			'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
			'FeatureDAO' => 'classes.press.FeatureDAO',
			'FooterCategoryDAO' => 'lib.pkp.classes.context.FooterCategoryDAO',
			'FooterLinkDAO' => 'lib.pkp.classes.context.FooterLinkDAO',
			'GenreDAO' => 'classes.monograph.GenreDAO',
			'IdentificationCodeDAO' => 'classes.publicationFormat.IdentificationCodeDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'LibraryFileDAO' => 'classes.press.LibraryFileDAO',
			'MarketDAO' => 'classes.publicationFormat.MarketDAO',
			'MonographCommentDAO' => 'classes.monograph.MonographCommentDAO',
			'MonographDAO' => 'classes.monograph.MonographDAO',
			'MonographEmailLogDAO' => 'classes.log.MonographEmailLogDAO',
			'MonographEventLogDAO' => 'classes.log.MonographEventLogDAO',
			'MonographFileEmailLogDAO' => 'classes.log.MonographFileEmailLogDAO',
			'MonographFileEventLogDAO' => 'classes.log.MonographFileEventLogDAO',
			'MonographFileSignoffDAO' => 'classes.monograph.MonographFileSignoffDAO',
			'MonographSearchDAO' => 'classes.search.MonographSearchDAO',
			'NewReleaseDAO' => 'classes.press.NewReleaseDAO',
			'NoteDAO' => 'classes.note.NoteDAO',
			'NotificationStatusDAO' => 'classes.press.NotificationStatusDAO',
			'OAIDAO' => 'classes.oai.omp.OAIDAO',
			'OMPCompletedPaymentDAO' => 'classes.payment.omp.OMPCompletedPaymentDAO',
			'PluginSettingsDAO' => 'classes.plugins.PluginSettingsDAO',
			'PressDAO' => 'classes.press.PressDAO',
			'PressSettingsDAO' => 'classes.press.PressSettingsDAO',
			'ProductionAssignmentDAO' => 'classes.submission.productionAssignment.ProductionAssignmentDAO',
			'PublicationDateDAO' => 'classes.publicationFormat.PublicationDateDAO',
			'PublicationFormatDAO' => 'classes.publicationFormat.PublicationFormatDAO',
			'PublishedMonographDAO' => 'classes.monograph.PublishedMonographDAO',
			'QueuedPaymentDAO' => 'lib.pkp.classes.payment.QueuedPaymentDAO',
			'RepresentativeDAO' => 'classes.monograph.RepresentativeDAO',
			'ReviewAssignmentDAO' => 'classes.submission.reviewAssignment.ReviewAssignmentDAO',
			'ReviewerSubmissionDAO' => 'classes.submission.reviewer.ReviewerSubmissionDAO',
			'ReviewFormDAO' => 'lib.pkp.classes.reviewForm.ReviewFormDAO',
			'ReviewFormElementDAO' => 'lib.pkp.classes.reviewForm.ReviewFormElementDAO',
			'ReviewFormResponseDAO' => 'lib.pkp.classes.reviewForm.ReviewFormResponseDAO',
			'ReviewRoundDAO' => 'classes.monograph.reviewRound.ReviewRoundDAO',
			'RoleDAO' => 'classes.security.RoleDAO',
			'SalesRightsDAO' => 'classes.publicationFormat.SalesRightsDAO',
			'SeriesDAO' => 'classes.press.SeriesDAO',
			'SeriesEditorsDAO' => 'classes.press.SeriesEditorsDAO',
			'SeriesEditorSubmissionDAO' => 'classes.submission.seriesEditor.SeriesEditorSubmissionDAO',
			'SignoffDAO' => 'classes.signoff.SignoffDAO',
			'SocialMediaDAO' => 'classes.press.SocialMediaDAO',
			'SpotlightDAO' => 'classes.spotlight.SpotlightDAO',
			'StageAssignmentDAO' => 'classes.stageAssignment.StageAssignmentDAO',
			'SubmissionFileDAO' => 'classes.monograph.SubmissionFileDAO',
			'UserGroupAssignmentDAO' => 'lib.pkp.classes.security.UserGroupAssignmentDAO',
			'UserDAO' => 'classes.user.UserDAO',
			'UserSettingsDAO' => 'classes.user.UserSettingsDAO',
			'UserStageAssignmentDAO' => 'classes.user.UserStageAssignmentDAO',
			'ViewsDAO' => 'classes.views.ViewsDAO',
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
			'pubIds',
			'blocks',
			'generic',
			'importexport',
			'oaiMetadataFormats',
		);
	}

	/**
	 * Get the top-level context DAO.
	 */
	static function getContextDAO() {
		return DAORegistry::getDAO('PressDAO');
	}

	/**
	 * Get the submission DAO.
	 */
	static function getSubmissionDAO() {
		return DAORegistry::getDAO('MonographDAO');
	}
}

?>
