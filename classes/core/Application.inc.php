<?php

/**
 * @file classes/core/Application.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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

define('PHP_REQUIRED_VERSION', '7.1.0');
define('REQUIRES_XSL', true);

define('ASSOC_TYPE_MONOGRAPH',			ASSOC_TYPE_SUBMISSION);
define('ASSOC_TYPE_PUBLISHED_MONOGRAPH',	ASSOC_TYPE_PUBLISHED_SUBMISSION);
define('ASSOC_TYPE_PUBLICATION_FORMAT',		ASSOC_TYPE_REPRESENTATION);

define('ASSOC_TYPE_PRESS',			0x0000200);
define('ASSOC_TYPE_CATEGORY',			0x000020D);
define('ASSOC_TYPE_SERIES',			ASSOC_TYPE_SECTION);

define('ASSOC_TYPE_CHAPTER', 0x0000214);

define('CONTEXT_PRESS', 1);

define('LANGUAGE_PACK_DESCRIPTOR_URL', 'http://pkp.sfu.ca/omp/xml/%s/locales.xml');
define('LANGUAGE_PACK_TAR_URL', 'http://pkp.sfu.ca/omp/xml/%s/%s.tar.gz');

class Application extends PKPApplication {

	/**
	 * Get the "context depth" of this application, i.e. the number of
	 * parts of the URL after index.php that represent the context of
	 * the current request (e.g. Journal [1], or Conference and
	 * Scheduled Conference [2], or Press [1]).
	 * @return int
	 */
	public function getContextDepth() {
		return 1;
	}

	/**
	 * Get a list of contexts for this application.
	 * @return array
	 */
	public function getContextList() {
		return array('press');
	}

	/**
	 * Get the symbolic name of this application
	 * @return string
	 */
	public static function getName() {
		return 'omp';
	}

	/**
	 * Get the locale key for the name of this application.
	 * @return string
	 */
	public function getNameKey() {
		return('common.openMonographPress');
	}

	/**
	 * Get the URL to the XML descriptor for the current version of this
	 * application.
	 * @return string
	 */
	public function getVersionDescriptorUrl() {
		return('http://pkp.sfu.ca/omp/xml/omp-version.xml');
	}

	/**
	 * Get the map of DAOName => full.class.Path for this application.
	 * @return array
	 */
	public function getDAOMap() {
		return array_merge(parent::getDAOMap(), array(
			'AuthorDAO' => 'classes.monograph.AuthorDAO',
			'ChapterAuthorDAO' => 'classes.monograph.ChapterAuthorDAO',
			'ChapterDAO' => 'classes.monograph.ChapterDAO',
			'FeatureDAO' => 'classes.press.FeatureDAO',
			'IdentificationCodeDAO' => 'classes.publicationFormat.IdentificationCodeDAO',
			'LayoutAssignmentDAO' => 'submission.layoutAssignment.LayoutAssignmentDAO',
			'MarketDAO' => 'classes.publicationFormat.MarketDAO',
			'MetricsDAO' => 'lib.pkp.classes.statistics.PKPMetricsDAO',
			'MonographDAO' => 'classes.monograph.MonographDAO',
			'MonographFileEmailLogDAO' => 'classes.log.MonographFileEmailLogDAO',
			'MonographSearchDAO' => 'classes.search.MonographSearchDAO',
			'NewReleaseDAO' => 'classes.press.NewReleaseDAO',
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
			'RepresentativeDAO' => 'classes.monograph.RepresentativeDAO',
			'ReviewerSubmissionDAO' => 'classes.submission.reviewer.ReviewerSubmissionDAO',
			'SalesRightsDAO' => 'classes.publicationFormat.SalesRightsDAO',
			'SeriesDAO' => 'classes.press.SeriesDAO',
			'SpotlightDAO' => 'classes.spotlight.SpotlightDAO',
			'SubjectDAO' => 'classes.codelist.SubjectDAO',
		));
	}

	/**
	 * Get the list of plugin categories for this application.
	 * @return array
	 */
	public function getPluginCategories() {
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
			'gateways',
			'themes',
			'importexport',
			'oaiMetadataFormats',
			'paymethod',
		);
	}

	/**
	 * Get the top-level context DAO.
	 */
	public static function getContextDAO() {
		return DAORegistry::getDAO('PressDAO');
	}

	/**
	 * Get the context settings DAO.
	 * @return SettingsDAO
	 */
	public static function getContextSettingsDAO() {
		return DAORegistry::getDAO('PressSettingsDAO');
	}

	/**
	 * Get the submission DAO.
	 */
	public static function getSubmissionDAO() {
		return DAORegistry::getDAO('MonographDAO');
	}

	/**
	 * Get the published submission DAO.
	 */
	public static function getPublishedSubmissionDAO() {
		return DAORegistry::getDAO('PublishedMonographDAO');
	}

	/**
	 * Get the section DAO.
	 * @return SeriesDAO
	 */
	public static function getSectionDAO() {
		return DAORegistry::getDAO('SeriesDAO');
	}

	/**
	 * Get the representation DAO.
	 */
	public static function getRepresentationDAO() {
		return DAORegistry::getDAO('PublicationFormatDAO');
	}

	/**
	 * Get a SubmissionSearchIndex instance.
	 */
	public static function getSubmissionSearchIndex() {
		import('classes.search.MonographSearchIndex');
		return new MonographSearchIndex();
	}

	/**
	 * returns the name of the context column in plugin_settings
	 */
	public static function getPluginSettingsContextColumnName() {
		if (defined('SESSION_DISABLE_INIT')) {
			$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
			$driver = $pluginSettingsDao->getDriver();
			switch ($driver) {
				case 'mysql':
				case 'mysqli':
					$checkResult = $pluginSettingsDao->retrieve('SHOW COLUMNS FROM plugin_settings LIKE ?', array('context_id'));
					if ($checkResult->NumRows() == 0) {
						return 'press_id';
					}
					break;
				case 'postgres':
				case 'postgres64':
				case 'postgres7':
				case 'postgres8':
				case 'postgres9':
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
	 * Get the stages used by the application.
	 */
	public static function getApplicationStages() {
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
	public static function getFileDirectories() {
		return array('context' => '/presses/', 'submission' => '/monographs/');
	}

	/**
	 * Returns the context type for this application.
	 */
	public static function getContextAssocType() {
		return ASSOC_TYPE_PRESS;
	}

	/**
	 * Get the payment manager.
	 * @param $context Context
	 * @return OMPPaymentManager
	 */
	public static function getPaymentManager($context) {
		import('classes.payment.omp.OMPPaymentManager');
		return new OMPPaymentManager($context);
	}

	/**
	 * @copydoc PKPApplication::getAllowMultipleContexts()
	 */
	public static function getAllowMultipleContexts() {
		return false;
	}
}
