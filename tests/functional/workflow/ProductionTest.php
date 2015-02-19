<?php

/**
 * @file tests/functional/workflow/ProductionTest.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
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
	 * @copydoc PKPProductionBaseTestCase::productionTest()
	 */
	function testProduction() {
		$this->open(self::$baseUrl);

		$submissionId = $this->findSubmissionAsEditor('dbarnes', null, $fullTitle = 'Wild Words: Essays on Alberta Literature');
		$this->checkApproveSubmissionNotification();

		$this->uploadWizardFile($filename = 'Production ready file', null, true, 'component-grid-files-productionready-productionreadyfilesgrid-addFile-button-'); 
		$this->addPublicationFormat($formatTitle = 'PDF production test');
		$this->openPublicationFormatTab($formatTitle);
		$notificationMessages = $this->getNotificationMessages();
		$this->assertTextPresent($notificationMessages[NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION]);
		$this->click('css=span.xIcon');

		$this->requestLayoutTask('Stephen Hellier', 'shellier@mailinator.com', $fullTitle);
		$this->logOut();

		$this->openTaskAsUser('shellier', $layoutTaskText = 'You have been asked to review layouts for "' . $fullTitle  . '".');

		// Make sure the layout editor can download the production ready file.
		$this->downloadFile($filename);
		$this->uploadWizardFile('Proof file', null, true, 'component-proofFiles-1-addFile-button-');
		$this->completeLayoutTask('Daniel Barnes', 'dbarnes@mailinator.com', $submissionId, 'Galleys have now been prepared for the manuscript,');

		// Make sure the NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT is gone. 
		$this->open(self::$baseUrl . '/index.php/publicknowledge/dashboard');
		$this->waitForElementPresent('css=[id^=component-grid-notifications-notificationsgrid-]');
		$this->assertTextNotPresent($layoutTaskText);
		$this->logOut();

		$this->findSubmissionAsEditor('dbarnes', null, $fullTitle);

		// Approve submission.
		$this->waitForElementPresent($catalogEntrySelector = 'css=a[id^=catalogEntry-button-]');
		$this->click($catalogEntrySelector);
		$this->waitForElementPresent($checkSelector = 'css=input#confirm');
		$this->click($checkSelector);
		$this->click('css=#submission button[id^=submitFormButton-]');
		$this->waitJQuery();
		$this->click('css=.pkp_controllers_modal_titleBar span.xIcon');

		// This is necessary to handle bug #9023, when fixed remove it.
		$this->open(self::$baseUrl . '/index.php/publicknowledge/workflow/access/' . $submissionId);

		$this->assertTextPresent($notificationMessages[NOTIFICATION_TYPE_VISIT_CATALOG]);
		$this->assertTextNotPresent($notificationMessages[NOTIFICATION_TYPE_APPROVE_SUBMISSION]);

		$this->openPublicationFormatTab($formatTitle);
		$this->assertTextNotPresent($notificationMessages[NOTIFICATION_TYPE_FORMAT_NEEDS_APPROVED_SUBMISSION]);
		$this->logOut();
	}

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
