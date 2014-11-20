<?php

/**
 * @file tests/functional/workflow/ProductionTest.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionTest
 * @ingroup tests_functional_workflow
 *
 * @brief Test production process.
 */

import('lib.pkp.tests.functional.workflow.PKPProductionBaseTestCase');

class ProductionTest extends PKPProductionBaseTestCase {

	/**
	 * @copydoc
	 */
	protected function getNotificationMessages() {
		return array(
			NOTIFICATION_TYPE_APPROVE_SUBMISSION => 'This submission is currently awaiting approval in the Catalog Entry tool before it will appear in the public catalog', // notification.type.approveSubmission 
			NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION => 'The metadata for the catalog entry must be approved before the publication format is included in the catalog. To approve catalog metadata, click on Monograph tab above', // notification.type.formatNeedsApprovedSubmission
			NOTIFICATION_TYPE_VISIT_CATALOG => 'The monograph has been approved. Please visit Catalog to manage its catalog details, using the catalog link just above' // notification.type.visitCatalog
		); 
	}
}
