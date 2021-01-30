<?php

/**
 * @file classes/services/OMPServiceProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPServiceProvider
 * @ingroup services
 *
 * @brief Utility class to package all OMP services
 */

namespace APP\Services;

require_once(dirname(__FILE__) . '/../../lib/pkp/lib/vendor/pimple/pimple/src/Pimple/Container.php');
require_once(dirname(__FILE__) . '/../../lib/pkp/lib/vendor/pimple/pimple/src/Pimple/ServiceProviderInterface.php');

use \Pimple\Container;
use \APP\Services\PublicationFormatService;
use \APP\Services\PublicationService;
use \APP\Services\StatsEditorialService;
use \APP\Services\StatsService;
use \APP\Services\SubmissionFileService;
use \PKP\Services\PKPAnnouncementService;
use \PKP\Services\PKPAuthorService;
use \PKP\Services\PKPEmailTemplateService;
use \PKP\Services\PKPFileService;
use \PKP\Services\PKPSchemaService;
use \PKP\Services\PKPSiteService;
use \PKP\Services\PKPUserService;


class OMPServiceProvider implements \Pimple\ServiceProviderInterface {

	/**
	 * Registers services
	 * @param Pimple\Container $pimple
	 */
	public function register(Container $pimple) {

		// Announcement service
		$pimple['announcement'] = function() {
			return new PKPAnnouncementService();
		};

		// File service
		$pimple['file'] = function() {
			return new PKPFileService();
		};

		// Submission service
		$pimple['submission'] = function() {
			return new SubmissionService();
		};

		// Publication service
		$pimple['publication'] = function() {
			return new PublicationService();
		};

		// PublicationFormat service
		$pimple['publicationFormat'] = function() {
			return new PublicationFormatService();
		};

		// NavigationMenus service
		$pimple['navigationMenu'] = function() {
			return new NavigationMenuService();
		};

		// Author service
		$pimple['author'] = function() {
			return new PKPAuthorService();
		};

		// User service
		$pimple['user'] = function() {
			return new PKPUserService();
		};

		// Context service
		$pimple['context'] = function() {
			return new ContextService();
		};

		// Submission file service
		$pimple['submissionFile'] = function() {
			return new SubmissionFileService();
		};

		// Email Template service
		$pimple['emailTemplate'] = function() {
			return new PKPEmailTemplateService();
		};

		// Schema service
		$pimple['schema'] = function() {
			return new PKPSchemaService();
		};

		// Site service
		$pimple['site'] = function() {
			return new PKPSiteService();
		};

		// Publication statistics service
		$pimple['stats'] = function() {
			return new StatsService();
		};

		// Publication statistics service
		$pimple['editorialStats'] = function() {
			return new StatsEditorialService();
		};
	}
}
