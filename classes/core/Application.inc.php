<?php

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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
define('REQUIRES_XSL', true);

define('ASSOC_TYPE_MONOGRAPH',			ASSOC_TYPE_SUBMISSION);
define('ASSOC_TYPE_PUBLISHED_MONOGRAPH',	ASSOC_TYPE_PUBLISHED_SUBMISSION);
define('ASSOC_TYPE_PUBLICATION_FORMAT',		ASSOC_TYPE_REPRESENTATION);

define('ASSOC_TYPE_PRESS',			0x0000200);
define('ASSOC_TYPE_CATEGORY',			0x000020D);
define('ASSOC_TYPE_SERIES',			ASSOC_TYPE_SECTION);

define('CONTEXT_PRESS', 1);

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
			'AuthorDAO' => 'classes.monograph.AuthorDAO',
			'ChapterAuthorDAO' => 'classes.monograph.ChapterAuthorDAO',
			'ChapterDAO' => 'classes.monograph.ChapterDAO',
			'CategoryDAO' => 'classes.press.CategoryDAO',
			'EmailTemplateDAO' => 'classes.mail.EmailTemplateDAO',
			'FeatureDAO' => 'classes.press.FeatureDAO',
			'IdentificationCodeDAO' => 'classes.publicationFormat.IdentificationCodeDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'MarketDAO' => 'classes.publicationFormat.MarketDAO',
			'SubmissionCommentDAO' => 'lib.pkp.classes.submission.SubmissionCommentDAO',
			'MetricsDAO' => 'lib.pkp.classes.statistics.PKPMetricsDAO',
			'MonographDAO' => 'classes.monograph.MonographDAO',
			'MonographFileEmailLogDAO' => 'classes.log.MonographFileEmailLogDAO',
			'MonographSearchDAO' => 'classes.search.MonographSearchDAO',
			'NewReleaseDAO' => 'classes.press.NewReleaseDAO',
			'NotificationStatusDAO' => 'classes.press.NotificationStatusDAO',
			'OAIDAO' => 'classes.oai.omp.OAIDAO',
			'OMPCompletedPaymentDAO' => 'classes.payment.omp.OMPCompletedPaymentDAO',
			'ONIXCodelistItemDAO' => 'classes.codelist.ONIXCodelistItemDAO',
			'PressDAO' => 'classes.press.PressDAO',
			'PressSettingsDAO' => 'classes.press.PressSettingsDAO',
			'ProductionAssignmentDAO' => 'classes.submission.productionAssignment.ProductionAssignmentDAO',
			'PublicationDateDAO' => 'classes.publicationFormat.PublicationDateDAO',
			'PublicationFormatDAO' => 'classes.publicationFormat.PublicationFormatDAO',
			'PublishedMonographDAO' => 'classes.monograph.PublishedMonographDAO',
			'QualifierDAO' => 'classes.codelist.QualifierDAO',
			'QueuedPaymentDAO' => 'lib.pkp.classes.payment.QueuedPaymentDAO',
			'RepresentativeDAO' => 'classes.monograph.RepresentativeDAO',
			'ReviewAssignmentDAO' => 'lib.pkp.classes.submission.reviewAssignment.ReviewAssignmentDAO',
			'ReviewerSubmissionDAO' => 'classes.submission.reviewer.ReviewerSubmissionDAO',
			'ReviewFormDAO' => 'lib.pkp.classes.reviewForm.ReviewFormDAO',
			'ReviewFormElementDAO' => 'lib.pkp.classes.reviewForm.ReviewFormElementDAO',
			'ReviewFormResponseDAO' => 'lib.pkp.classes.reviewForm.ReviewFormResponseDAO',
			'RoleDAO' => 'classes.security.RoleDAO',
			'SalesRightsDAO' => 'classes.publicationFormat.SalesRightsDAO',
			'SeriesDAO' => 'classes.press.SeriesDAO',
			'SeriesEditorsDAO' => 'classes.press.SeriesEditorsDAO',
			'SpotlightDAO' => 'classes.spotlight.SpotlightDAO',
			'StageAssignmentDAO' => 'lib.pkp.classes.stageAssignment.StageAssignmentDAO',
			'SubjectDAO' => 'classes.codelist.SubjectDAO',
			'SubmissionEventLogDAO' => 'classes.log.SubmissionEventLogDAO',
			'SubmissionFileDAO' => 'classes.monograph.SubmissionFileDAO',
			'UserGroupAssignmentDAO' => 'lib.pkp.classes.security.UserGroupAssignmentDAO',
			'UserDAO' => 'classes.user.UserDAO',
			'UserSettingsDAO' => 'classes.user.UserSettingsDAO',
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
			'viewableFiles',
			'metadata',
			'pubIds',
			'blocks',
			'generic',
			'gateways',
			'themes',
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

	/**
	 * Get the section DAO.
	 * @return SeriesDAO
	 */
	static function getSectionDAO() {
		return DAORegistry::getDAO('SeriesDAO');
	}

	/**
	 * Get the representation DAO.
	 */
	static function getRepresentationDAO() {
		return DAORegistry::getDAO('PublicationFormatDAO');
	}

	/**
	 * returns the name of the context column in plugin_settings
	 */
	static function getPluginSettingsContextColumnName() {
		if (defined('SESSION_DISABLE_INIT')) {
			$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
			$driver = $pluginSettingsDao->getDriver();
			switch ($driver) {
				case 'mysql':
					$checkResult = $pluginSettingsDao->retrieve('SHOW COLUMNS FROM plugin_settings LIKE ?', array('context_id'));
					if ($checkResult->NumRows() == 0) {
						return 'press_id';
					}
					break;
				case 'postgres':
					$checkResult = $pluginSettingsDao->retrieve('SELECT column_name FROM information_schema.columns WHERE table_name= ? AND column_name= ?', array('plugin_settings', 'context_id'));
					if ($checkResult->NumRows() == 0) {
						return 'press_id';
					}
					break;
			}
		}
		return 'context_id';
	}

	/**
	 * Get the DAO for ROLE_ID_SUB_EDITOR roles.
	 */
	static function getSubEditorsDAO() {
		return DAORegistry::getDAO('SeriesEditorsDAO');
	}

	/**
	 * Get the stages used by the application.
	 */
	static function getApplicationStages() {
		// We leave out WORKFLOW_STAGE_ID_PUBLISHED since it technically is not a 'stage'.
		return array(
			WORKFLOW_STAGE_ID_SUBMISSION,
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
			WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
			WORKFLOW_STAGE_ID_EDITING,
			WORKFLOW_STAGE_ID_PRODUCTION
		);
	}

	/**
	 * Get the file directory array map used by the application.
	 */
	static function getFileDirectories() {
		return array('context' => '/presses/', 'submission' => '/monographs/');
	}

	/**
	 * Returns the context type for this application.
	 */
	static function getContextAssocType() {
		return ASSOC_TYPE_PRESS;
	}
}

?>
